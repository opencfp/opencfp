<?php

use Phinx\Migration\AbstractMigration;

class CreateLeagueOauthTables extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('oauth_clients', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string')
            ->addColumn('secret', 'string')
            ->addColumn('name', 'string')
            ->create();

        $this->table('oauth_client_redirect_uris')
            ->addColumn('client_id', 'string')
            ->addColumn('redirect_uri', 'string')
            ->create();

        $this->table('oauth_scopes', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string')
            ->addColumn('description', 'string')
            ->create();

        $this->table('oauth_sessions')
            ->addColumn('owner_type', 'string')
            ->addColumn('owner_id', 'string')
            ->addColumn('client_id', 'string')
            ->addColumn('client_redirect_uri', 'string', ['null' => true])
            ->addForeignKey('client_id', 'oauth_clients', 'id', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_access_tokens', ['id' => false, 'primary_key' => 'access_token'])
            ->addColumn('access_token', 'string')
            ->addColumn('session_id', 'integer')
            ->addColumn('expire_time', 'integer')
            ->addForeignKey('session_id', 'oauth_sessions', 'id', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_refresh_tokens', ['id' => false, 'primary_key' => 'refresh_token'])
            ->addColumn('refresh_token', 'string')
            ->addColumn('expire_time', 'integer')
            ->addColumn('access_token', 'string')
            ->addForeignKey('access_token', 'oauth_access_tokens', 'access_token', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_auth_codes', ['id' => false, 'primary_key' => 'auth_code'])
            ->addColumn('auth_code', 'string')
            ->addColumn('session_id', 'integer')
            ->addColumn('expire_time', 'integer')
            ->addColumn('client_redirect_uri', 'string')
            ->addForeignKey('session_id', 'oauth_sessions', 'id', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_access_token_scopes')
            ->addColumn('access_token', 'string')
            ->addColumn('scope', 'string')
            ->addForeignKey('access_token', 'oauth_access_tokens', 'access_token', ['delete' => 'cascade'])
            ->addForeignKey('scope', 'oauth_scopes', 'id', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_auth_code_scopes')
            ->addColumn('auth_code', 'string')
            ->addColumn('scope', 'string')
            ->addForeignKey('auth_code', 'oauth_auth_codes', 'auth_code', ['delete' => 'cascade'])
            ->addForeignKey('scope', 'oauth_scopes', 'id', ['delete' => 'cascade'])
            ->create();

        $this->table('oauth_session_scopes')
            ->addColumn('session_id', 'integer')
            ->addColumn('scope', 'string')
            ->addForeignKey('session_id', 'oauth_sessions', 'id', ['delete' => 'cascade'])
            ->addForeignKey('scope', 'oauth_scopes', 'id', ['delete' => 'cascade'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('oauth_session_scopes')->drop();
        $this->table('oauth_auth_code_scopes')->drop();
        $this->table('oauth_access_token_scopes')->drop();
        $this->table('oauth_auth_codes')->drop();
        $this->table('oauth_refresh_tokens')->drop();
        $this->table('oauth_access_tokens')->drop();
        $this->table('oauth_sessions')->drop();
        $this->table('oauth_scopes')->drop();
        $this->table('oauth_client_redirect_uris')->drop();
        $this->table('oauth_clients')->drop();
    }
}