<?php

use Phinx\Migration\AbstractMigration;

class CreateAppRoles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $speaker_permissions = '{"talk.update":true,"talk.review":false,"user.delete":false}';
        $reviewer_permissions = '{"talk.update":true,"talk.review":true,"user.delete":false}';
        $admin_permissions = '{"talk.update":true,"talk.review":true,"user.delete":true}';
        $role_data = [
            ['name' => 'Speaker', 'slug' => 'speaker', 'permissions' => $speaker_permissions],
            ['name' => 'Reviewer', 'slug' => 'reviewer', 'permissions' => $reviewer_permissions],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => $admin_permissions],
        ];
        $this->insert('roles', $role_data);
    }
}
