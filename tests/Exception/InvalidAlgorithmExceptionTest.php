<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidAlgorithmException;

/**
 * @internal
 */
#[CoversClass(InvalidAlgorithmException::class)]
final class InvalidAlgorithmExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidAlgorithmException('Test message');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
    }

    public function testCanBeCreatedWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidAlgorithmException('Test message', 123, $previous);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
