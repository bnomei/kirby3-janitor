<?php

declare(strict_types=1);

namespace Bnomei;

use Exception;
use Kirby\Cms\Media;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Filesystem\Dir;
use Kirby\Http\Remote;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;
use Symfony\Component\Finder\Finder;

final class RenderJob extends JanitorJob
{
    /**
     * @var int|void
     */
    private $countPages;
    /**
     * @var bool
     */
    private $verbose;
    /**
     * @var array
     */
    private $failed;
    /**
     * @var array
     */
    private $found;
    private $countLanguages;
    private $renderSiteUrl;
    /**
     * @var mixed
     */
    private $renderTemplate;

    /**
     * @return array
     */
    public function job(): array
    {
        $kirby = kirby();
        $climate = Janitor::climate();
        $progress = null;
        $this->verbose = $climate ? $climate->arguments->defined('verbose') : false;
        $time = time();

        // make sure the thumbs are triggered
        $kirby->cache('pages')->flush();
        if (class_exists('\Bnomei\Lapse')) {
            Lapse::singleton()->flush();
        }

        // visit all pages to generate media/*.job files
        $allPages = $this->getAllPagesIDs();
        $this->countPages = count($allPages);
        $this->countLanguages = $kirby->languages()->count() > 0 ? $kirby->languages()->count() : 1;
        $visited = 0;

        if ($climate) {
            $climate->out('Languages: ' . $this->countLanguages);
            $climate->out('Pages: ' . $this->countPages);
            $climate->blue('Rendering Pages...');
        }
        if ($this->countPages && $climate) {
            $progress = $climate->progress()->total($this->countPages);
        }
        $this->failed = [];
        $this->found = [];

       $this->renderSiteUrl = rtrim((string)\option('bnomei.janitor.renderSiteUrl')(), '/');

        foreach ($allPages as $pageId) {
            try {
                $content = '';
                if (strlen($this->renderSiteUrl) > 0) {
                    $content = $this->remoteGetPage($pageId);
                } else {
                    $content = $this->renderPage($pageId);
                }
                if ($this->verbose && strlen($content) > 0) {
                    $this->verboseCheckContent($content);
                }
            } catch (Exception $ex) {
                $this->failed[] = $pageId . ': ' . $ex->getMessage();
            }

            $visited++;
            if ($progress && $climate) {
                $progress->current($visited);
            }
        }

        $this->found = array_unique($this->found);
        if ($climate && count($this->found)) {
            $climate->out('Found images with media/pages/* : ' . count($this->found));
        }

        if (count($this->failed)) {
            $climate->out('Rendering failed for Pages: ' . count($this->failed));
            foreach ($this->failed as $fail) {
                $climate->red($fail);
            }
        }

        $duration = time() - $time;

        return [
            'status' => $visited > 0 ? 200 : 204,
            'duration' => $duration,
        ];
    }

    private function getAllPagesIDs(): array
    {
        $ids = [];
        $allPages = null;
        if ($this->data()) {
            $allPages = (new Query(
                $this->data(),
                [
                    'kirby' => kirby(),
                    'site' => site(),
                    'page' => $this->page(),
                ]
            ))->result();
            if (is_a($allPages, Page::class)) {
                $allPages = new Pages([$allPages]);
            }
            foreach ($allPages as $page) {
                $ids[] = $page->id(); // this should not fully load the page yet
            }
        }
        if (!$allPages) {
            $finder = new Finder();
            $finder->directories()
                ->in(kirby()->roots()->content());
            foreach ($finder as $folder) {
                $id = $folder->getRelativePathname();
                if (strpos($id, '_drafts') === false) {
                    $ids[] = ltrim(preg_replace('/\/*\d+_/', '/', $id), '/');
                }
            }
        }

        return $ids;
    }

    private function remoteGetPage(string $pageId): string
    {
        $content = Remote::get($this->renderSiteUrl . '/' . $pageId)->content();
        foreach (kirby()->languages() as $lang) {
            $content .= Remote::get($this->renderSiteUrl . '/' . $lang->code() . '/' . $pageId)->content();
        }
        return $content;
    }

    private function verboseCheckContent(string $content)
    {
        preg_match_all('~/media/pages/([a-zA-Z0-9-_./]+.(?:png|jpg|jpeg|webp|avif|gif))~', $content, $matches);
        if ($matches && count($matches) > 1) {
            $this->found = array_merge($this->found, $matches[1]);
        }
    }

    private function renderPage(string $pageId)
    {
        $page = page($pageId);
        $content = '';
        if ($this->countLanguages > 1) {
            $content = $page->render();
            foreach (kirby()->languages() as $lang) {
                site()->visit($page, $lang->code());
                $content .= $page->render();
            }
        } else {
            site()->visit($page);
            $content = $page->render();
        }
        return $content;
    }
}
