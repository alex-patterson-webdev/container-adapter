<?php

namespace ArpTest\Container\ContainerTest;

use Arp\Container\Container;
use Arp\Container\Adapter\ContainerAdapterInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * ContainerTest
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\ContainerTest
 */
class ContainerTest extends TestCase
{
    /**
     * $adapter
     *
     * @var ContainerAdapterInterface|MockObject
     */
    protected $adapter;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);
    }

    /**
     * testImplementsContainerInterface
     *
     * Ensure that the container implements the PSR
     *
     * @test
     */
    public function testImplementsContainerInterface()
    {
        $container = new Container($this->adapter);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

}