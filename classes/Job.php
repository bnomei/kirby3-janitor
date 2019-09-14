<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;

interface Job
{
    public function __construct(?Page $page = null, ?string $data = null);

    public function data(): ?array;

    public function page(): ?Page;

    public function job(): array;
}
