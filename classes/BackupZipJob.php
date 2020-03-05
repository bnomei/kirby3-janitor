<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Symfony\Component\Finder\Finder;
use ZipArchive;

final class BackupZipJob extends JanitorJob
{
    /** @var array */
    private $options;

    public function __construct(?Page $page = null, ?string $data = null)
    {
        parent::__construct($page, $data);

        $this->options = [
            'ulimit' => option('bnomei.janitor.backupzip.ulimit', 512), // 1024 seems to be unix default
            'date' => option('bnomei.janitor.backupzip.date', null), // null to disable, 'since 1 day ago'
            'roots' => option('bnomei.janitor.backupzip.roots', function() {
                return [
                    kirby()->roots()->accounts(),
                    kirby()->roots()->content(),
                ];
            }),
            'target' => option('bnomei.janitor.backupzip.target', function() {
                $dir = realpath(kirby()->roots()->accounts() . '/../') . '/backups';
                Dir::make($dir);
                return $dir . '/' . time() . '.zip'; // date('Y-m-d')
            }),
        ];

        foreach ($this->options as $key => $value) {
            if (!is_string($value) && is_callable($value)) {
                $this->options[$key] = $value();
            }
        }
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function job(): array
    {
        $climate = \Bnomei\Janitor::climate();

        $zipPath = (string) $this->option('target');
        $zip = new ZipArchive();
        if($zip->open($zipPath,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== true) {
            if ($climate) {
                $climate->red('Failed to create: ' . $zipPath);
            }
            return [
                'status' => 500,
            ];
        }

        $roots = $this->option('roots');
        $finder = new Finder();
        $finder->files()->in($roots);
        if ($date = $this->option('date')) {
            $finder->date($date);
        }
        $count = iterator_count($finder); // closing of zip

        $ulimit = $this->option('ulimit');
        $zipped = 0;
        if ($climate) {
            $climate->out('Files: ' . $count);
        }

        $progress = null;
        if ($count && $climate) {
            $progress = $climate->progress()->total($count);
        }

        foreach ($finder as $file) {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $localFilePath = $filePath;
            foreach($roots as $root) {
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

                if ($progress && $climate) {
                    $progress->current($zipped);
                }
                if ($zipped % $ulimit === 0) {
                    $zip->close();
                    if ($zip->open($zipPath) === false) {
                        @unlink($zipPath);
                        if ($climate) {
                            $climate->red('Hit ulimit but failed to reopen zip: ' . $zipPath);
                        }
                        return [
                            'status' => 500,
                        ];
                    }
                }
            }
        }

        if ($climate) {
            $climate->out('Closing zip...');
        }
        $zip->close();
        if ($climate) {
            $climate->out($zipPath);
        }

        return [
            'status' => $zipped > 0 ? 200 : 204,
        ];
    }
}
