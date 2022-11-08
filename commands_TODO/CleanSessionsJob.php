<?php

declare(strict_types=1);


final class CleanSessionsJob extends \Bnomei\JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $success = kirby()->app()->session()->store()->collectGarbage();
        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
