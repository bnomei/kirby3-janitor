<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\F;
use Symfony\Component\Finder\Finder;

final class CleanCacheFilesJob extends JanitorJob
{
    /**
     * @return bool
     */
    public function job(): array
    {
        $dir = kirby()->roots()->cache();
        $removed = 0;
        $finder = new Finder();
        $finder->files()->name('*.cache')->in($dir);
        foreach ($finder as $cacheFile) {
            if (F::remove($cacheFile->getRealPath())) {
                $removed++;
            }
        }
        return [
            'status' => $removed > 0 ? 200 : 204,
        ];
    }
}
