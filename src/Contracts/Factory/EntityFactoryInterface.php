<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Contracts\Factory;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Collection\EntityCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Component\ExpressionLanguage\Expression;

interface EntityFactoryInterface
{
    /**
     * @param class-string $entityFqcn
     */
    public function create(string $entityFqcn, mixed $entityId = null, string|Expression|null $entityPermission = null): EntityDto;

    public function createForEntityInstance(object $entityInstance): EntityDto;

    /**
     * @param iterable<object>|null $entityInstances
     */
    public function createCollection(EntityDto $entityDto, ?iterable $entityInstances): EntityCollection;

    /**
     * @template TEntity of object
     *
     * @param class-string<TEntity> $entityFqcn
     *
     * @return ClassMetadata<TEntity>
     */
    public function getEntityMetadata(string $entityFqcn): ClassMetadata;
}
