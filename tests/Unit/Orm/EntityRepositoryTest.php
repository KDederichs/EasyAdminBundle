<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Factory\EntityFactoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Provider\AdminContextProviderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityRepositoryTest extends TestCase
{
    private AdminContextProviderInterface $adminContextProvider;
    private ManagerRegistry $doctrine;
    private EventDispatcherInterface $eventDispatcher;
    private EntityRepository $entityRepository;
    private EntityFactoryInterface $entityFactory;

    protected function setUp(): void
    {
        $this->adminContextProvider = $this->createMock(AdminContextProviderInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // use reflection to create EntityRepository without needing to mock final classes
        // entityFactory and FormFactory are only used in specific scenarios
        $this->entityFactory = $this->createMock(EntityFactoryInterface::class);
        $formFactory = $this->createFormFactoryStub();

        $this->entityRepository = new EntityRepository(
            $this->adminContextProvider,
            $this->doctrine,
            $this->entityFactory,
            $formFactory,
            $this->eventDispatcher
        );
    }

    public function testCreateQueryBuilderReturnsQueryBuilder(): void
    {
        $searchDto = $this->createSearchDto();
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->with('App\Entity\Product')
            ->willReturn($entityManager);

        $result = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $this->assertSame($queryBuilder, $result);
    }

    public function testCreateQueryBuilderWithEmptyQueryDoesNotAddSearchClause(): void
    {
        $searchDto = $this->createSearchDto();
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        // no andWhere or orWhere for search should be called
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function testCreateQueryBuilderWithSortAddsOrderClause(): void
    {
        $searchDto = $this->createSearchDto('', ['name' => 'ASC']);
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getAllAliases')->willReturn(['entity']);
        $queryBuilder
            ->expects($this->once())
            ->method('addOrderBy')
            ->with('entity.name', 'ASC');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function testCreateQueryBuilderWithMultipleSortFields(): void
    {
        $searchDto = $this->createSearchDto('', ['name' => 'ASC', 'createdAt' => 'DESC']);
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getAllAliases')->willReturn(['entity']);
        $queryBuilder
            ->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturnSelf();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function testCreateQueryBuilderWithNoAppliedFiltersDoesNotCallFormFactory(): void
    {
        $searchDto = $this->createSearchDto('', [], null);
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getAllAliases')->willReturn(['entity']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // test completes without errors since filters are null
        $result = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $this->assertSame($queryBuilder, $result);
    }

    public function testCreateQueryBuilderWithEmptyAppliedFiltersDoesNotCallFormFactory(): void
    {
        $searchDto = $this->createSearchDto();
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getAllAliases')->willReturn(['entity']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $result = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $this->assertSame($queryBuilder, $result);
    }

    public function testCreateQueryBuilderWithSearchQueryAttemptsToGetConnection(): void
    {
        $searchDto = $this->createSearchDto('test search');
        $entityDto = $this->createEntityDto();
        $fields = new FieldCollection([]);
        $filters = new FilterCollection();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getAllAliases')->willReturn(['entity']);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);
        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willThrowException(new \RuntimeException('Connection not available'));

        $this->doctrine
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // when search query is not empty, it tries to get the connection
        // to determine the database platform for the search clause
        $result = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $this->assertSame($queryBuilder, $result);
    }

    public function testResolveNestedAssociationsWithSimpleProperty(): void
    {
        $rootEntityDto = $this->createEntityDto('App\Entity\Post', ['title' => ['type' => 'string']], []);

        $resolved = $this->entityRepository->resolveNestedAssociations(null, $rootEntityDto, 'title');

        self::assertSame($rootEntityDto, $resolved['entity_dto']);
        self::assertSame('entity', $resolved['entity_alias']);
        self::assertSame('title', $resolved['property_name']);
    }

    public function testResolveNestedAssociationsWithNestedProperty(): void
    {
        $authorEntityDto = $this->createEntityDto('App\Entity\User', ['name' => ['type' => 'string']], []);
        $rootEntityDto = $this->createEntityDto('App\Entity\Post', [], ['author' => 'App\Entity\User']);

        $this->entityFactory->expects(self::once())
            ->method('create')
            ->with('App\Entity\User')
            ->willReturn($authorEntityDto);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())
            ->method('leftJoin')
            ->with('entity.author', 'author');

        $resolved = $this->entityRepository->resolveNestedAssociations($queryBuilder, $rootEntityDto, 'author.name');

        self::assertSame($authorEntityDto, $resolved['entity_dto']);
        self::assertSame('author', $resolved['entity_alias']);
        self::assertSame('name', $resolved['property_name']);
    }

    public function testResolveNestedAssociationsEndingWithAssociation(): void
    {
        $categoryEntityDto = $this->createEntityDto('App\Entity\Category', [], ['parent' => 'App\Entity\Category']);
        $rootEntityDto = $this->createEntityDto('App\Entity\Post', [], ['category' => 'App\Entity\Category']);

        $this->entityFactory->expects(self::once())
            ->method('create')
            ->with('App\Entity\Category')
            ->willReturn($categoryEntityDto);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())
            ->method('leftJoin')
            ->with('entity.category', 'category');

        $resolved = $this->entityRepository->resolveNestedAssociations(
            $queryBuilder,
            $rootEntityDto,
            'category.parent',
            true
        );

        self::assertSame($categoryEntityDto, $resolved['entity_dto']);
        self::assertSame('category', $resolved['entity_alias']);
        self::assertSame('parent', $resolved['property_name']);
    }

    public function testResolveNestedAssociationsDoesNotDuplicateJoins(): void
    {
        $authorEntityDto = $this->createEntityDto('App\Entity\User', ['name' => ['type' => 'string']], []);
        $rootEntityDto = $this->createEntityDto('App\Entity\Post', [], ['author' => 'App\Entity\User']);

        $this->entityFactory->method('create')->willReturn($authorEntityDto);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('leftJoin');

        $this->entityRepository->resolveNestedAssociations($queryBuilder, $rootEntityDto, 'author.name');
        $this->entityRepository->resolveNestedAssociations($queryBuilder, $rootEntityDto, 'author.name');
    }

    public function testResolveNestedAssociationsThrowsOnInvalidProperty(): void
    {
        $rootEntityDto = $this->createEntityDto('App\Entity\Post', ['title' => ['type' => 'string']], []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "invalid" property is not valid');

        $this->entityRepository->resolveNestedAssociations(null, $rootEntityDto, 'invalid');
    }

    private function createEntityDto(string $fqcn = 'App\Entity\Product', array $fieldMappings = [], array $associations = []): EntityDto
    {
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->fieldMappings = $fieldMappings;
        $classMetadata->method('getFieldNames')->willReturn(array_keys($fieldMappings));
        $classMetadata->method('getFieldMapping')->willReturnCallback(
            static fn (string $name): array => $fieldMappings[$name] ?? throw new \InvalidArgumentException()
        );
        $classMetadata->method('hasAssociation')->willReturnCallback(
            static fn (string $name): bool => isset($associations[$name])
        );
        $classMetadata->method('getAssociationTargetClass')->willReturnCallback(
            static fn (string $name): string => $associations[$name] ?? throw new \InvalidArgumentException()
        );

        return new EntityDto($fqcn, $classMetadata);
    }

    private function createSearchDto(string $query = '', array $sort = [], ?array $appliedFilters = []): SearchDto
    {
        return new SearchDto(
            new Request(),
            null, // searchableProperties
            $query,
            $sort, // defaultSort
            [], // customSort
            $appliedFilters
        );
    }

    /**
     * Creates a stub for FormFactory using reflection since it's a final class.
     */
    private function createFormFactoryStub(): FormFactory
    {
        return (new \ReflectionClass(FormFactory::class))
            ->newInstanceWithoutConstructor();
    }
}
