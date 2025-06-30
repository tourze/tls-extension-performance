<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Extension;

use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionPerformance\Exception\InvalidExtensionDataException;
use Tourze\TLSExtensionPerformance\Exception\InvalidRecordSizeLimitException;

/**
 * 记录大小限制扩展实现
 *
 * 允许端点限制其发送的记录的大小
 * 这对于内存或带宽受限的设备特别有用
 *
 * @see https://datatracker.ietf.org/doc/html/rfc8449
 */
class RecordSizeLimitExtension extends AbstractExtension
{
    /**
     * 扩展类型值
     * 注意：这是基于 RFC 8449 的值
     */
    private const EXTENSION_TYPE = 28;
    
    /**
     * 最小允许的记录大小（64字节）
     */
    public const MIN_RECORD_SIZE = 64;
    
    /**
     * 最大允许的记录大小（16384字节）
     */
    public const MAX_RECORD_SIZE = 16384;
    
    /**
     * 记录大小限制（字节）
     */
    private int $recordSizeLimit;
    
    /**
     * 构造函数
     *
     * @param int $recordSizeLimit 记录大小限制（字节）
     */
    public function __construct(int $recordSizeLimit = self::MAX_RECORD_SIZE)
    {
        $this->setRecordSizeLimit($recordSizeLimit);
    }
    
    /**
     * 从二进制数据解码扩展
     *
     * @param string $data 二进制数据
     * @return static 解码后的扩展对象
     * @throws InvalidExtensionDataException 如果数据格式错误
     */
    public static function decode(string $data): static
    {
        if (strlen($data) !== 2) {
            throw new InvalidExtensionDataException('Record size limit extension data must be exactly 2 bytes');
        }

        $offset = 0;
        $recordSizeLimit = self::decodeUint16($data, $offset);

        return new static($recordSizeLimit); // @phpstan-ignore-line
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
     * 获取记录大小限制
     *
     * @return int 记录大小限制（字节）
     */
    public function getRecordSizeLimit(): int
    {
        return $this->recordSizeLimit;
    }
    
    /**
     * 设置记录大小限制
     *
     * @param int $recordSizeLimit 记录大小限制（字节）
     * @return self
     * @throws InvalidRecordSizeLimitException 如果大小限制无效
     */
    public function setRecordSizeLimit(int $recordSizeLimit): self
    {
        if ($recordSizeLimit < self::MIN_RECORD_SIZE) {
            throw new InvalidRecordSizeLimitException(sprintf(
                'Record size limit must be at least %d bytes, %d given',
                self::MIN_RECORD_SIZE,
                $recordSizeLimit
            ));
        }

        if ($recordSizeLimit > self::MAX_RECORD_SIZE) {
            throw new InvalidRecordSizeLimitException(sprintf(
                'Record size limit must not exceed %d bytes, %d given',
                self::MAX_RECORD_SIZE,
                $recordSizeLimit
            ));
        }

        $this->recordSizeLimit = $recordSizeLimit;
        return $this;
    }
    
    /**
     * 将扩展编码为二进制数据
     *
     * @return string 编码后的二进制数据
     */
    public function encode(): string
    {
        return $this->encodeUint16($this->recordSizeLimit);
    }
    
    /**
     * 检查扩展是否适用于指定的TLS版本
     *
     * @param string $tlsVersion TLS版本
     * @return bool 是否适用
     */
    public function isApplicableForVersion(string $tlsVersion): bool
    {
        // 记录大小限制扩展主要用于 TLS 1.3
        // 但在 TLS 1.2 中也有一定的适用性
        return in_array($tlsVersion, ['1.2', '1.3'], true);
    }
}