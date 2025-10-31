<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Tests\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;

/**
 * @internal
 */
#[CoversClass(CertificateCompressionAlgorithm::class)]
final class CertificateCompressionAlgorithmTest extends AbstractEnumTestCase
{
    public function testValues(): void
    {
        $this->assertEquals(1, CertificateCompressionAlgorithm::ZLIB->value);
        $this->assertEquals(2, CertificateCompressionAlgorithm::BROTLI->value);
        $this->assertEquals(3, CertificateCompressionAlgorithm::ZSTD->value);
    }

    public function testGetName(): void
    {
        $this->assertEquals('zlib', CertificateCompressionAlgorithm::ZLIB->getName());
        $this->assertEquals('brotli', CertificateCompressionAlgorithm::BROTLI->getName());
        $this->assertEquals('zstd', CertificateCompressionAlgorithm::ZSTD->getName());
    }

    public function testIsAvailable(): void
    {
        // ZLIB 通常是可用的，因为它是 PHP 核心扩展的一部分
        $this->assertEquals(
            function_exists('gzcompress'),
            CertificateCompressionAlgorithm::ZLIB->isAvailable()
        );

        // Brotli 和 Zstd 通常需要额外安装
        $this->assertEquals(
            function_exists('brotli_compress'),
            CertificateCompressionAlgorithm::BROTLI->isAvailable()
        );

        $this->assertEquals(
            function_exists('zstd_compress'),
            CertificateCompressionAlgorithm::ZSTD->isAvailable()
        );
    }

    public function testCases(): void
    {
        $cases = CertificateCompressionAlgorithm::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(CertificateCompressionAlgorithm::ZLIB, $cases);
        $this->assertContains(CertificateCompressionAlgorithm::BROTLI, $cases);
        $this->assertContains(CertificateCompressionAlgorithm::ZSTD, $cases);
    }

    public function testTryFrom(): void
    {
        $this->assertEquals(CertificateCompressionAlgorithm::ZLIB, CertificateCompressionAlgorithm::tryFrom(1));
        $this->assertEquals(CertificateCompressionAlgorithm::BROTLI, CertificateCompressionAlgorithm::tryFrom(2));
        $this->assertEquals(CertificateCompressionAlgorithm::ZSTD, CertificateCompressionAlgorithm::tryFrom(3));
        $this->assertNull(CertificateCompressionAlgorithm::tryFrom(999));
    }

    public function testToArray(): void
    {
        $zlibArray = CertificateCompressionAlgorithm::ZLIB->toArray();
        $this->assertEquals([
            'value' => 1,
            'label' => 'Zlib 压缩',
        ], $zlibArray);

        $brotliArray = CertificateCompressionAlgorithm::BROTLI->toArray();
        $this->assertEquals([
            'value' => 2,
            'label' => 'Brotli 压缩',
        ], $brotliArray);

        $zstdArray = CertificateCompressionAlgorithm::ZSTD->toArray();
        $this->assertEquals([
            'value' => 3,
            'label' => 'Zstandard 压缩',
        ], $zstdArray);
    }
}
