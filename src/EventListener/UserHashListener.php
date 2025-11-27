<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(
    event: Events::prePersist,
    method: 'hashPassword',
    entity: User::class
)]
#[AsEntityListener(
    event: Events::preUpdate,
    method: 'hashPassword',
    entity: User::class
)]
class UserHashListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function hashPassword(User $user): void
    {
        if (!$user->getPlainPassword()) {
            return;
        }

        $hashed = $this->passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($hashed);
        $user->eraseCredentials();
    }
}
