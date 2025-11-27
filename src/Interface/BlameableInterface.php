<?php

namespace App\Interface;

use App\Entity\User;

interface BlameableInterface
{
    public function setCreatedBy(?User $user): static;
    public function getCreatedBy(): ?User;
}
