<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidPaddingException;

/**
 * @internal
 */
#[CoversClass(InvalidPaddingException::class)]
final class InvalidPaddingExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidPaddingException('Test message');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testCanBeCreatedWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidPaddingException('Test message', 123, $previous);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
