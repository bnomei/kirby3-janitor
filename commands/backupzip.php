<?php

declare(strict_types=1);

use Bnomei\Janitor;
use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Symfony\Component\Finder\Finder;

return [
    'description' => 'Create Backup ZIP',
    'args' => [
        'date' => [
            'longPrefix' => 'date',
            'description' => 'leave empty to not filter by date or provide a string like `since 1 day ago`',
            'defaultValue' => '', // no filter
            'castTo' => 'string',
        ],
        'ulimit' => [
            'longPrefix' => 'ulimit',
            'description' => 'ulimit',
            'defaultValue' => 512,
            'castTo' => 'int',
        ],
        'roots' => [
            'longPrefix' => 'roots',
            'description' => 'comma separated list of absolute folders paths. defaults to: accounts and content roots',
            'defaultValue' => '',
            'castTo' => 'string',
        ],
        'target' => [
            'longPrefix' => 'target',
            'description' => 'absolute output folder and file. defaults to: site/backups/{{ timestamp }}.zip',
            'defaultValue' => '',
            'castTo' => 'string',
        ],
    ] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        $time = time();
        $target = $cli->arg('target');
        if (empty($target)) {
            $target = realpath(kirby()->roots()->accounts() . '/../') . '/backups/{{ timestamp }}.zip';
        }
        $target = str_replace('{{ timestamp }}', strval(time()), $target);
        Dir::make(dirname($target));

        $zip = new ZipArchive();
        if ($zip->open($target, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== true) {
            defined('STDOUT') && $cli->error('Failed to create: ' . $target);
            janitor()->data($cli->arg('command'), [
                'status' => 500,
            ]);
            return;
        }
        $roots = $cli->arg('roots');
        if (empty($roots)) {
            $roots = [
                $cli->kirby()->roots()->accounts(),
                $cli->kirby()->roots()->content(),
            ];
        } else {
            $roots = explode(',', $roots);
        }
        $finder = new Finder();
        $finder->files()->in($roots);
        if (!empty($options['date'])) {
            $finder->date($options['date']);
        }
        $count = iterator_count($finder); // closing of zip
        $ulimit = intval($cli->arg('ulimit'));
        $zipped = 0;

        foreach ($finder as $file) {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $localFilePath = $filePath;
            foreach ($roots as $root) {
                $localFilePath = str_replace(dirname($root), '', $localFilePath);
            }
            if ($zip->addFile($filePath, $localFilePath)) {
                $mime = F::mime($filePath);

                if (in_array($mime, [
                    'application/json', 'text/json',
                    'application/yaml', 'text/yaml',
                    'text/html',
                    'text/plain',
                    'text/xml',
                    'application/x-javascript',
                    'text/css',
                    'text/csv', 'text/x-comma-separated-values',
                    'text/comma-separated-values', 'application/octet-stream',
                ])) {
                    $zip->setCompressionName($filePath, ZipArchive::CM_DEFLATE);
                } else {
                    $zip->setCompressionName($filePath, ZipArchive::CM_STORE);
                }

                $zipped++;
                defined('STDOUT') && $cli->out("[${zipped}/${count}] " . $filePath);

                if ($zipped % $ulimit === 0) {
                    $zip->close();
                    if ($zip->open($target) === false) {
                        @unlink($target);
                        $errorMessage = 'Hit ulimit but failed to reopen zip: ' . $target;
                        if (defined('STDOUT')) {
                            $cli->error($errorMessage);
                        }
                        janitor()->data($cli->arg('command'), [
                            'status' => 500,
                            'error' => $errorMessage,
                        ]);
                        return;
                    }
                }
            }
        }
        $zip->close();

        $data = [
            'status' => $zipped > 0 ? 200 : 204,
            'duration' => time() - $time,
            'filename' => basename($target, '.zip'),
            'files' => $zipped,
            'nicesize' => F::niceSize($target),
            'modified' => date('d/m/Y, H:i:s', F::modified($target)),
        ];
        $data['label'] = $data['filename'] . '.zip [' .$data['nicesize'] .']';

        defined('STDOUT') && $cli->blue($data['duration'] . ' sec');
        defined('STDOUT') && $cli->blue($data['nicesize']);
        defined('STDOUT') && $cli->success($target);

        janitor()->data($cli->arg('command'), $data);
    }
];
