<?php

namespace Tests\Feature;

use App\Services\MovieImporter\JsonImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test the feed is downloaded correctly.
     *
     * @dataProvider jsonInputCase
     * @param string $json
     * @return void
     */
    public function test_feed_download(string $json)
    {
        $jsonImporter = new JsonImporter(new ConsoleOutput());
        $jsonImporter->import($json);

        $this->assertDatabaseCount('movies', 1);
        $this->assertDatabaseCount('actors', 5);
    }

    /**
     * Get data from a small local file.
     *
     * @return array[]
     */
    public function jsonInputCase(): array
    {
        $movies = file_get_contents(
            __DIR__.DIRECTORY_SEPARATOR . 'feed.json',
            FILE_USE_INCLUDE_PATH);
        return [
            [$movies]
        ];
    }
}
