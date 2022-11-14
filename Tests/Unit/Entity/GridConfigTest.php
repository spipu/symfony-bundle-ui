<?php
namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\GridConfig;

class GridConfigTest extends TestCase
{
    public function testEntity()
    {
        $entity = new GridConfig();

        $entity->setUserIdentifier('user_id');
        $entity->setGridIdentifier('grid_id');
        $entity->setName('My Very Long Name');
        $entity->setConfig([]);

        $this->assertSame('grid_id', $entity->getGridIdentifier());
        $this->assertSame('user_id', $entity->getUserIdentifier());
        $this->assertSame('My Very Long Name', $entity->getName());
        $this->assertSame('My Very L...', $entity->getCutName(12));
        $this->assertSame([], $entity->getConfig());
        $this->assertSame([], $entity->getConfigColumns());
        $this->assertSame([], $entity->getConfigFilters());
        $this->assertSame('', $entity->getConfigFilter('foo'));
        $this->assertSame(['column' => null, 'order' => null], $entity->getConfigSort());
        $this->assertSame(null, $entity->getConfigSortColumn());
        $this->assertSame(null, $entity->getConfigSortOrder());
        $this->assertFalse($entity->isDefault());

        $entity->setName('default');
        $this->assertTrue($entity->isDefault());

        $config = [
            'columns' => ['col1', 'col2'],
            'filters' => ['foo' => 'bar'],
            'sort' => ['column' => 'col1', 'order' => 'desc'],
        ];
        $entity->setConfig($config);
        $this->assertSame($config, $entity->getConfig());
        $this->assertSame($config['columns'], $entity->getConfigColumns());
        $this->assertSame($config['filters'], $entity->getConfigFilters());
        $this->assertSame($config['filters']['foo'], $entity->getConfigFilter('foo'));
        $this->assertSame($config['sort'], $entity->getConfigSort());
        $this->assertSame($config['sort']['column'], $entity->getConfigSortColumn());
        $this->assertSame($config['sort']['order'], $entity->getConfigSortOrder());

        $config['sort'] = [];
        $entity->setConfig($config);
        $this->assertSame(null, $entity->getConfigSortColumn());
        $this->assertSame(null, $entity->getConfigSortOrder());

        $config['filters']['foo'] = '';
        $entity->setConfig($config);
        $this->assertSame('', $entity->getConfigFilter('foo'));

        $config['filters']['foo'] = null;
        $entity->setConfig($config);
        $this->assertSame('', $entity->getConfigFilter('foo'));

        $config['filters']['foo'] = false;
        $entity->setConfig($config);
        $this->assertSame('0', $entity->getConfigFilter('foo'));

        $config['filters']['foo'] = true;
        $entity->setConfig($config);
        $this->assertSame('1', $entity->getConfigFilter('foo'));

        $config['filters']['foo'] = 2;
        $entity->setConfig($config);
        $this->assertSame('2', $entity->getConfigFilter('foo'));

        $config['filters']['foo'] = 3.1415;
        $entity->setConfig($config);
        $this->assertSame('3.1415', $entity->getConfigFilter('foo'));
    }
}
