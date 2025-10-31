<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidExtensionDataException;

/**
 * @internal
 */
#[CoversClass(InvalidExtensionDataException::class)]
final class InvalidExtensionDataExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidExtensionDataException('Test message');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testCanBeCreatedWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidExtensionDataException('Test message', 123, $previous);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
