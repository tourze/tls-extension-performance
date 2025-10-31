<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

/**
 * 片段长度无效异常
 *
 * 当最大片段长度值不在允许的范围内时抛出此异常
 */
class InvalidFragmentLengthException extends \InvalidArgumentException
{
}
