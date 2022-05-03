<?php

namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    private mixed $redis;

    public function loadRedis()
    {
        try {
            $this->redis = app()->make('redis');
        } catch(\Exception $e) {
            // Here we should log the error.
            die($e->getMessage());
        }

    }
    /**
     * Gets all the Movies with their resources.
     *
     * @return false|mixed|string|void
     */
    public function all()
    {
        $movieCollection = Movie::with('images', 'groupVideos.videos', 'directors', 'actors')->get();
        $this->loadRedis();
        return self::cacheCollection(
            $movieCollection,
            3600
        );
    }

    /**
     * Gets oen movie with all the resources.
     *
     * @param $id
     * @return false|mixed|string|void
     */
    public function get($id)
    {
        $movie = Movie::with('images', 'groupVideos.videos', 'directors', 'actors')->find($id);
        $this->loadRedis();
        return self::cache(
            $movie,
            3600
        );
    }

    protected function cacheCollection(Collection $movieCollection, int $expire)
    {
        $movies = collect(array());
        $movieCollection->each(function ($movie) use ($movies, $expire) {
            $movies->push($this->cache($movie, $expire));
        });

        return $movies;
    }

    protected function cache($movie, int $expire)
    {
        $key = $movie->UUID;
        if(!$this->redis->exists($key)) {
            $data = $this->createCacheEntry($key, $movie, $expire);
        } else {
            $data = $this->getCacheEntry($key);
        }

        return json_decode($data);
    }

    /**
     * Create new entry in cached with a specific key.
     *
     * @param string $key
     * @param $data
     * @param int $expire
     * @return false|string
     */
    private function createCacheEntry(string $key, $data, int $expire)
    {
        $data = json_encode($data);
        $this->redis->set($key, $data);
        $this->redis->expire($key, $expire);

        return $data;
    }

    /**
     * Get resource from cache with specific key.
     *
     * @param $key
     * @return mixed
     */
    private function getCacheEntry($key)
    {
        return $this->redis->get($key);
    }
}
