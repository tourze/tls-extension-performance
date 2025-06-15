<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionPerformance\Extension\MaxFragmentLengthExtension;

/**
 * MaxFragmentLengthExtension 测试类
 */
class MaxFragmentLengthExtensionTest extends TestCase
{
    public function testGetType(): void
    {
        $extension = new MaxFragmentLengthExtension();
        $this->assertEquals(ExtensionType::MAX_FRAGMENT_LENGTH->value, $extension->getType());
    }
    
    public function testDefaultLength(): void
    {
        $extension = new MaxFragmentLengthExtension();
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_4096, $extension->getLength());
        $this->assertEquals(4096, $extension->getLengthInBytes());
    }
    
    public function testSetLength(): void
    {
        $extension = new MaxFragmentLengthExtension();
        
        $extension->setLength(MaxFragmentLengthExtension::LENGTH_512);
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_512, $extension->getLength());
        $this->assertEquals(512, $extension->getLengthInBytes());
        
        $extension->setLength(MaxFragmentLengthExtension::LENGTH_1024);
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_1024, $extension->getLength());
        $this->assertEquals(1024, $extension->getLengthInBytes());
        
        $extension->setLength(MaxFragmentLengthExtension::LENGTH_2048);
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_2048, $extension->getLength());
        $this->assertEquals(2048, $extension->getLengthInBytes());
        
        $extension->setLength(MaxFragmentLengthExtension::LENGTH_4096);
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_4096, $extension->getLength());
        $this->assertEquals(4096, $extension->getLengthInBytes());
    }
    
    public function testSetLengthChaining(): void
    {
        $extension = new MaxFragmentLengthExtension();
        $result = $extension->setLength(MaxFragmentLengthExtension::LENGTH_1024);
        $this->assertSame($extension, $result);
    }
    
    public function testSetInvalidLength(): void
    {
        $extension = new MaxFragmentLengthExtension();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid max fragment length value: 5');
        
        $extension->setLength(5);
    }
    
    public function testEncode(): void
    {
        $extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_512);
        $encoded = $extension->encode();
        $this->assertEquals("\x01", $encoded);
        
        $extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_1024);
        $encoded = $extension->encode();
        $this->assertEquals("\x02", $encoded);
        
        $extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_2048);
        $encoded = $extension->encode();
        $this->assertEquals("\x03", $encoded);
        
        $extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_4096);
        $encoded = $extension->encode();
        $this->assertEquals("\x04", $encoded);
    }
    
    public function testDecode(): void
    {
        $extension = MaxFragmentLengthExtension::decode("\x01");
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_512, $extension->getLength());
        
        $extension = MaxFragmentLengthExtension::decode("\x02");
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_1024, $extension->getLength());
        
        $extension = MaxFragmentLengthExtension::decode("\x03");
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_2048, $extension->getLength());
        
        $extension = MaxFragmentLengthExtension::decode("\x04");
        $this->assertEquals(MaxFragmentLengthExtension::LENGTH_4096, $extension->getLength());
    }
    
    public function testDecodeInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max fragment length extension data must be exactly 1 byte');
        
        MaxFragmentLengthExtension::decode("\x01\x02");
    }
    
    public function testDecodeEmptyData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max fragment length extension data must be exactly 1 byte');
        
        MaxFragmentLengthExtension::decode("");
    }
    
    public function testDecodeInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid max fragment length value: 5');
        
        MaxFragmentLengthExtension::decode("\x05");
    }
    
    public function testEncodeDecode(): void
    {
        $original = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_2048);
        $encoded = $original->encode();
        $decoded = MaxFragmentLengthExtension::decode($encoded);
        
        $this->assertEquals($original->getLength(), $decoded->getLength());
        $this->assertEquals($original->getLengthInBytes(), $decoded->getLengthInBytes());
    }
    
    public function testIsApplicableForVersion(): void
    {
        $extension = new MaxFragmentLengthExtension();
        
        // 默认适用于所有版本
        $this->assertTrue($extension->isApplicableForVersion('1.0'));
        $this->assertTrue($extension->isApplicableForVersion('1.1'));
        $this->assertTrue($extension->isApplicableForVersion('1.2'));
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
    }
}