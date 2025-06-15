<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionPerformance\Extension\RecordSizeLimitExtension;

/**
 * RecordSizeLimitExtension 测试类
 */
class RecordSizeLimitExtensionTest extends TestCase
{
    public function testGetType(): void
    {
        $extension = new RecordSizeLimitExtension();
        $this->assertEquals(28, $extension->getType());
    }
    
    public function testDefaultRecordSizeLimit(): void
    {
        $extension = new RecordSizeLimitExtension();
        $this->assertEquals(RecordSizeLimitExtension::MAX_RECORD_SIZE, $extension->getRecordSizeLimit());
    }
    
    public function testSetRecordSizeLimit(): void
    {
        $extension = new RecordSizeLimitExtension();
        
        $extension->setRecordSizeLimit(1024);
        $this->assertEquals(1024, $extension->getRecordSizeLimit());
        
        $extension->setRecordSizeLimit(RecordSizeLimitExtension::MIN_RECORD_SIZE);
        $this->assertEquals(RecordSizeLimitExtension::MIN_RECORD_SIZE, $extension->getRecordSizeLimit());
        
        $extension->setRecordSizeLimit(RecordSizeLimitExtension::MAX_RECORD_SIZE);
        $this->assertEquals(RecordSizeLimitExtension::MAX_RECORD_SIZE, $extension->getRecordSizeLimit());
    }
    
    public function testSetRecordSizeLimitChaining(): void
    {
        $extension = new RecordSizeLimitExtension();
        $result = $extension->setRecordSizeLimit(1024);
        $this->assertSame($extension, $result);
    }
    
    public function testSetRecordSizeLimitTooSmall(): void
    {
        $extension = new RecordSizeLimitExtension();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit must be at least 64 bytes, 63 given');
        
        $extension->setRecordSizeLimit(63);
    }
    
    public function testSetRecordSizeLimitTooLarge(): void
    {
        $extension = new RecordSizeLimitExtension();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit must not exceed 16384 bytes, 16385 given');
        
        $extension->setRecordSizeLimit(16385);
    }
    
    public function testConstructorWithInvalidSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit must be at least 64 bytes, 32 given');
        
        new RecordSizeLimitExtension(32);
    }
    
    public function testEncode(): void
    {
        $extension = new RecordSizeLimitExtension(1024);
        $encoded = $extension->encode();
        
        $this->assertEquals(2, strlen($encoded));
        $this->assertEquals("\x04\x00", $encoded); // 1024 = 0x0400
    }
    
    public function testEncodeMinSize(): void
    {
        $extension = new RecordSizeLimitExtension(RecordSizeLimitExtension::MIN_RECORD_SIZE);
        $encoded = $extension->encode();
        
        $this->assertEquals(2, strlen($encoded));
        $this->assertEquals("\x00\x40", $encoded); // 64 = 0x0040
    }
    
    public function testEncodeMaxSize(): void
    {
        $extension = new RecordSizeLimitExtension(RecordSizeLimitExtension::MAX_RECORD_SIZE);
        $encoded = $extension->encode();
        
        $this->assertEquals(2, strlen($encoded));
        $this->assertEquals("\x40\x00", $encoded); // 16384 = 0x4000
    }
    
    public function testDecode(): void
    {
        $extension = RecordSizeLimitExtension::decode("\x04\x00"); // 1024
        $this->assertEquals(1024, $extension->getRecordSizeLimit());
    }
    
    public function testDecodeMinSize(): void
    {
        $extension = RecordSizeLimitExtension::decode("\x00\x40"); // 64
        $this->assertEquals(64, $extension->getRecordSizeLimit());
    }
    
    public function testDecodeMaxSize(): void
    {
        $extension = RecordSizeLimitExtension::decode("\x40\x00"); // 16384
        $this->assertEquals(16384, $extension->getRecordSizeLimit());
    }
    
    public function testDecodeInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit extension data must be exactly 2 bytes');
        
        RecordSizeLimitExtension::decode("\x04");
    }
    
    public function testDecodeEmptyData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit extension data must be exactly 2 bytes');
        
        RecordSizeLimitExtension::decode("");
    }
    
    public function testDecodeTooLongData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit extension data must be exactly 2 bytes');
        
        RecordSizeLimitExtension::decode("\x04\x00\x00");
    }
    
    public function testDecodeInvalidSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Record size limit must be at least 64 bytes, 32 given');
        
        RecordSizeLimitExtension::decode("\x00\x20"); // 32
    }
    
    public function testEncodeDecode(): void
    {
        $original = new RecordSizeLimitExtension(8192);
        $encoded = $original->encode();
        $decoded = RecordSizeLimitExtension::decode($encoded);
        
        $this->assertEquals($original->getRecordSizeLimit(), $decoded->getRecordSizeLimit());
    }
    
    public function testIsApplicableForVersion(): void
    {
        $extension = new RecordSizeLimitExtension();
        
        // 不适用于旧版本
        $this->assertFalse($extension->isApplicableForVersion('1.0'));
        $this->assertFalse($extension->isApplicableForVersion('1.1'));
        
        // 适用于 TLS 1.2 和 1.3
        $this->assertTrue($extension->isApplicableForVersion('1.2'));
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
    }
}