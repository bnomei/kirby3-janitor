<?php

declare(strict_types=1);

if (! class_exists('Bnomei\Janitor')) {
    require_once __DIR__.'/../classes/Janitor.php';
}

use Bnomei\Janitor;
use Kirby\CLI\CLI;
use Kirby\Cms\Media;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\A;
use Symfony\Component\Finder\Finder;

return [
    'description' => 'Generate all thumbs',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $time = time();

        $root = null;
        // just one page
        if (! empty($cli->arg('page')) && $page = page($cli->arg('page'))) {
            $root = $page->mediaRoot();
        }
        // full site
        if (! $root || $cli->arg('site')) {
            $root = realpath(kirby()->roots()->index().'/media/').'/pages';
        }
        Dir::make($root); // make sure folder exists

        // find existing thumbs
        $finder = new Finder();
        $finder->files()
            ->in($root)
            ->name('/\.(?:avif|gif|jpeg|jpg|png|webp)$/');
        $cli->blue(iterator_count($finder).' existing thumbs');

        // job files
        $finder = new Finder();
        $finder->files()
            ->in($root)
            ->ignoreDotFiles(false)
            ->name('/\.json$/');
        $countJobs = iterator_count($finder);
        $cli->blue($countJobs.' thumb job files found');

        // generate
        $jobs = 0;
        $created = 0;
        $jobsFailed = [];
        $countJobs > 0 && $cli->out('Generating Thumbs...');

        foreach ($finder as $file) {
            $jobs++;

            $parentID = null;
            $page = null;

            $page = null;
            if (preg_match('/.*\/media\/pages\/(.*)\/.*-[\d]*\/\.jobs/', $file->getPath(), $matches)) {
                $page = page($matches[1]);
            }
            if (! $page) {
                $skip = '𐄂 '.$parentID.' => Page not found, removed job file';
                $jobsFailed[] = $skip;
                $cli->red($skip);
                unlink($file->getPath().'/'.$file->getFilename());

                continue;
            }

            $path = $file->getPath().'/'.$file->getFilename();
            $options = Data::read($path);
            $jobFilename = $file->getFilenameWithoutExtension();
            $filename = A::get($options, 'filename');

            $pageFile = $page->file($filename);
            if (! $pageFile) {
                $skip = '𐄂 '.$parentID.'/'.$filename.' => File not found, removed job file';
                $jobsFailed[] = $skip;
                $cli->red($skip);
                unlink($file->getPath().'/'.$file->getFilename());

                continue;
            }

            $hash = basename(str_replace('/.jobs', '', $file->getPath()));
            if (Media::link($page, $hash, $jobFilename) !== false) {
                $cli->out('✔ '.$file->getPath().'/'.$file->getFilename());
                $created++;
            }
        }

        $duration = time() - $time;

        $cli->blue($duration.' sec');
        (
            count($jobsFailed) > 0 ?
            $cli->red(count($jobsFailed).' jobs failed') :
            $cli->blue(count($jobsFailed).' jobs failed')
        );
        $cli->success($created.' thumbs created');

        janitor()->data($cli->arg('command'), [
            'status' => $created > 0 ? 200 : 204,
            'duration' => $duration,
            'jobs' => $countJobs,
            'created' => $created,
            'failed' => count($jobsFailed),
            'message' => t('janitor.thumbs.message', str_replace(
                ['{{ created }}', '{{ failed }}'],
                [$created, count($jobsFailed)],
                '{{ created }} thumbs created, {{ failed }} failed'
            )),
        ]);
    },
];
