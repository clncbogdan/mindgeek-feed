<?php

namespace App\Console\Commands;

use App\Services\MovieImporter\JsonImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Output\ConsoleOutput;

class MindGeekImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mindgeek:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the feed that is provided in the .env file under MGM_FEED_URL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting file import...');
        $rawData = Http::get(env('MGM_FEED_URL'))->body();
        $jsonImporter = new JsonImporter(new ConsoleOutput());
        $jsonImporter->import($rawData);
        $this->info('Imported successfully!');
        return 0;
    }
}
