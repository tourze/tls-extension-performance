<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Extension;

use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionPerformance\Exception\InvalidAlgorithmException;
use Tourze\TLSExtensionPerformance\Exception\InvalidExtensionDataException;

/**
 * 压缩证书扩展实现
 *
 * 允许使用压缩算法来减少证书的传输大小
 * 这可以显著减少握手延迟，特别是在低带宽环境中
 *
 * @see https://datatracker.ietf.org/doc/html/rfc8879
 */
class CompressedCertificateExtension extends AbstractExtension
{
    /**
     * 扩展类型值
     * 注意：这是基于 RFC 8879 的值
     */
    private const EXTENSION_TYPE = 27;

    /**
     * 支持的压缩算法列表
     *
     * @var CertificateCompressionAlgorithm[]
     */
    private array $algorithms = [];

    /**
     * 构造函数
     *
     * @param CertificateCompressionAlgorithm[] $algorithms 支持的压缩算法列表
     */
    public function __construct(array $algorithms = [])
    {
        if ([] === $algorithms) {
            // 默认添加所有可用的算法
            foreach (CertificateCompressionAlgorithm::cases() as $algorithm) {
                if ($algorithm->isAvailable()) {
                    $algorithms[] = $algorithm;
                }
            }
        }

        $this->setAlgorithms($algorithms);
    }

    /**
     * 从二进制数据解码扩展
     *
     * @param string $data 二进制数据
     *
     * @return static 解码后的扩展对象
     *
     * @throws InvalidExtensionDataException 如果数据格式错误
     */
    public static function decode(string $data): static
    {
        $offset = 0;
        $length = strlen($data);

        if ($length < 1) {
            throw new InvalidExtensionDataException('Compressed certificate extension data is too short');
        }

        $algorithmCount = ord($data[$offset++]);

        if ($length < 1 + $algorithmCount * 2) {
            throw new InvalidExtensionDataException('Compressed certificate extension data is too short for the specified algorithm count');
        }

        $algorithms = [];
        for ($i = 0; $i < $algorithmCount; ++$i) {
            [$algorithmId, $offset] = self::decodeUint16($data, $offset);

            $algorithm = CertificateCompressionAlgorithm::tryFrom($algorithmId);
            if (null === $algorithm) {
                // 跳过未知的算法
                continue;
            }

            $algorithms[] = $algorithm;
        }

        if ([] === $algorithms) {
            throw new InvalidExtensionDataException('No supported compression algorithms found in extension data');
        }

        return new static($algorithms); // @phpstan-ignore-line
    }

    /**
     * 获取扩展类型
     *
     * @return int 扩展类型值
     */
    public function getType(): int
    {
        return self::EXTENSION_TYPE;
    }

    /**
     * 获取支持的压缩算法
     *
     * @return CertificateCompressionAlgorithm[] 算法列表
     */
    public function getAlgorithms(): array
    {
        return $this->algorithms;
    }

    /**
     * 设置支持的压缩算法
     *
     * @param CertificateCompressionAlgorithm[] $algorithms 算法列表
     *
     * @throws InvalidAlgorithmException 如果算法列表为空
     */
    public function setAlgorithms(array $algorithms): void
    {
        if ([] === $algorithms) {
            throw new InvalidAlgorithmException('At least one compression algorithm must be specified');
        }

        foreach ($algorithms as $algorithm) {
            if (!$algorithm instanceof CertificateCompressionAlgorithm) {
                throw new InvalidAlgorithmException('All algorithms must be instances of CertificateCompressionAlgorithm');
            }
        }

        $this->algorithms = array_values($algorithms);
    }

    /**
     * 添加支持的压缩算法
     *
     * @param CertificateCompressionAlgorithm $algorithm 算法
     */
    public function addAlgorithm(CertificateCompressionAlgorithm $algorithm): void
    {
        if (!in_array($algorithm, $this->algorithms, true)) {
            $this->algorithms[] = $algorithm;
        }
    }

    /**
     * 检查是否支持指定的压缩算法
     *
     * @param CertificateCompressionAlgorithm $algorithm 算法
     *
     * @return bool 是否支持
     */
    public function supportsAlgorithm(CertificateCompressionAlgorithm $algorithm): bool
    {
        return in_array($algorithm, $this->algorithms, true);
    }

    /**
     * 将扩展编码为二进制数据
     *
     * @return string 编码后的二进制数据
     */
    public function encode(): string
    {
        $algorithmCount = count($this->algorithms);
        $data = chr($algorithmCount);

        foreach ($this->algorithms as $algorithm) {
            $data .= $this->encodeUint16($algorithm->value);
        }

        return $data;
    }

    /**
     * 检查扩展是否适用于指定的TLS版本
     *
     * @param string $tlsVersion TLS版本
     *
     * @return bool 是否适用
     */
    public function isApplicableForVersion(string $tlsVersion): bool
    {
        // 压缩证书扩展主要用于 TLS 1.3，但也可以在 TLS 1.2 中使用
        return in_array($tlsVersion, ['1.2', '1.3'], true);
    }
}
