<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;
use Tourze\TLSExtensionPerformance\Extension\CompressedCertificateExtension;

/**
 * @internal
 */
#[CoversClass(CompressedCertificateExtension::class)]
final class CompressedCertificateExtensionTest extends TestCase
{
    public function testGetType(): void
    {
        $extension = new CompressedCertificateExtension();
        $this->assertEquals(27, $extension->getType());
    }

    public function testDefaultAlgorithms(): void
    {
        $extension = new CompressedCertificateExtension();
        $algorithms = $extension->getAlgorithms();

        // 至少应该有一个可用的算法（通常是 zlib）
        $this->assertNotEmpty($algorithms);

        // 检查是否只包含可用的算法
        foreach ($algorithms as $algorithm) {
            $this->assertInstanceOf(CertificateCompressionAlgorithm::class, $algorithm);
            $this->assertTrue($algorithm->isAvailable());
        }
    }

    public function testSetAlgorithms(): void
    {
        $algorithms = [
            CertificateCompressionAlgorithm::ZLIB,
            CertificateCompressionAlgorithm::BROTLI,
        ];

        $extension = new CompressedCertificateExtension();
        $extension->setAlgorithms($algorithms);

        $this->assertEquals($algorithms, $extension->getAlgorithms());
    }

    public function testSetAlgorithmsChaining(): void
    {
        $extension = new CompressedCertificateExtension();
        $extension->setAlgorithms([CertificateCompressionAlgorithm::ZLIB]);

        // Verify the algorithm was set correctly
        $this->assertSame([CertificateCompressionAlgorithm::ZLIB], $extension->getAlgorithms());
    }

    public function testSetEmptyAlgorithms(): void
    {
        $extension = new CompressedCertificateExtension();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one compression algorithm must be specified');

        $extension->setAlgorithms([]);
    }

    public function testAddAlgorithm(): void
    {
        $extension = new CompressedCertificateExtension([CertificateCompressionAlgorithm::ZLIB]);

        $extension->addAlgorithm(CertificateCompressionAlgorithm::BROTLI);

        $algorithms = $extension->getAlgorithms();
        $this->assertCount(2, $algorithms);
        $this->assertEquals(CertificateCompressionAlgorithm::ZLIB, $algorithms[0]);
        $this->assertEquals(CertificateCompressionAlgorithm::BROTLI, $algorithms[1]);
    }

    public function testAddDuplicateAlgorithm(): void
    {
        $extension = new CompressedCertificateExtension([CertificateCompressionAlgorithm::ZLIB]);

        $extension->addAlgorithm(CertificateCompressionAlgorithm::ZLIB);

        $algorithms = $extension->getAlgorithms();
        $this->assertCount(1, $algorithms);
    }

    public function testSupportsAlgorithm(): void
    {
        $extension = new CompressedCertificateExtension([
            CertificateCompressionAlgorithm::ZLIB,
            CertificateCompressionAlgorithm::BROTLI,
        ]);

        $this->assertTrue($extension->supportsAlgorithm(CertificateCompressionAlgorithm::ZLIB));
        $this->assertTrue($extension->supportsAlgorithm(CertificateCompressionAlgorithm::BROTLI));
        $this->assertFalse($extension->supportsAlgorithm(CertificateCompressionAlgorithm::ZSTD));
    }

    public function testEncode(): void
    {
        $extension = new CompressedCertificateExtension([CertificateCompressionAlgorithm::ZLIB]);
        $encoded = $extension->encode();

        // 1 byte for count + 2 bytes for algorithm ID
        $this->assertEquals(3, strlen($encoded));
        $this->assertEquals("\x01", substr($encoded, 0, 1)); // Count = 1
        $this->assertEquals("\x00\x01", substr($encoded, 1, 2)); // ZLIB = 1
    }

    public function testEncodeMultipleAlgorithms(): void
    {
        $extension = new CompressedCertificateExtension([
            CertificateCompressionAlgorithm::ZLIB,
            CertificateCompressionAlgorithm::BROTLI,
            CertificateCompressionAlgorithm::ZSTD,
        ]);
        $encoded = $extension->encode();

        // 1 byte for count + 6 bytes for algorithm IDs
        $this->assertEquals(7, strlen($encoded));
        $this->assertEquals("\x03", substr($encoded, 0, 1)); // Count = 3
        $this->assertEquals("\x00\x01", substr($encoded, 1, 2)); // ZLIB = 1
        $this->assertEquals("\x00\x02", substr($encoded, 3, 2)); // BROTLI = 2
        $this->assertEquals("\x00\x03", substr($encoded, 5, 2)); // ZSTD = 3
    }

    public function testDecode(): void
    {
        $data = "\x01\x00\x01"; // Count=1, Algorithm=ZLIB
        $extension = CompressedCertificateExtension::decode($data);

        $algorithms = $extension->getAlgorithms();
        $this->assertCount(1, $algorithms);
        $this->assertEquals(CertificateCompressionAlgorithm::ZLIB, $algorithms[0]);
    }

    public function testDecodeMultipleAlgorithms(): void
    {
        $data = "\x03\x00\x01\x00\x02\x00\x03"; // Count=3, ZLIB, BROTLI, ZSTD
        $extension = CompressedCertificateExtension::decode($data);

        $algorithms = $extension->getAlgorithms();
        $this->assertCount(3, $algorithms);
        $this->assertEquals(CertificateCompressionAlgorithm::ZLIB, $algorithms[0]);
        $this->assertEquals(CertificateCompressionAlgorithm::BROTLI, $algorithms[1]);
        $this->assertEquals(CertificateCompressionAlgorithm::ZSTD, $algorithms[2]);
    }

    public function testDecodeWithUnknownAlgorithm(): void
    {
        $data = "\x02\x00\x01\xFF\xFF"; // Count=2, ZLIB, Unknown(65535)
        $extension = CompressedCertificateExtension::decode($data);

        $algorithms = $extension->getAlgorithms();
        $this->assertCount(1, $algorithms); // Only known algorithm
        $this->assertEquals(CertificateCompressionAlgorithm::ZLIB, $algorithms[0]);
    }

    public function testDecodeEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Compressed certificate extension data is too short');

        CompressedCertificateExtension::decode('');
    }

    public function testDecodeTooShortData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Compressed certificate extension data is too short for the specified algorithm count');

        CompressedCertificateExtension::decode("\x02\x00\x01"); // Count=2 but only 1 algorithm
    }

    public function testDecodeOnlyUnknownAlgorithms(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No supported compression algorithms found in extension data');

        CompressedCertificateExtension::decode("\x01\xFF\xFF"); // Count=1, Unknown algorithm
    }

    public function testEncodeDecode(): void
    {
        $original = new CompressedCertificateExtension([
            CertificateCompressionAlgorithm::ZLIB,
            CertificateCompressionAlgorithm::BROTLI,
        ]);
        $encoded = $original->encode();
        $decoded = CompressedCertificateExtension::decode($encoded);

        $this->assertEquals($original->getAlgorithms(), $decoded->getAlgorithms());
    }

    public function testIsApplicableForVersion(): void
    {
        $extension = new CompressedCertificateExtension();

        // 不适用于旧版本
        $this->assertFalse($extension->isApplicableForVersion('1.0'));
        $this->assertFalse($extension->isApplicableForVersion('1.1'));

        // 适用于 TLS 1.2 和 1.3
        $this->assertTrue($extension->isApplicableForVersion('1.2'));
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
    }
}
