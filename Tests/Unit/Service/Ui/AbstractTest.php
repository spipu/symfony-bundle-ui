<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTest extends TestCase
{
    protected function getContainerMock($services = []): ContainerInterface
    {
        $services = array_merge(
            [
                'request_stack'    => SymfonyMock::getRequestStack($this),
                'router'           => SymfonyMock::getRouter($this),
                'translator'       => SymfonyMock::getTranslator($this),
                'event_dispatcher' => SymfonyMock::getEventDispatcher($this),
                'twig'             => SymfonyMock::getTwig($this),
                'security.authorization_checker'      => SymfonyMock::getAuthorizationChecker($this),
                'doctrine.orm.default_entity_manager' => SymfonyMock::getEntityManager($this),
            ],
            $services
        );

        return SymfonyMock::getContainer($this, $services);
    }
}
