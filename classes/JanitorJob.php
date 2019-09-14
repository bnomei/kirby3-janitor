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
     * @return array|null
     */
    public function data(): ?array
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
     * @return bool
     */
    abstract public function job(): array;
}
