<?php

namespace App\Services\MovieImporter;

use App\Models\Actor;
use App\Models\Director;
use App\Models\Movie;
use App\Services\FileDownloader\FileDownloader;
use Illuminate\Support\LazyCollection;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Console\Output\ConsoleOutput;

class JsonImporter implements ImporterInterface
{
    private ConsoleOutput $output;
    private LazyCollection $collection;

    public function __construct(ConsoleOutput $output)
    {
        $this->output = $output;
    }

    /**
     * Implements the import function from a JSON source. The $source param should be the raw string coming from an endpoint or file.
     *
     * @param string $source
     * @param int $chunkSize
     * @return void
     */
    public function import(string $source, int $chunkSize = 50): void
    {
        $this->loadCollection($this->formatData($source));

        $chunkedCollection = $this->collection->chunk($chunkSize)->all();
        foreach ($chunkedCollection as $chunk) {
            $chunkData = $this->extractMovieData($chunk);
            $batchSize = count($chunkData['moviesArray']);
            $this->output->writeln("<info>Importing {$batchSize} movies!</info>");

            Movie::insert($chunkData['moviesArray']);
            $latestInsertedMovies = Movie::orderBy('id', 'desc')->take($batchSize)->get();

            foreach ($latestInsertedMovies as $insertedMovie) {
                $imagesBatchSize = count($chunkData['imagesArray'][$insertedMovie->UUID]);
                $this->output->writeln("<info>Downloading {$imagesBatchSize} images for movie {$insertedMovie->UUID}...</info>");

                foreach ($chunkData['imagesArray'][$insertedMovie->UUID] as &$i) {
                    $i['local_path'] = FileDownloader::downloadFromUrl($i['url'], $insertedMovie->UUID);
                }

                $insertedMovie->images()->createMany($chunkData['imagesArray'][$insertedMovie->UUID]);

                $actorsBatchSize = count($chunkData['actorsArray'][$insertedMovie->UUID]);
                $this->output->writeln("<info>Creating and updating {$actorsBatchSize} actors for movie {$insertedMovie->UUID}...</info>");

                foreach ($chunkData['actorsArray'][$insertedMovie->UUID] as $actor) {
                    $actor = Actor::firstOrCreate(['name' => $actor['name']]);
                    $insertedMovie->actors()->attach($actor);
                }

                $directorsBatchSize = count($chunkData['directorsArray'][$insertedMovie->UUID]);
                $this->output->writeln("<info>Creating and updating {$directorsBatchSize} directors for movie {$insertedMovie->UUID}...</info>");

                foreach ($chunkData['directorsArray'][$insertedMovie->UUID] as $director) {
                    $director = Director::firstOrCreate(['name' => $director['name']]);
                    $insertedMovie->directors()->attach($director);
                }

                if (isset($chunkData['videoArray'][$insertedMovie->UUID])) {
                    foreach ($chunkData['videoArray'][$insertedMovie->UUID] as $clip) {
                        $groupVideoRow = $insertedMovie->groupVideos()->create([
                            'title' => $clip->title ?? '',
                            'type' => $clip->type ?? '',
                            'thumbnail' => $clip->thumbnailUrl ?? '',
                            'url' => $clip->url ?? ''
                        ]);

                        if (isset($clip->alternatives)) {
                            $this->output->writeln("<info>Creating " . count($clip->alternatives) . " alternatives...</info>");
                            foreach ($clip->alternatives as $alternative) {
                                $groupVideoRow->videos()->create([
                                    'quality' => $alternative->quality ?? '',
                                    'url' => $alternative->url ?? ''
                                ]);
                            }
                        }
                    }
                }

            }
        }
    }

    /**
     * Format raw json data to UTF-8//TRANSLIT.
     *
     * @param $data
     * @return bool|string
     */
    private function formatData($data): bool|string
    {
        return iconv('UTF-8', 'UTF-8//TRANSLIT', utf8_encode($data));
    }

    /**
     * Load the formatted data into the $collection attribute.
     *
     * @param $data
     * @return void
     */
    private function loadCollection($data): void
    {
        $this->collection = LazyCollection::make(json_decode($data));
    }

    /**
     * Extract all movie data into arrays to be batch inserted into tables.
     *
     * @param $movies
     * @return array|array[]
     */
    #[ArrayShape(['moviesArray' => "array", 'imagesArray' => "array", 'actorsArray' => "array", 'videoArray' => "array", 'directorsArray' => "array"])]
    private function extractMovieData($movies): array
    {
        $data = array(
            'moviesArray' => array(),
            'imagesArray' => array(),
            'actorsArray' => array(),
            'videoArray'  => array(),
            'directorsArray' => array()
        );

        foreach ($movies as $movie) {
            $data['moviesArray'][] = [
                'UUID' => $movie->id ?? '',
                'body' => $movie->body ?? '',
                'cert' => $movie->cert ?? '',
                'duration' => $movie->duration ?? 0,
                'headline' => $movie->headline ?? '',
                'quote' => $movie->quote ?? '',
                'rating' => $movie->rating ?? 0,
                'review_author' => $movie->reviewAuthor ?? '',
                'sky_go_id' => $movie->skyGoId ?? '',
                'sky_go_url' => $movie->skyGoUrl ?? '',
                'sum' => $movie->sum ?? '',
                'synopsis' => $movie->synopsis ?? '',
                'url' => $movie->url ?? '',
                'vw_start_date' => $movie->viewingWindow->startDate ?? NULL,
                'vw_wtw' => $movie->viewingWindow->wayToWatch ?? '',
                'vw_end_date' => $movie->viewingWindow->endDate ?? NULL,
                'year' => $movie->year ?? '',
            ];

            foreach ($movie->cardImages as $image) {
                $data['imagesArray'][$movie->id][] = [
                    'url' => $image->url,
                    'local_path' => '',
                    'h' => $image->h,
                    'w' => $image->w,
                    'type' => 1
                ];
            }

            foreach ($movie->keyArtImages as $image) {
                $data['imagesArray'][$movie->id][] = [
                    'url' => $image->url,
                    'local_path' => '',
                    'h' => $image->h,
                    'w' => $image->w,
                    'type' => 2
                ];
            }

            foreach ($movie->cast as $actor) {
                $data['actorsArray'][$movie->id][] = [
                    'name' => $actor->name
                ];
            }

            foreach ($movie->directors as $director) {
                $data['directorsArray'][$movie->id][] = [
                    'name' => $director->name
                ];
            }

            if(isset($movie->videos)) {
                foreach ($movie->videos as $video) {
                    $data['videoArray'][$movie->id][] = $video;
                }
            }
        }

        return $data;
    }
}
