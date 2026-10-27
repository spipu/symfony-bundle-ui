<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UiBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Entity\Form;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\AbstractOptions;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\AbstractDataProvider;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Spipu\UiBundle\Service\Ui\PersonalData\PersonalDataProtection;
use Spipu\UiBundle\Service\Ui\PersonalData\PersonalDataRuleInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type;

class SpipuUiMock extends TestCase
{
    /**
     * @param TestCase $testCase
     * @param Grid\Grid $definition
     * @param array $getValues
     * @return GridRequest
     */
    public static function getGridRequest(TestCase $testCase, Grid\Grid $definition, array $getValues = []): GridRequest
    {
        $gridRequest = new GridRequest(
            SymfonyMock::getRequestStack($testCase, $getValues)->getCurrentRequest(),
            SymfonyMock::getRouter($testCase),
            $definition
        );

        return $gridRequest;
    }

    public static function getResourceMock(): ResourceMock
    {
        return new ResourceMock();
    }

    public static function getPersonalDataResourceMock(): PersonalDataResourceMock
    {
        return new PersonalDataResourceMock();
    }

    public static function getGridDefinitionMock(?string $entityName = null): GridDefinitionMock
    {
        return new GridDefinitionMock($entityName);
    }

    public static function getPersonalDataGridDefinitionMock(): GridDefinitionMock
    {
        $definition = new GridDefinitionMock(PersonalDataResourceMock::class);
        $definition->getDefinition()->getColumn('field_a_b')->getFilter()->useQuickSearch(true);
        $definition->getDefinition()->addColumn(
            (new Grid\Column('sub_field_b_a', 'Sub Field B.A', 'sub.fieldBA', 50))
                ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
                ->setFilter((new Grid\ColumnFilter(true, true)))
                ->useSortable()
        );

        return $definition;
    }

    /**
     * Rules: PersonalDataResourceMock protects fieldAB, PersonalDataSubResourceMock protects fieldBA.
     * Doctrine metadata: PersonalDataResourceMock::$sub targets PersonalDataSubResourceMock.
     * @param PersonalDataRuleInterface[]|null $rules
     */
    public static function getPersonalDataProtection(
        TestCase $testCase,
        ?array $rules = null
    ): PersonalDataProtection {
        if ($rules === null) {
            $rules = [
                new PersonalDataRuleMock(PersonalDataResourceMock::class, ['fieldAB']),
                new PersonalDataRuleMock(PersonalDataSubResourceMock::class, ['fieldBA']),
            ];
        }

        $metadataFactory = $testCase->createMock(ClassMetadataFactory::class);
        $metadataFactory
            ->method('isTransient')
            ->willReturnCallback(
                fn (string $className): bool => !is_a($className, EntityInterface::class, true)
            );

        $entityManager = $testCase->createMock(EntityManagerInterface::class);
        $entityManager->method('getMetadataFactory')->willReturn($metadataFactory);
        $entityManager
            ->method('getClassMetadata')
            ->willReturnCallback(
                function (string $className) use ($testCase): ClassMetadata {
                    $associations = [];
                    if ($className === PersonalDataResourceMock::class) {
                        $associations['sub'] = PersonalDataSubResourceMock::class;
                    }

                    $metadata = $testCase->createMock(ClassMetadata::class);
                    $metadata
                        ->method('hasAssociation')
                        ->willReturnCallback(
                            fn (string $name): bool => array_key_exists($name, $associations)
                        );
                    $metadata
                        ->method('getAssociationTargetClass')
                        ->willReturnCallback(
                            fn (string $name): string => $associations[$name]
                        );

                    return $metadata;
                }
            );

        return new PersonalDataProtection(PropertyAccess::createPropertyAccessor(), $entityManager, $rules);
    }

    public static function getEntityDefinitionMock(): EntityDefinitionMock
    {
        return new EntityDefinitionMock();
    }

