<?php

namespace App\Security;

use App\Entity\Bloc;
use Symfony\Component\Security\Core\User\UserInterface;

class BlocAccessManager
{
    public function isAccessible(Bloc $bloc, ?UserInterface $user): bool
    {
        if ($bloc->getExpiresAt() && $bloc->getExpiresAt() < new \DateTimeImmutable()) {
            return false;
        }

        $requiredRole = $bloc->getRequiredRole();
        if (null === $requiredRole) {
            return true;
        }

        if (null === $user) {
            return false;
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        return in_array($requiredRole, $roles, true);
    }
}
