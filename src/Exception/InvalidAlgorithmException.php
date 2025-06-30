<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

use InvalidArgumentException;

/**
 * 算法无效异常
 *
 * 当压缩算法或其他算法参数无效时抛出此异常
 */
class InvalidAlgorithmException extends InvalidArgumentException
{
}