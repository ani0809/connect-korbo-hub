<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait Uploadable
{
    public function uploadTo(string , UploadedFile ): string
    {
        return ->store(, 'public');
    }
}

