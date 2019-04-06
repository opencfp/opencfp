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

use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use Phinx\Migration\AbstractMigration;
use Symfony\Component\HttpFoundation\File\File;

class ResetPhotoPaths extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $generator = new PseudoRandomStringGenerator();

        // Cleans out "orphaned" files that we have no record of.
        foreach ($this->photosNotPartOfProfile() as $fileName) {
            echo "[info] Removing {$fileName} as it is not registered with any user." . PHP_EOL;
            \unlink($fileName);
        }

        foreach ($this->getSpeakers() as $speaker) {
            echo "[info] Attempting to regenerate photo path for {$speaker['name']}." . PHP_EOL;
            $this->regenerateSpeakerPhotoPath($speaker);
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // This is a data-migration for which there should be no reverse for.
    }

    private function photosNotPartOfProfile()
    {
        $registeredPhotos           = [];
        $fileNamesFlaggedForRemoval = [];

        // Get registered photos from users table.
        foreach ($this->fetchAll("SELECT photo_path FROM users WHERE photo_path <> ''") as $result) {
            $registeredPhotos[] = $result['photo_path'];
        }

        // Crawl uploads directory.
        // If filename is not registered, flag it for removal.
        $iterator = new DirectoryIterator(__DIR__ . '/../web/uploads');

        foreach ($iterator as $file) {
            if ($file->isDot() || \in_array($file->getFilename(), ['dummyphoto.jpg'])) {
                continue;
            }

            if (!\in_array($file->getFilename(), $registeredPhotos)) {
                $fileNamesFlaggedForRemoval[] = $file->getRealPath();
            }
        }

        return $fileNamesFlaggedForRemoval;
    }

    private function getSpeakers()
    {
        // Return structure that is collection of [id, photo_path]
        if ($this->isSqlite()) {
            return $this->fetchAll("SELECT id, photo_path, first_name || ' ' || last_name as name FROM users WHERE photo_path <> ''");
        }

        return $this->fetchAll("SELECT id, photo_path, CONCAT(first_name, ' ', last_name) as name FROM users WHERE photo_path <> ''");
    }

    private function regenerateSpeakerPhotoPath($speaker)
    {
        // If speaker photo does not exist, null it out and return.
        if (!$this->fileExists($speaker['photo_path'])) {
            echo "[info] {$speaker['name']}'s photo was not found in file system. Removing record of it from profile." . PHP_EOL;
            $this->execute("UPDATE users SET photo_path = '' WHERE id = {$speaker['id']}");

            return;
        }

        // Need to guess extension. Cannot trust current file extensions.
        $file      = new File(__DIR__ . '/../web/uploads/' . $speaker['photo_path']);
        $extension = $file->guessExtension();

        // Otherwise, generate a new filename.
        $generator   = new PseudoRandomStringGenerator();
        $newFileName = $generator->generate(40) . '.' . $extension;

        $oldFilePath = __DIR__ . '/../web/uploads/' . $speaker['photo_path'];
        $newFilePath = __DIR__ . '/../web/uploads/' . $newFileName;

        // If photo name is changed in file system, update record in database.
        if (\rename($oldFilePath, $newFilePath)) {
            try {
                $this->execute("UPDATE users SET photo_path = '{$newFileName}' WHERE id = '{$speaker['id']}'");

                echo "[info] Regenerated photo path for {$speaker['name']}." . PHP_EOL;
            } catch (\Exception $e) {
                // If update fails for any reason, revert filename in file system.
                \rename($newFilePath, $oldFilePath);
            }
        }
    }

    private function fileExists($photoPath)
    {
        return \file_exists(__DIR__ . '/../web/uploads/' . $photoPath);
    }

    private function isSqlite()
    {
        return $this->getAdapter()->getAdapterType() === 'sqlite';
    }
}
