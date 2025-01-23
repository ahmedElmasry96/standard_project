<?php

namespace App\Traits;

trait FileTrait {
    public function uploadImage($file,$path): string
    {
        $imageName = time().'_' . $file->getClientOriginalName();
        $file->move(public_path($path), $imageName);

        return $path . $imageName;
    }

    public function removeImage($path): void
    {
        $image_path = public_path($path);
        if(file_exists($image_path)){
            unlink($image_path);
        }
    }
}
