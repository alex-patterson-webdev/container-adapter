<?php


namespace Arp\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * NotFoundException
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Exception
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{}