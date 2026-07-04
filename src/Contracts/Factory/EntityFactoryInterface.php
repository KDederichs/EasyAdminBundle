<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Contracts\Factory;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Collection\EntityCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Component\ExpressionLanguage\Expression;

interface EntityFactoryInterface
{
    public function create(string $entityFqcn, mixed $entityId = null, string|Expression|null $entityPermission = null): EntityDto;
    public function createCollection(EntityDto $entityDto, ?iterable $entityInstances): EntityCollection;
    public function getEntityMetadata(string $entityFqcn): ClassMetadata;
}