    public static function getDataProviderMock(): DataProviderMock
    {
        return new DataProviderMock();
    }

    public static function getOptionStringMock(): OptionStringMock
    {
        return new OptionStringMock();
    }

    public static function getOptionIntegerMock(): OptionIntegerMock
    {
        return new OptionIntegerMock();
    }
}

class ResourceMock implements EntityInterface
{
    private int $fieldAA = 0;
    private string $fieldAB = '';
    private string $fieldBA = '';
    private string $fieldBB = '';

    public function getId(): ?int
    {
        return null;
    }

    public function getFieldAA(): int
    {
        return $this->fieldAA;
    }

    public function setFieldAA(int $fieldAA): self
    {
        $this->fieldAA = $fieldAA;

        return $this;
    }

    public function getFieldAB(): string
    {
        return $this->fieldAB;
    }

    public function setFieldAB(string $fieldAB): self
    {
        $this->fieldAB = $fieldAB;

        return $this;
    }

    public function getFieldBA(): string
    {
        return $this->fieldBA;
    }

    public function setFieldBA(string $fieldBA): self
    {
        $this->fieldBA = $fieldBA;

        return $this;
    }

    public function getFieldBB(): string
    {
        return $this->fieldBB;
    }

    public function setFieldBB(string $fieldBB): self
    {
        $this->fieldBB = $fieldBB;

        return $this;
    }
}

class PersonalDataResourceMock extends ResourceMock
{
    private static int $nextId = 1;
    private ?int $id;
    private ?PersonalDataSubResourceMock $sub = null;
    private bool $hidden = false;

    public function __construct()
    {
        $this->id = self::$nextId++;
    }

    public function __clone()
    {
        $this->id = self::$nextId++;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSub(): ?PersonalDataSubResourceMock
    {
        return $this->sub;
    }

    public function setSub(?PersonalDataSubResourceMock $sub): self
    {
        $this->sub = $sub;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}

class PersonalDataSubResourceMock extends ResourceMock
{
    private static int $nextId = 1;
    private int $id;
    private bool $hidden = false;

    public function __construct()
    {
        $this->id = self::$nextId++;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}

class PersonalDataRuleMock implements PersonalDataRuleInterface
{
    private string $entityClass;
    private array $fields;
    private bool $active = true;

    public function __construct(string $entityClass, array $fields)
    {
        $this->entityClass = $entityClass;
        $this->fields = $fields;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function isVisible(EntityInterface $entity): bool
    {
        return !(
            ($entity instanceof PersonalDataResourceMock || $entity instanceof PersonalDataSubResourceMock)
            && $entity->isHidden()
        );
    }

    public function getVisibleDqlCondition(string $alias): array
    {
        return [
            $alias . '.hidden = :hidden OR ' . $alias . '.fieldAA > :hidden_limit',
            ['hidden' => false, ':hidden_limit' => 10],
        ];
    }
}

class GridDefinitionMock implements GridDefinitionInterface
{
    private ?string $entityName;
    private ?Grid\Grid $definition = null;

    public function __construct(?string $entityName = null)
    {
        $this->entityName = $entityName;
    }

    public function getDefinition(): Grid\Grid
    {
        if ($this->definition) {
            return $this->definition;
        }

        $this->definition = new Grid\Grid('test', $this->entityName);
        $this->definition
            ->setDataProviderServiceName('data_provider')
            ->addColumn(
                (new Grid\Column('field_a_a', 'Field A.A', 'fieldAA', 10))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_INTEGER)))
                    ->setFilter((new Grid\ColumnFilter(true))->useRange())
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('field_a_b', 'Field A.B', 'fieldAB', 20))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_INTEGER)))
                    ->setFilter((new Grid\ColumnFilter(true)))
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('field_b_a', 'Field B.A', 'fieldBA', 30))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
                    ->setFilter((new Grid\ColumnFilter(true)))
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('field_b_b', 'Field B.B', 'fieldBB', 30))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
                    ->setFilter((new Grid\ColumnFilter(false)))
                    ->useSortable(false)
            )
        ;

        return $this->definition;
    }
}

