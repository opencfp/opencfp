<?php

namespace OpenCFP\Domain\Services;

use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle taking an image file uplaoded via a form and resizing/cropping/resaving.
 *
 * Class ProfileImageProcessor
 *
 * @author Michael Williams <themrwilliams@gmail.com>
 */
class ProfileImageProcessor
{
    /**
     * @var string
     */
    protected $publishDir;

    /**
     * @var RandomStringGenerator
     */
    private $generator;

    /**
     * @var int
     */
    protected $size;

    /**
     * Constructor
     *
     * @param string                $publishDir
     * @param RandomStringGenerator $generator
     * @param int                   $size
     */
    public function __construct($publishDir, RandomStringGenerator $generator, $size = 250)
    {
        $this->publishDir = $publishDir;
        $this->size = $size;
        $this->generator = $generator;
    }

    /**
     * Process an uploaded file and store it in a web-accessible place.
     *
     * @param UploadedFile $file
     * @param string       $publishFilename
     *
     * @return string
     *
     * @throws \Exception
     */
    public function process(UploadedFile $file, $publishFilename = null): string
    {
        if ($publishFilename === null) {
            $publishFilename = $this->generator->generate(50). '.'. $file->guessExtension();
        }
        // Temporary filename to work with.
        $tempFilename = $this->generator->generate(40);

        try {
            $file->move($this->publishDir, $tempFilename);

            $speakerPhoto = Image::make($this->publishDir . '/' . $tempFilename);

            if ($speakerPhoto->height() > $speakerPhoto->width()) {
                $speakerPhoto->resize($this->size, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $speakerPhoto->resize(null, $this->size, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $speakerPhoto->crop($this->size, $this->size);

            if ($speakerPhoto->save($this->publishDir . '/' . $publishFilename)) {
                unlink($this->publishDir . '/' . $tempFilename);
            }
            return $publishFilename;
        } catch (\Exception $e) {
            unlink($this->publishDir . '/' . $tempFilename);
            throw $e;
        }
    }
}
