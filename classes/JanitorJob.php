<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;

abstract class JanitorJob implements Job
{
    /*
     * @var Page
     */
    private $page;

    /*
     * @var string
     */
    private $data;

    public function __construct(?Page $page = null, ?string $data = null)
    {
        $this->page = $page;
        $this->data = $data;
    }

    /**
     * @return string|null
     */
    public function data(): ?string
    {
        return $this->data;
    }

    /**
     * @return Page|null
     */
    public function page(): ?Page
    {
        return $this->page;
    }

    /**
     * @return array
     */
    abstract public function job(): array;
}