class EntityDefinitionMock implements EntityDefinitionInterface
{
    private ?Form\Form $definition = null;

    public function getDefinition(): Form\Form
    {
        if ($this->definition) {
            return $this->definition;
        }

        $this->definition = new Form\Form('test');
        $this->definition
            ->addFieldSet(
                (new Form\FieldSet('fieldset_a', 'FieldSet A', 10))
                    ->addField(
                        (new Form\Field(
                            'field_a_a',
                            Type\TextType::class,
                            10,
                            ['label' => 'Field A.A']
                        ))->setValue('test')
                    )
                    ->addField(
                        (new Form\Field(
                            'field_a_b',
                            Type\TextType::class,
                            20,
                            ['label' => 'Field A.B']
                        ))->setValue('not_defined')->useHiddenInForm()
                    )
            )
            ->addFieldSet(
                (new Form\FieldSet('fieldset_B', 'FieldSet B', 20))
                    ->addField(
                        (new Form\Field(
                            'field_b_a',
                            Type\TextType::class,
                            10,
                            ['label' => 'Field B.A']
                        ))
                    )
                    ->addField(
                        (new Form\Field(
                            'field_b_b',
                            Type\TextType::class,
                            20,
                            ['label' => 'Field B.B']
                        ))->setValue('not_called')
                    )
            )
        ;

        return $this->definition;
    }

    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void
    {
        if ($resource !== null) {
            /** @var ResourceMock $resource */
            $resource->setFieldBB('called');
        } elseif (in_array('error', $form->getData(), true)) {
            throw new FormException('mock error');
        }
    }
}

class DataProviderMock extends AbstractDataProvider
{
    /**
     * @var ResourceMock[]
     */
    private ?array $entities = null;

    /**
     * @var ResourceMock[]
     */
    private ?array $found = null;

    private int $nbEntities = 100;

    public function setNbEntities(int $nbEntities): self
    {
        $this->nbEntities = $nbEntities;

        return $this;
    }

    private function createEntities(): void
    {
        $this->entities = [];
        for ($key=0; $key < $this->nbEntities; $key++) {
            $this->createEntity($key+1);
        }
    }

    private function createEntity(int $key): void
    {
        $entity = new ResourceMock();
        $entity
            ->setFieldAA($key)
            ->setFieldAB((string) $key)
            ->setFieldBA('Name ' . str_pad((string) $key, 6, '0', STR_PAD_LEFT))
            ->setFieldBB('Name ' . str_pad((string) $key, 6, '0', STR_PAD_LEFT));

        $this->entities[] = $entity;
    }

    private function search(): void
    {
        $this->found = $this->entities;
    }

    public function getNbTotalRows(): int
    {
        if ($this->found === null) {
            $this->createEntities();
            $this->search();
        }

        return count($this->found);
    }

    /**
     * @return EntityInterface[]
     */
    public function getPageRows(): array
    {
        $this->getNbTotalRows();

        return array_slice(
            $this->found,
            ($this->request->getPageCurrent() - 1) * $this->request->getPageLength(),
            $this->request->getPageLength()
        );
    }
}

class OptionIntegerMock extends AbstractOptions
{
    public const VALUE_YES = 1;
    public const VALUE_NO = 0;

    protected function buildOptions(): array
    {
        return [
            self::VALUE_YES => 'yes',
            self::VALUE_NO => 'no',
        ];
    }
}

class OptionStringMock extends AbstractOptions
{
    public const VALUE_YES = 'yes';
    public const VALUE_NO = 'no';

    protected function buildOptions(): array
    {
        return [
            self::VALUE_YES => 'Yes !',
            self::VALUE_NO => 'No !',
        ];
    }
}
