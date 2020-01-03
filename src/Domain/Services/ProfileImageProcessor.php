<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2020 OpenCFP
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
     * @var int
     */
    private $applicationUserImageSize;

    /**
     * @var string
     */
    private $publishDir;

    /**
     * @var RandomStringGenerator
     */
    private $generator;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var int
     */
    private $applicationUserThumbnailSize = 240; // 120x120@2x

    /**
     * @param string                $publishDir
     * @param RandomStringGenerator $generator
     * @param FilesystemInterface   $filesystem
     * @param int                   $applicationUserImageSize
     */
    public function __construct(string $publishDir, RandomStringGenerator $generator, FilesystemInterface $filesystem, int $applicationUserImageSize = 300)
    {
        $this->publishDir               = $publishDir;
        $this->generator                = $generator;
        $this->filesystem               = $filesystem;
        $this->applicationUserImageSize = $applicationUserImageSize;
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

        // Generate the thumbnail name by adding a "thumb" segment before the extension
        $tmpThumbnailFilenameParts = \explode('.', $publishFilename);
        \array_splice($tmpThumbnailFilenameParts, -1, 0, ['thumb']);
        $thumbnailFilename = \implode('.', $tmpThumbnailFilenameParts);

        // Temporary filename to work with.
        $tempFilename = $this->generator->generate(40);
        $tempFilepath = $this->publishDir . '/' . $tempFilename;

        try {
            $file->move($this->publishDir, $tempFilename);

            // Write the full-sized image to disk
            $speakerPhoto = Image::make($tempFilepath);

            $speakerPhoto->fit($this->applicationUserImageSize);

            $photoData = $speakerPhoto->encode($extension);
            $this->filesystem->write($publishFilename, $photoData);

            // Write the thumbnail-sized image to disk
            $speakerPhoto = Image::make($tempFilepath);

            $speakerPhoto->fit($this->applicationUserThumbnailSize);

            $photoData = $speakerPhoto->encode($extension);
            $this->filesystem->write($thumbnailFilename, $photoData);

            return $publishFilename;
        } finally {
            \unlink($tempFilepath);
        }
    }
}
