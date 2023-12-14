<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    public static function store($image, $folderPath): string
    {
        $imageUrl = '';

        if ($image) {
            if (! Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            $path = Storage::putFile($folderPath, $image);

            $imageUrl = env('APP_URL').(Storage::url($path));
        }

        return $imageUrl;
    }

    public static function delete($path): void
    {
        if (Storage::exists('/public'.$path)) {
            Storage::delete('/public'.$path);
        }
    }
}
