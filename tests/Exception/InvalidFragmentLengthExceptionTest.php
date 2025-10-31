<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidFragmentLengthException;

/**
 * @internal
 */
#[CoversClass(InvalidFragmentLengthException::class)]
final class InvalidFragmentLengthExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidFragmentLengthException('Test message');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testCanBeCreatedWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidFragmentLengthException('Test message', 123, $previous);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
