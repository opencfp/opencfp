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

use Phinx\Migration\AbstractMigration;

class RemoveSpeakerTable extends AbstractMigration
{
    public function change()
    {
        $users = $this->table('users');

        // Add speaker columns to the users table
        $users->addColumn('info', 'text');
        $users->addColumn('bio', 'text');
        $users->addColumn('photo_path', 'string', ['limit' => 255]);
        $users->save();

        // Migrate data from speakers to users
        $rows = $this->fetchAll('SELECT * FROM speakers');

        foreach ($rows as $row) {
            $info      = \filter_var($row['info'], FILTER_SANITIZE_MAGIC_QUOTES);
            $bio       = \filter_var($row['bio'], FILTER_SANITIZE_MAGIC_QUOTES);
            $photoPath = \filter_var($row['photo_path'], FILTER_SANITIZE_MAGIC_QUOTES);
            $sql       = "UPDATE users SET info = '{$info}', bio = '{$bio}', photo_path = '{$photoPath}' WHERE id = {$row['user_id']}";
            $this->execute($sql);
        }

        $this->dropTable('speakers');
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Recreate speakers table
        $speakers = $this->table('speakers');
        $speakers->addColumn('user_id', 'integer');
        $speakers->addColumn('info', 'text');
        $speakers->addColumn('bio', 'text');
        $speakers->addColumn('photo_path', 'string', ['limit' => 255]);
        $speakers->create();

        // Migrate data back from users to speakers
        $rows = $this->fetchAll('SELECT id, info, bio, photo_path FROM users');

        foreach ($rows as $row) {
            $info      = \filter_var($row['info'], FILTER_SANITIZE_MAGIC_QUOTES);
            $bio       = \filter_var($row['bio'], FILTER_SANITIZE_MAGIC_QUOTES);
            $photoPath = \filter_var($row['photo_path'], FILTER_SANITIZE_MAGIC_QUOTES);
            $sql       = "INSERT INTO speakers (user_id, info, bio, photo_path)
                VALUES ({$row['user_id']}, '{$info}', '{$bio}', '{$photoPath}')";
            $this->execute($sql);
        }
    }
}
