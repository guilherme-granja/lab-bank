<?php

namespace Src\Infrastructure\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KycDocumentStorage
{
    public function upload(UploadedFile $file, string $customerId, string $type): false|string
    {
        $directory = sprintf(
            'kyc/%s',
            $customerId
        );

        $name = sprintf(
            '%s-%s.%s',
            $type,
            Str::uuid()->toString(),
            $file->getClientOriginalExtension()
        );

        return Storage::putFileAs(
            path: $directory,
            file: $file,
            name: $name,
        );
    }
}
