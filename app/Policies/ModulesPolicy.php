<?php

namespace Crater\Policies;

use Crater\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulesPolicy
{
    use HandlesAuthorization;

    public function manageModules(User $user): bool
	{
        if ($user->isOwner()) {
            return true;
        }

        return false;
    }
}
