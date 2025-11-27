<?php

namespace App\Doctrine;

use App\Interface\SoftDeletableInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{
    public function addFilterConstraint(
        ClassMetadata $targetEntity,
        $targetTableAlias
    ): string {
        if (!$targetEntity->reflClass->implementsInterface(
            SoftDeletableInterface::class
        )
        ) {
            return '';
        }

        return sprintf('%s.deleted_at IS NULL', $targetTableAlias);
    }
}
