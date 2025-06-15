<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Extension;

/**
 * 证书压缩算法枚举
 * 
 * @see https://www.iana.org/assignments/tls-extensiontype-values/tls-extensiontype-values.xhtml#certificate-compression-algorithm-ids
 */
enum CertificateCompressionAlgorithm: int
{
    /**
     * Zlib 压缩算法
     */
    case ZLIB = 1;
    
    /**
     * Brotli 压缩算法
     */
    case BROTLI = 2;
    
    /**
     * Zstandard 压缩算法
     */
    case ZSTD = 3;
    
    /**
     * 获取算法名称
     * 
     * @return string 算法名称
     */
    public function getName(): string
    {
        return match ($this) {
            self::ZLIB => 'zlib',
            self::BROTLI => 'brotli',
            self::ZSTD => 'zstd',
        };
    }
    
    /**
     * 检查算法是否在系统中可用
     * 
     * @return bool 是否可用
     */
    public function isAvailable(): bool
    {
        return match ($this) {
            self::ZLIB => function_exists('gzcompress'),
            self::BROTLI => function_exists('brotli_compress'),
            self::ZSTD => function_exists('zstd_compress'),
        };
    }
}