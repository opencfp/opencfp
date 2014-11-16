<?php

namespace OpenCFP\Domain\Services;

use Intervention\Image\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle taking an image file uplaoded via a form and resizing/cropping/resaving.
 *
 * Class ProfileImageProcessor
 * @author Michael Williams <themrwilliams@gmail.com>
 */
class ProfileImageProcessor
{
    /**
     * @var string
     */
    protected $publishDir;

    /**
     * @var int
     */
    protected $size;

    /**
     * Constructor
     *
     * @param $publishDir
     * @param int $size
     */
    public function __construct($publishDir, $size = 250)
    {
        $this->publishDir = $publishDir;
        $this->size = $size;
    }

    /**
     * Process an uploaded file and store it in a web-accessible place.
     *
     * @param UploadedFile $file
     * @param $publishFilename
     */
    public function process(UploadedFile $file, $publishFilename)
    {
        // Temporary filename to work with.
        $fileName = uniqid() . '_' . $file->getClientOriginalName();

        $file->move($this->publishDir, $fileName);

        $speakerPhoto = Image::make($this->publishDir . '/' . $fileName);

        if ($speakerPhoto->height > $speakerPhoto->width) {
            $speakerPhoto->resize($this->size, null, true);
        } else {
            $speakerPhoto->resize(null, $this->size, true);
        }

        $speakerPhoto->crop($this->size, $this->size);

        if ($speakerPhoto->save($this->publishDir . '/' . $publishFilename)) {
            unlink($this->publishDir . '/' . $fileName);
        }
    }
}