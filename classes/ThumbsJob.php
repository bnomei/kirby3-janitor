<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Media;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Data\Data;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;
use Symfony\Component\Finder\Finder;

final class ThumbsJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $climate = Janitor::climate();
        $progress = null;
        $verbose = $climate ? $climate->arguments->defined('verbose') : false;
        $time = time();

        $root = realpath(kirby()->roots()->index() . '/media/') . '/pages';
        if ($this->page() && $this->data()) {
            $root = $this->page()->mediaRoot();
        }
        Dir::make($root);

        if ($verbose) {
            $finder = new Finder();
            $finder->files()
                ->in($root)
                ->name('/\.(?:png|jpg|jpeg|webp|gif)$/');
            if ($climate) {
                $climate->out('Thumbs found: ' . iterator_count($finder));
            }
        }

        $finder = new Finder();
        $finder->files()
            ->in($root)
            ->ignoreDotFiles(false)
            ->name('/\.json$/');
        $countJobs = iterator_count($finder);
        $jobs = 0;
        $created = 0;
        $jobsSkipped = [];
        if ($climate) {
            $climate->out('Jobs found: ' . $countJobs);
        }

        if ($countJobs && $climate) {
            $progress = $climate->progress()->total($countJobs);
        }

        foreach ($finder as $file) {
            $jobs++;

            $parentID = null;
            $page = null;

            $page = null;
            if (preg_match('/.*\/media\/pages\/(.*)\/[-\d]*\/\.jobs/', $file->getPath(), $matches)) {
                $page = page($matches[1]);
            }
            if (!$page) {
                $jobsSkipped[] = 'Page not found: ' . $parentID;
                continue;
            }

            $path = $file->getPath() . '/' . $file->getFilename();
            $options = Data::read($path);
            $jobFilename = $file->getFilenameWithoutExtension();
            $filename = A::get($options, 'filename');

            $pageFile = $page->file($filename);
            if (!$pageFile) {
                $jobsSkipped[] = 'File not found: ' . $parentID . '/' . $filename;
                continue;
            }

            $hash = basename(str_replace('/.jobs', '', $file->getPath()));

            if (Media::link($page, $hash, $jobFilename) !== false) {
                $created++;
            }

            if ($progress && $climate) {
                $progress->current($jobs);
            }
        }

        $duration = time() - $time;
        if ($climate) {
            $climate->out('Thumbs created: ' . $created);
            if ($jobsSkipped) {
                $climate->out('Jobs executed: ' . $jobs);
                $climate->out('Jobs skipped: ' . count($jobsSkipped));
                foreach ($jobsSkipped as $skip) {
                    $climate->red($skip);
                }
            }
            $climate->out('Duration in seconds: ' . strval($duration));
        }

        return [
            'status' => $created > 0 ? 200 : 204,
            'duration' => $duration,
            'thumbs' => [
                'jobs' => $countJobs,
                'created' => $created,
            ],
        ];
    }
}
