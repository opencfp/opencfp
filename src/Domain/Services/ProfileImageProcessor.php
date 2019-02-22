<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use Intervention\Image\ImageManagerStatic as Image;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle taking an image file uplaoded via a form and resizing/cropping/resaving.
 *
 * @author Michael Williams <themrwilliams@gmail.com>
 */
class ProfileImageProcessor
{
    /**
     * @var string
     */
    private $publishDir;

    /**
     * @var RandomStringGenerator
     */
    private $generator;

    /**
     * @var int
     */
    private $size;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @param string                $publishDir
     * @param RandomStringGenerator $generator
     * @param FilesystemInterface   $filesystem
     * @param int                   $size
     */
    public function __construct($publishDir, RandomStringGenerator $generator, FilesystemInterface $filesystem, $size = 250)
    {
        $this->publishDir = $publishDir;
        $this->size       = $size;
        $this->generator  = $generator;
        $this->filesystem = $filesystem;
    }

    /**
     * Process an uploaded file and store it in a web-accessible place.
     *
     * @param UploadedFile $file
     * @param string       $publishFilename
     *
     * @throws \Exception
     *
     * @return string
     */
    public function process(UploadedFile $file, $publishFilename = null): string
    {
        $extension = $file->guessExtension();

        if ($publishFilename === null) {
            $publishFilename = $this->generator->generate(50) . '.' . $extension;
        }
        // Temporary filename to work with.
        $tempFilename = $this->generator->generate(40);
        $tempFilepath = $this->publishDir . '/' . $tempFilename;

        try {
            $file->move($this->publishDir, $tempFilename);

            $speakerPhoto = Image::make($tempFilepath);

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
            $photoData = $speakerPhoto->encode($extension);
            $this->filesystem->write($publishFilename, $photoData);

            return $publishFilename;
        } finally {
            \unlink($tempFilepath);
        }
    }
}
