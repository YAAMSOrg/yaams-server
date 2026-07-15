<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Validates and re-encodes an uploaded aircraft screenshot.
 *
 * Re-encoding is the real security boundary: the uploaded bytes are decoded by
 * GD and written back out as a fresh WebP, which discards all EXIF metadata and
 * any non-image payload smuggled into the original file. The raw upload is never
 * stored. Files are kept on the private `local` disk and only ever served
 * through the authorized `aircraft.images.show` route.
 */
class AircraftImageProcessor
{
    /**
     * Laravel validation rules for the upload field, driven by instance settings.
     * Runs before decode as a cheap first gate.
     *
     * @return array<int, string>
     */
    public static function rules(): array
    {
        $maxKb = (int) Setting::get('aircraft_image_max_filesize_kb');
        $maxDim = (int) Setting::get('aircraft_image_max_dimension');

        return [
            'required', 'file', 'image', 'mimes:jpeg,jpg,png,webp',
            'max:'.$maxKb,
            'dimensions:max_width='.$maxDim.',max_height='.$maxDim,
        ];
    }

    public static function maxPerAircraft(): int
    {
        return (int) Setting::get('aircraft_image_max_per_aircraft');
    }

    /**
     * Re-encode the upload to WebP and store it on the private disk.
     *
     * @return string The stored relative path.
     *
     * @throws \RuntimeException When the file cannot be decoded as an image.
     */
    public static function store(UploadedFile $file, int $aircraftId): string
    {
        $maxDim = (int) Setting::get('aircraft_image_max_dimension');

        try {
            $image = ImageManager::gd()->read($file->getRealPath());
            // Scale down (never up) so the longest side fits within the limit,
            // preserving aspect ratio.
            $image->scaleDown($maxDim, $maxDim);
            $encoded = (string) $image->toWebp(82);
        } catch (Throwable $e) {
            throw new \RuntimeException('The uploaded file could not be processed as an image.', 0, $e);
        }

        $path = 'aircraft/'.$aircraftId.'/'.Str::uuid()->toString().'.webp';
        Storage::disk('local')->put($path, $encoded);

        return $path;
    }
}
