<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionPerformance\Exception\ExtensionLogicException;

/**
 * @internal
 */
#[CoversClass(ExtensionLogicException::class)]
final class ExtensionLogicExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstanceOfLogicException(): void
    {
        $exception = new ExtensionLogicException('Test message');

        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testCanBeCreatedWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new ExtensionLogicException('Test message', 123, $previous);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
