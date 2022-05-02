<?php

namespace App\Services\FileDownloader;

use Illuminate\Support\Facades\Storage;

class FileDownloader
{
    private const LOCAL_PATH = 'public/images/';

    /**
     * Download a file to a specific file and return the filepath if the download was successful and null otherwise.
     *
     * @param $fileUrl
     * @param $path
     * @return string|null
     */
    public static function downloadFromUrl($fileUrl, $path): ?string
    {
        try {
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $contents = file_get_contents($fileUrl, false, stream_context_create($arrContextOptions));
            $name = substr($fileUrl, strrpos($fileUrl, '/') + 1);

            $localPath = self::LOCAL_PATH . "{$path}/" . $name;

            Storage::put($localPath, $contents);

            return $localPath;

        } catch(\Exception $e) {
            return NULL;
        }
    }
}
