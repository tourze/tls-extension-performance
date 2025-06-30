<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidFragmentLengthException;
use InvalidArgumentException;

/**
 * InvalidFragmentLengthException测试
 */
class InvalidFragmentLengthExceptionTest extends TestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidFragmentLengthException('Test message');
        
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
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