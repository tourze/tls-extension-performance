<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

use InvalidArgumentException;

/**
 * 填充无效异常
 *
 * 当填充参数无效时抛出此异常
 */
class InvalidPaddingException extends InvalidArgumentException
{
}