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
