<?php

namespace App\Interface;

interface SoftDeletableInterface
{
    public function getDeletedAt(): ?\DateTimeImmutable;
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static;
}
