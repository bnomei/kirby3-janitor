<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\F;
use Symfony\Component\Finder\Finder;

final class FlushSessionFilesJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $dir = kirby()->root('sessions');
        $removed = 0;
        $finder = new Finder();
        $finder->files()->name('*.sess')->in($dir);
        $count = iterator_count($finder);
        $climate = \Bnomei\Janitor::climate();
        $progress = null;
        if ($count && $climate) {
            $progress = $climate->progress()->total($count);
        }
        foreach ($finder as $cacheFile) {
            if (F::remove($cacheFile->getRealPath())) {
                $removed++;
                if ($progress && $climate) {
                    $progress->current($removed);
                }
            }
        }
        return [
            'status' => $removed > 0 ? 200 : 204,
        ];
    }
}
