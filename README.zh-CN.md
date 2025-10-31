# TLS 扩展性能包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/tls-extension-performance.svg?style=flat-square)](https://php.net)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![License](https://img.shields.io/packagist/l/tourze/tls-extension-performance.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-performance)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个全面的 PHP 库，实现与性能相关的 TLS 扩展，用于优化 TLS 握手效率、减少内存使用和提高传输性能。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [快速开始](#快速开始)
  - [最大片段长度扩展](#最大片段长度扩展)
  - [记录大小限制扩展](#记录大小限制扩展)
  - [填充扩展](#填充扩展)
  - [压缩证书扩展](#压缩证书扩展)
- [完整使用示例](#完整使用示例)
- [使用场景](#使用场景)
  - [移动应用和 IoT 设备](#移动应用和-iot-设备)
  - [高延迟网络](#高延迟网络)
  - [隐私保护](#隐私保护)
  - [带宽受限环境](#带宽受限环境)
- [API 文档](#api-文档)
  - [扩展类](#扩展类)
  - [压缩算法](#压缩算法)
  - [异常类](#异常类)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- **最大片段长度扩展 (RFC 6066)** - 通过协商更小的记录片段来减少内存使用
- **记录大小限制扩展 (RFC 8449)** - 为带宽受限环境控制最大记录大小
- **填充扩展 (RFC 7685)** - 添加填充以防止指纹攻击
- **压缩证书扩展 (RFC 8879)** - 通过压缩算法减少证书传输大小
- **类型安全的 PHP 8.1+ 实现** - 完整的类型提示和现代 PHP 特性
- **全面的测试覆盖** - 使用 PHPUnit 进行充分测试

## 系统要求

- PHP 8.1 或更高版本
- Composer

## 安装

```bash
composer require tourze/tls-extension-performance
```

## 快速开始

### 最大片段长度扩展

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\MaxFragmentLengthExtension;

// 创建 1KB 片段大小的扩展
$extension = new MaxFragmentLengthExtension(MaxFragmentLengthExtension::LENGTH_1024);

// 获取配置的值
echo $extension->getLength(); // 2 (LENGTH_1024 常量)
echo $extension->getLengthInBytes(); // 1024

// 编码以便传输
$encoded = $extension->encode();

// 从二进制数据解码
$decoded = MaxFragmentLengthExtension::decode($encoded);
```

### 记录大小限制扩展

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\RecordSizeLimitExtension;

// 将记录大小限制为 8KB
$extension = new RecordSizeLimitExtension(8192);

// 检查是否适用于 TLS 版本
if ($extension->isApplicableForVersion('1.3')) {
    $encoded = $extension->encode();
}
```

### 填充扩展

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\PaddingExtension;

// 添加 100 字节填充
$extension = new PaddingExtension(100);

// 或创建填充以达到目标消息大小
$currentSize = 500;
$targetSize = 1000;
$extension = PaddingExtension::createForTargetSize($currentSize, $targetSize);

echo $extension->getPaddingLength(); // 496 (1000-500-4 字节头部)
```

### 压缩证书扩展

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\CompressedCertificateExtension;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;

// 自动检测可用的压缩算法
$extension = new CompressedCertificateExtension();

// 或指定特定算法
$extension = new CompressedCertificateExtension([
    CertificateCompressionAlgorithm::ZLIB,
    CertificateCompressionAlgorithm::BROTLI,
]);

// 检查算法支持
if ($extension->supportsAlgorithm(CertificateCompressionAlgorithm::ZLIB)) {
    echo "支持 Zlib 压缩\n";
}

// 获取所有支持的算法
foreach ($extension->getAlgorithms() as $algorithm) {
    echo $algorithm->getName() . ": " . $algorithm->getLabel() . "\n";
}
```

## 完整使用示例

```php
<?php

use Tourze\TLSExtensionPerformance\Extension\MaxFragmentLengthExtension;
use Tourze\TLSExtensionPerformance\Extension\RecordSizeLimitExtension;
use Tourze\TLSExtensionPerformance\Extension\PaddingExtension;
use Tourze\TLSExtensionPerformance\Extension\CompressedCertificateExtension;
use Tourze\TLSExtensionPerformance\Extension\CertificateCompressionAlgorithm;

// 资源有限的 IoT 设备配置
class IoTDeviceTLSConfig
{
    private array $extensions = [];
    
    public function __construct()
    {
        // 1. 将片段大小限制为 1KB 以减少内存使用
        $this->extensions[] = new MaxFragmentLengthExtension(
            MaxFragmentLengthExtension::LENGTH_1024
        );
        
        // 2. 将记录大小限制为 2KB
        $this->extensions[] = new RecordSizeLimitExtension(2048);
        
        // 3. 启用证书压缩（仅使用 Zlib 以节省 CPU）
        $this->extensions[] = new CompressedCertificateExtension([
            CertificateCompressionAlgorithm::ZLIB
        ]);
        
        // 4. 添加填充以隐藏设备类型
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
        // 计算当前 ClientHello 消息大小
        return 300; // 示例值
    }
}

// 使用方法
$config = new IoTDeviceTLSConfig();
$extensions = $config->getExtensions();

foreach ($extensions as $extension) {
    echo sprintf(
        "扩展类型 %d: %s\n", 
        $extension->getType(),
        bin2hex($extension->encode())
    );
}
```

## 使用场景

### 移动应用和 IoT 设备
- 使用 `MaxFragmentLengthExtension` 减少内存占用
- 使用 `RecordSizeLimitExtension` 适应有限的缓冲区大小
- 为受限的硬件资源进行优化

### 高延迟网络
- 使用 `CompressedCertificateExtension` 减少证书传输时间
- 选择合适的压缩算法以平衡 CPU 使用和传输效率

### 隐私保护
- 使用 `PaddingExtension` 防止基于消息大小的指纹攻击
- 统一 ClientHello 消息大小以提高匿名性

### 带宽受限环境
- 组合使用多个扩展以最大化减少数据传输
- 根据网络条件动态调整参数

## API 文档

### 扩展类

- `MaxFragmentLengthExtension` - 协商更小的片段大小 (512B, 1KB, 2KB, 4KB)
- `RecordSizeLimitExtension` - 限制记录大小 (64-16384 字节)
- `PaddingExtension` - 添加填充 (0-65535 字节)
- `CompressedCertificateExtension` - 支持证书压缩

### 压缩算法

- `ZLIB` - 标准 zlib 压缩（需要 `gzcompress`）
- `BROTLI` - Brotli 压缩（需要 `brotli_compress`）
- `ZSTD` - Zstandard 压缩（需要 `zstd_compress`）

### 异常类

- `InvalidExtensionDataException` - 扩展数据格式错误
- `InvalidFragmentLengthException` - 无效的片段长度值
- `InvalidRecordSizeLimitException` - 无效的记录大小限制
- `InvalidPaddingException` - 无效的填充参数
- `InvalidAlgorithmException` - 无效的压缩算法
- `ExtensionLogicException` - 扩展内部逻辑错误

## 测试

```bash
# 从项目根目录运行测试
./vendor/bin/phpunit packages/tls-extension-performance/tests

# 运行 PHPStan 分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/tls-extension-performance
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

## 许可证

MIT 许可证。请参阅 [License File](LICENSE) 获取更多信息。 