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

class ReviewerRoleMigration extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO groups (name, permissions, created_at, updated_at) VALUES ('Reviewer', '{\"reviewer\":1}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    }

    public function down()
    {
        $this->execute("DELETE FROM groups WHERE name ='Reviewer'");
    }
}
