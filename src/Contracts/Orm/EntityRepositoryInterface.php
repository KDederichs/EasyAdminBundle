<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
interface EntityRepositoryInterface
{
    public function createQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder;

    /**
     * @return array{
     * entity_dto: EntityDto,
     * entity_alias: string,
     * property_name: string,
     * }
     */
    public function resolveNestedAssociations(?QueryBuilder $queryBuilder, EntityDto $rootEntityDto, string $propertyName, bool $mustEndWithAssociation = false): array;
}
