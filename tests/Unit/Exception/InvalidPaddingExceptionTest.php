<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionPerformance\Exception\InvalidPaddingException;
use InvalidArgumentException;

/**
 * InvalidPaddingException测试
 */
class InvalidPaddingExceptionTest extends TestCase
{
    public function testIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidPaddingException('Test message');
        
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
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