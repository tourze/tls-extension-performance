<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

/**
 * 记录大小限制无效异常
 *
 * 当记录大小限制值无效时抛出此异常
 */
class InvalidRecordSizeLimitException extends \InvalidArgumentException
{
}
