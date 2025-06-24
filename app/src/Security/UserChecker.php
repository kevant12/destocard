<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use App\Entity\Users;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Users && !$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Vous devez valider votre adresse email avant de pouvoir vous connecter.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à faire ici
    }
} 