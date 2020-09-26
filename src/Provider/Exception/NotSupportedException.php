<?php

declare(strict_types=1);

namespace Arp\Container\Provider\Exception;

/**
 * Exception which indicates the service provider was asked to execute a operation that the adapter does not support.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter\Exception
 */
final class NotSupportedException extends ServiceProviderException
{

}
