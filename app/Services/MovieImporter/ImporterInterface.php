<?php

namespace App\Services\MovieImporter;

interface ImporterInterface
{
    /**
     * The function will handle import from a source for the importer strategy.
     *
     * @param string $source
     * @param int $chunkSize
     * @return void
     */
    public function import(string $source, int $chunkSize = 50): void;
}
