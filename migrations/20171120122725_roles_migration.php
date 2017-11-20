<?php

use Phinx\Migration\AbstractMigration;

class RolesMigration extends AbstractMigration
{
    public function up()
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

   public function down()
   {
       $this->table('roles')->truncate();
   }
}
