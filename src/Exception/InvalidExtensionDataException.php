<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

use InvalidArgumentException;

/**
 * 扩展数据无效异常
 *
 * 当TLS扩展的数据格式不正确或无效时抛出此异常
 */
class InvalidExtensionDataException extends InvalidArgumentException
{
}