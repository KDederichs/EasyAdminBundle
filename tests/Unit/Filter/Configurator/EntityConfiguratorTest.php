<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Filter\Configurator;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\DashboardContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\I18nContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\EntityRepositoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Configurator\EntityConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class EntityConfiguratorTest extends TestCase
{
    private EntityRepositoryInterface $entityRepository;

    protected function setUp(): void
    {
        $this->entityRepository = $this->createMock(EntityRepositoryInterface::class);
    }

    public function testConfigureResolvesNestedAssociation(): void
    {
        $adminUrlGenerator = $this->createMock(AdminUrlGeneratorInterface::class);

        $configurator = new EntityConfigurator($adminUrlGenerator, $this->entityRepository);

        $filterDto = new FilterDto();
        $filterDto->setProperty('parent.category');

        $rootEntityDto = new EntityDto('App\Entity\Parent', $this->createMock(ClassMetadata::class));

        $parentCategoryClassMetadata = $this->createMock(ClassMetadata::class);
        $parentCategoryClassMetadata->expects(self::once())
            ->method('getAssociationTargetClass')
            ->with('category')
            ->willReturn('App\Entity\Category');

        $categoryEntityDto = new EntityDto('App\Entity\Category', $parentCategoryClassMetadata);

        $this->entityRepository->expects(self::once())
            ->method('resolveNestedAssociations')
            ->with(null, $rootEntityDto, 'parent.category', true)
            ->willReturn([
                'entity_dto' => $categoryEntityDto,
                'entity_alias' => 'category_parent',
                'property_name' => 'category',
            ]);

        $configurator->configure($filterDto, null, $rootEntityDto, new AdminContext(
            RequestContext::forTesting(),
            CrudContext::forTesting(),
            DashboardContext::forTesting(),
            I18nContext::forTesting(),
        ));

        self::assertSame('App\Entity\Category', $filterDto->getFormTypeOption('value_type_options.class'));
    }
}
