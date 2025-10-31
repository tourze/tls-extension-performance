# TLS Extension Performance

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/tls-extension-performance.svg?style=flat-square)](https://php.net)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![License](https://img.shields.io/packagist/l/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A comprehensive PHP library implementing performance-related TLS extensions for optimizing TLS handshake efficiency, reducing memory usage, and improving transmission performance.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
  - [Maximum Fragment Length Extension](#maximum-fragment-length-extension)
  - [Record Size Limit Extension](#record-size-limit-extension)
  - [Padding Extension](#padding-extension)
  - [Compressed Certificate Extension](#compressed-certificate-extension)
- [Complete Usage Example](#complete-usage-example)
- [Use Cases](#use-cases)
  - [Mobile Applications and IoT Devices](#mobile-applications-and-iot-devices)
  - [High-Latency Networks](#high-latency-networks)
  - [Privacy Protection](#privacy-protection)
  - [Bandwidth-Constrained Environments](#bandwidth-constrained-environments)
- [API Documentation](#api-documentation)
  - [Extensions](#extensions)
  - [Compression Algorithms](#compression-algorithms)
  - [Exceptions](#exceptions)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Maximum Fragment Length Extension (RFC 6066)** - Reduce memory usage by negotiating smaller record fragments
- **Record Size Limit Extension (RFC 8449)** - Control maximum record size for bandwidth-constrained environments
- **Padding Extension (RFC 7685)** - Add padding to prevent fingerprinting attacks
- **Compressed Certificate Extension (RFC 8879)** - Reduce certificate transmission size with compression algorithms
- **Type-safe PHP 8.1+ implementation** - Full type hints and modern PHP features
- **Comprehensive test coverage** - Thoroughly tested with PHPUnit

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require tourze/tls-extension-performance
```

## Quick Start

### Maximum Fragment Length Extension

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\MaxFragmentLengthExtension;

// Create extension with 1KB fragment size
$extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_1024);

// Get configured values
echo $extension->getLength(); // 2 (LENGTH_1024 constant)
echo $extension->getLengthInBytes(); // 1024

// Encode for transmission
$encoded = $extension->encode();

// Decode from binary data
$decoded = MaxFragmentLengthExtension::decode($encoded);
```

### Record Size Limit Extension

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\RecordSizeLimitExtension;

// Limit record size to 8KB
$extension = new RecordSizeLimitExtension(8192);

// Check if applicable for TLS version
if ($extension->isApplicableForVersion('1.3')) {
    $encoded = $extension->encode();
}
```

### Padding Extension

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\PaddingExtension;

// Add 100 bytes of padding
$extension = new PaddingExtension(100);

// Or create padding to reach target message size
$currentSize = 500;
$targetSize = 1000;
$extension = PaddingExtension::createForTargetSize($currentSize, $targetSize);

echo $extension->getPaddingLength(); // 496 (1000-500-4 bytes header)
```

### Compressed Certificate Extension

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\CompressedCertificateExtension;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;

// Auto-detect available compression algorithms
$extension = new CompressedCertificateExtension();

// Or specify specific algorithms
$extension = new CompressedCertificateExtension([
    CertificateCompressionAlgorithm::ZLIB,
    CertificateCompressionAlgorithm::BROTLI,
]);

// Check algorithm support
if ($extension->supportsAlgorithm(CertificateCompressionAlgorithm::ZLIB)) {
    echo "Zlib compression supported\n";
}

// Get all supported algorithms
foreach ($extension->getAlgorithms() as $algorithm) {
    echo $algorithm->getName() . ": " . $algorithm->getLabel() . "\n";
}
```

## Complete Usage Example

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\MaxFragmentLengthExtension;
use Tourze\TLSExtensionPerformance\Extension\RecordSizeLimitExtension;
use Tourze\TLSExtensionPerformance\Extension\PaddingExtension;
use Tourze\TLSExtensionPerformance\Extension\CompressedCertificateExtension;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;

// Configuration for IoT device with limited resources
class IoTDeviceTLSConfig
{
    private array $extensions = [];
    
    public function __construct()
    {
        // 1. Limit fragment size to 1KB to reduce memory usage
        $this->extensions[] = new MaxFragmentLengthExtension(
            MaxFragmentLengthExtension::LENGTH_1024
        );
        
        // 2. Limit record size to 2KB
        $this->extensions[] = new RecordSizeLimitExtension(2048);
        
        // 3. Enable certificate compression (Zlib only to save CPU)
        $this->extensions[] = new CompressedCertificateExtension([
            CertificateCompressionAlgorithm::ZLIB
        ]);
        
        // 4. Add padding to hide device type
        $currentMessageSize = $this->calculateCurrentMessageSize();
        $targetSize = 512;
        if ($currentMessageSize < $targetSize) {
            $this->extensions[] = PaddingExtension::createForTargetSize(
                $currentMessageSize, 
                $targetSize
            );
        }
    }
    
    public function getExtensions(): array
    {
        return $this->extensions;
    }
    
    public function encodeExtensions(): array
    {
        $encoded = [];
        foreach ($this->extensions as $extension) {
            $encoded[] = [
                'type' => $extension->getType(),
                'data' => $extension->encode()
            ];
        }
        return $encoded;
    }
    
    private function calculateCurrentMessageSize(): int
    {
        // Calculate current ClientHello message size
        return 300; // Example value
    }
}

// Usage
$config = new IoTDeviceTLSConfig();
$extensions = $config->getExtensions();

foreach ($extensions as $extension) {
    echo sprintf(
        "Extension type %d: %s\n", 
        $extension->getType(),
        bin2hex($extension->encode())
    );
}
```

## Use Cases

### Mobile Applications and IoT Devices
- Use `MaxFragmentLengthExtension` to reduce memory footprint
- Use `RecordSizeLimitExtension` to adapt to limited buffer sizes
- Optimize for constrained hardware resources

### High-Latency Networks
- Use `CompressedCertificateExtension` to reduce certificate transmission time
- Choose appropriate compression algorithms to balance CPU usage and transmission efficiency

### Privacy Protection
- Use `PaddingExtension` to prevent message size-based fingerprinting
- Standardize ClientHello message sizes for improved anonymity

### Bandwidth-Constrained Environments
- Combine multiple extensions to maximize data transmission reduction
- Dynamically adjust parameters based on network conditions

## API Documentation

### Extensions

- `MaxFragmentLengthExtension` - Negotiate smaller fragment sizes (512B, 1KB, 2KB, 4KB)
- `RecordSizeLimitExtension` - Limit record size (64-16384 bytes)
- `PaddingExtension` - Add padding (0-65535 bytes)
- `CompressedCertificateExtension` - Support certificate compression

### Compression Algorithms

- `ZLIB` - Standard zlib compression (requires `gzcompress`)
- `BROTLI` - Brotli compression (requires `brotli_compress`)
- `ZSTD` - Zstandard compression (requires `zstd_compress`)

### Exceptions

- `InvalidExtensionDataException` - Invalid extension data format
- `InvalidFragmentLengthException` - Invalid fragment length value
- `InvalidRecordSizeLimitException` - Invalid record size limit
- `InvalidPaddingException` - Invalid padding parameters
- `InvalidAlgorithmException` - Invalid compression algorithm
- `ExtensionLogicException` - Extension internal logic error

## Testing

```bash
# Run tests from project root
./vendor/bin/phpunit packages/tls-extension-performance/tests

# Run PHPStan analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/tls-extension-performance
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
