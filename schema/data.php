<?php

use Doctrine\DBAL\Schema\Schema;

$schema = new Schema();

$table = $schema->createTable('groups');
$table->addColumn('id', 'integer', array('autoincrement' => true));
$table->addColumn('name', 'string', array('length' => 255));
$table->addColumn('permissions', 'text', array('notnull' => false));
$table->addColumn('created_at', 'datetime', array('default' => '0000-00-00 00:00:00'));
$table->addColumn('updated_at', 'datetime', array('default' => '0000-00-00 00:00:00'));
$table->setPrimaryKey(array('id'), 'PRIMARY');
$table->addUniqueIndex(array('name'), 'groups_name_unique');

$table = $schema->createTable('migrations');
$table->addColumn('migration', 'string', array('length' => 255));
$table->addColumn('batch', 'integer', array());

$table = $schema->createTable('speakers');
$table->addColumn('user_id', 'integer', array());
$table->addColumn('info', 'text', array('notnull' => false));
$table->addColumn('bio', 'text', array('notnull' => false));
$table->addColumn('transportation', 'boolean', array('default' => '0'));
$table->addColumn('hotel', 'boolean', array('default' => '0'));
$table->addColumn('photo_path', 'string', array('notnull' => false));
$table->setPrimaryKey(array('user_id'), 'PRIMARY');

$table = $schema->createTable('talks');
$table->addColumn('id', 'integer', array('autoincrement' => true));
$table->addColumn('user_id', 'integer', array());
$table->addColumn('title', 'string', array('length' => 100, 'fixed' => true));
$table->addColumn('description', 'text', array('notnull' => false));
$table->addColumn('type', 'string', array('notnull' => false, 'length' => 50, 'fixed' => true));
$table->addColumn('level', 'string', array('notnull' => false, 'length' => 50, 'fixed' => true));
$table->addColumn('category', 'string', array('notnull' => false, 'length' => 50, 'fixed' => true));
$table->addColumn('desired', 'boolean', array('default' => '0'));
$table->addColumn('slides', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('other', 'text', array('notnull' => false));
$table->addColumn('sponsor', 'boolean', array('default' => '0'));
$table->addColumn('favorite', 'boolean', array('default' => '0'));
$table->addColumn('selected', 'boolean', array('default' => '0'));
$table->addColumn('created_at', 'datetime', array('notnull' => false));
$table->addColumn('updated_at', 'datetime', array('notnull' => false));
$table->setPrimaryKey(array('id'), 'PRIMARY');

$table = $schema->createTable('throttle');
$table->addColumn('id', 'integer', array('autoincrement' => true));
$table->addColumn('user_id', 'integer', array());
$table->addColumn('ip_address', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('attempts', 'integer', array('default' => '0'));
$table->addColumn('suspended', 'boolean', array('default' => '0'));
$table->addColumn('banned', 'boolean', array('default' => '0'));
$table->addColumn('last_attempt_at', 'datetime', array('notnull' => false));
$table->addColumn('suspended_at', 'datetime', array('notnull' => false));
$table->addColumn('banned_at', 'datetime', array('notnull' => false));
$table->setPrimaryKey(array('id'), 'PRIMARY');

$table = $schema->createTable('users');
$table->addColumn('id', 'integer', array('autoincrement' => true));
$table->addColumn('first_name', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('last_name', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('email', 'string', array('length' => 255));
$table->addColumn('password', 'string', array('length' => 255));
$table->addColumn('company', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('url', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('twitter', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('airport', 'string', array('notnull' => false, 'length' => 5));
$table->addColumn('permissions', 'text', array('notnull' => false));
$table->addColumn('activated', 'boolean', array('default' => '0'));
$table->addColumn('activation_code', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('activated_at', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('last_login', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('persist_code', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('reset_password_code', 'string', array('notnull' => false, 'length' => 255));
$table->addColumn('created_at', 'datetime', array('default' => '0000-00-00 00:00:00'));
$table->addColumn('updated_at', 'datetime', array('default' => '0000-00-00 00:00:00'));
$table->setPrimaryKey(array('id'), 'PRIMARY');
$table->addUniqueIndex(array('email'), 'users_email_unique');

$table = $schema->createTable('users_groups');
$table->addColumn('id', 'integer', array('autoincrement' => true));
$table->addColumn('user_id', 'integer', array());
$table->addColumn('group_id', 'integer', array());
$table->setPrimaryKey(array('id'), 'PRIMARY');

return $schema;
