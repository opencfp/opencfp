<?php

namespace OpenCFP\Domain\Services;

use Cartalyst\Sentry\Users\Eloquent\User;

interface AccountManagement
{
    public function findById($userId): User;
    public function findByLogin($email): User;
    public function findByRole($role): array;
    public function create($email, $password, array $data = []);
    public function promote($email);
    public function demote($email);
}