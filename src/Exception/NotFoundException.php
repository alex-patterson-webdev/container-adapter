<?php

declare(strict_types=1);

namespace Arp\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Exception
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
