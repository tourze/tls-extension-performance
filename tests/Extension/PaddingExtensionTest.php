<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionPerformance\Extension\PaddingExtension;

/**
 * PaddingExtension 测试类
 */
class PaddingExtensionTest extends TestCase
{
    public function testGetType(): void
    {
        $extension = new PaddingExtension();
        $this->assertEquals(ExtensionType::PADDING->value, $extension->getType());
    }
    
    public function testDefaultPaddingLength(): void
    {
        $extension = new PaddingExtension();
        $this->assertEquals(0, $extension->getPaddingLength());
    }
    
    public function testSetPaddingLength(): void
    {
        $extension = new PaddingExtension();
        
        $extension->setPaddingLength(100);
        $this->assertEquals(100, $extension->getPaddingLength());
        
        $extension->setPaddingLength(0);
        $this->assertEquals(0, $extension->getPaddingLength());
        
        $extension->setPaddingLength(PaddingExtension::MAX_PADDING_LENGTH);
        $this->assertEquals(PaddingExtension::MAX_PADDING_LENGTH, $extension->getPaddingLength());
    }
    
    public function testSetPaddingLengthChaining(): void
    {
        $extension = new PaddingExtension();
        $result = $extension->setPaddingLength(100);
        $this->assertSame($extension, $result);
    }
    
    public function testSetNegativePaddingLength(): void
    {
        $extension = new PaddingExtension();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Padding length cannot be negative');
        
        $extension->setPaddingLength(-1);
    }
    
    public function testSetPaddingLengthTooLarge(): void
    {
        $extension = new PaddingExtension();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Padding length cannot exceed 65535 bytes, 65536 given');
        
        $extension->setPaddingLength(65536);
    }
    
    public function testEncode(): void
    {
        $extension = new PaddingExtension(0);
        $encoded = $extension->encode();
        $this->assertEquals("", $encoded);
        
        $extension = new PaddingExtension(5);
        $encoded = $extension->encode();
        $this->assertEquals("\x00\x00\x00\x00\x00", $encoded);
        $this->assertEquals(5, strlen($encoded));
        
        $extension = new PaddingExtension(10);
        $encoded = $extension->encode();
        $this->assertEquals(str_repeat("\x00", 10), $encoded);
        $this->assertEquals(10, strlen($encoded));
    }
    
    public function testDecode(): void
    {
        $extension = PaddingExtension::decode("");
        $this->assertEquals(0, $extension->getPaddingLength());
        
        $extension = PaddingExtension::decode("\x00\x00\x00\x00\x00");
        $this->assertEquals(5, $extension->getPaddingLength());
        
        $extension = PaddingExtension::decode(str_repeat("\x00", 100));
        $this->assertEquals(100, $extension->getPaddingLength());
    }
    
    public function testDecodeWithNonZeroByte(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Padding extension data must contain only zero bytes, found non-zero byte at position 3');
        
        PaddingExtension::decode("\x00\x00\x00\x01\x00");
    }
    
    public function testEncodeDecode(): void
    {
        $original = new PaddingExtension(250);
        $encoded = $original->encode();
        $decoded = PaddingExtension::decode($encoded);
        
        $this->assertEquals($original->getPaddingLength(), $decoded->getPaddingLength());
    }
    
    public function testCreateForTargetSize(): void
    {
        // 当前大小100，目标200，需要96字节填充（考虑4字节头部）
        $extension = PaddingExtension::createForTargetSize(100, 200);
        $this->assertEquals(96, $extension->getPaddingLength());
        
        // 当前大小和目标大小相同
        $extension = PaddingExtension::createForTargetSize(100, 100);
        $this->assertEquals(0, $extension->getPaddingLength());
        
        // 目标大小只比当前大小多3字节（小于头部大小）
        $extension = PaddingExtension::createForTargetSize(100, 103);
        $this->assertEquals(0, $extension->getPaddingLength());
        
        // 正好够头部大小
        $extension = PaddingExtension::createForTargetSize(100, 104);
        $this->assertEquals(0, $extension->getPaddingLength());
        
        // 超过头部大小1字节
        $extension = PaddingExtension::createForTargetSize(100, 105);
        $this->assertEquals(1, $extension->getPaddingLength());
    }
    
    public function testCreateForTargetSizeInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target size (50) must be greater than or equal to current size (100)');
        
        PaddingExtension::createForTargetSize(100, 50);
    }
    
    public function testIsApplicableForVersion(): void
    {
        $extension = new PaddingExtension();
        
        // 适用于所有版本
        $this->assertTrue($extension->isApplicableForVersion('1.0'));
        $this->assertTrue($extension->isApplicableForVersion('1.1'));
        $this->assertTrue($extension->isApplicableForVersion('1.2'));
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
    }
    
    public function testLargePadding(): void
    {
        // 测试大填充
        $extension = new PaddingExtension(1000);
        $encoded = $extension->encode();
        
        $this->assertEquals(1000, strlen($encoded));
        $this->assertEquals(str_repeat("\x00", 1000), $encoded);
        
        $decoded = PaddingExtension::decode($encoded);
        $this->assertEquals(1000, $decoded->getPaddingLength());
    }
}