<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Exception;

use LogicException;

/**
 * 扩展逻辑异常
 *
 * 当扩展内部逻辑出现问题时抛出此异常
 */
class ExtensionLogicException extends LogicException
{
}