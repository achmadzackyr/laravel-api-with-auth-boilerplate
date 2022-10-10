<?php
namespace App\Http\Traits;

trait RoleTrait
{
    protected function isAdmin($user): bool
    {
        if (!empty($user)) {
            return $user->tokenCan('admin');
        }

        return false;
    }
}
