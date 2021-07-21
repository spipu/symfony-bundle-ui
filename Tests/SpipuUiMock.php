<?php
namespace Spipu\UiBundle\Tests;

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
    public static function getGridRequest(TestCase $testCase, Grid\Grid $definition, array $getValues = [])
    {
        $gridRequest = new GridRequest(
            SymfonyMock::getRequestStack($testCase, $getValues)->getCurrentRequest(),
            SymfonyMock::getRouter($testCase),
            $definition
        );

        return $gridRequest;
    }

    public static function getResourceMock()
    {
        return new ResourceMock();
    }

    public static function getGridDefinitionMock()
    {
        return new GridDefinitionMock();
    }

    public static function getEntityDefinitionMock()
    {
        return new EntityDefinitionMock();
    }

    public static function getDataProviderMock()
    {
        return new DataProviderMock();
    }

    public static function getOptionStringMock()
    {
        return new OptionStringMock();
    }

    public static function getOptionIntegerMock()
    {
        return new OptionIntegerMock();
    }
}

class ResourceMock implements EntityInterface
{
    /**
     * @var string
     */
    private $fieldAA = '';

    /**
     * @var string
     */
    private $fieldAB = '';

    /**
     * @var string
     */
    private $fieldBA = '';

    /**
     * @var string
     */
    private $fieldBB = '';

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return null;
    }

    /**
     * @return string
     */
    public function getFieldAA(): string
    {
        return $this->fieldAA;
    }

    /**
     * @param string $fieldAA
     * @return self
     */
    public function setFieldAA(string $fieldAA): self
    {
        $this->fieldAA = $fieldAA;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldAB(): string
    {
        return $this->fieldAB;
    }

    /**
     * @param string $fieldAB
     * @return self
     */
    public function setFieldAB(string $fieldAB): self
    {
        $this->fieldAB = $fieldAB;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldBA(): string
    {
        return $this->fieldBA;
    }

    /**
     * @param string $fieldBA
     * @return self
     */
    public function setFieldBA(string $fieldBA): self
    {
        $this->fieldBA = $fieldBA;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldBB(): string
    {
        return $this->fieldBB;
    }

    /**
     * @param string $fieldBB
     * @return self
     */
    public function setFieldBB(string $fieldBB): self
    {
        $this->fieldBB = $fieldBB;

        return $this;
    }
}

class GridDefinitionMock implements GridDefinitionInterface
{
    /**
     * @var Grid\Grid
     */
    private $definition;

    /**
     * @return Grid\Grid
     * @throws \Spipu\UiBundle\Exception\GridException
     */
    public function getDefinition(): Grid\Grid
    {
        if ($this->definition) {
            return $this->definition;
        }

        $this->definition = new Grid\Grid('test');
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
    /**
     * @var Form\Form
     */
    private $definition;

    /**
     * @return Form\Form
     * @throws FormException
     */
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

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @throws FormException
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        if ($resource !== null) {
            /** @var ResourceMock $resource */
            $resource->setFieldBB('called');
        } elseif (in_array('error', $form->getData())) {
            throw new FormException('mock error');
        }
    }
}

class DataProviderMock extends AbstractDataProvider
{
    /**
     * @var ResourceMock[]
     */
    private $entities;

    /**
     * @var ResourceMock[]
     */
    private $found;

    /**
     * @var int
     */
    private $nbEntities = 100;

    /**
     * @param int $nbEntities
     * @return self
     */
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
            ->setFieldAB($key)
            ->setFieldBA('Name '.str_pad((string) $key, 6, '0', STR_PAD_LEFT))
            ->setFieldBB('Name '.str_pad((string) $key, 6, '0', STR_PAD_LEFT));

        $this->entities[] = $entity;
    }

    private function search()
    {
        $this->found = $this->entities;
    }

    /**
     * @return int
     */
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

    /**
     * Build the list of the available options
     * @return array
     */
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

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        return [
            self::VALUE_YES => 'Yes !',
            self::VALUE_NO => 'No !',
        ];
    }
}
