<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Extension;

use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionPerformance\Exception\ExtensionLogicException;
use Tourze\TLSExtensionPerformance\Exception\InvalidExtensionDataException;
use Tourze\TLSExtensionPerformance\Exception\InvalidFragmentLengthException;

/**
 * 最大分片长度扩展实现
 *
 * 允许客户端和服务器协商较小的最大记录片段大小
 * 这可以减少内存使用并提高性能，特别是对于内存受限的设备
 *
 * @see https://datatracker.ietf.org/doc/html/rfc6066#section-4
 */
class MaxFragmentLengthExtension extends AbstractExtension
{
    /**
     * 512字节
     */
    public const LENGTH_512 = 1;

    /**
     * 1024字节
     */
    public const LENGTH_1024 = 2;

    /**
     * 2048字节
     */
    public const LENGTH_2048 = 3;

    /**
     * 4096字节
     */
    public const LENGTH_4096 = 4;

    /**
     * 最大片段长度值
     */
    private int $length;

    /**
     * 构造函数
     *
     * @param int $length 最大片段长度值
     */
    public function __construct(int $length = self::LENGTH_4096)
    {
        $this->setLength($length);
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
        if (1 !== strlen($data)) {
            throw new InvalidExtensionDataException('Max fragment length extension data must be exactly 1 byte');
        }

        $length = ord($data[0]);

        return new static($length); // @phpstan-ignore-line
    }

    /**
     * 获取扩展类型
     *
     * @return int 扩展类型值
     */
    public function getType(): int
    {
        return ExtensionType::MAX_FRAGMENT_LENGTH->value;
    }

    /**
     * 获取最大片段长度
     *
     * @return int 最大片段长度值
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * 设置最大片段长度
     *
     * @param int $length 最大片段长度值
     *
     * @throws InvalidFragmentLengthException 如果长度值无效
     */
    public function setLength(int $length): void
    {
        if (!in_array($length, [self::LENGTH_512, self::LENGTH_1024, self::LENGTH_2048, self::LENGTH_4096], true)) {
            throw new InvalidFragmentLengthException(sprintf('Invalid max fragment length value: %d', $length));
        }

        $this->length = $length;
    }

    /**
     * 获取实际的字节数
     *
     * @return int 实际的最大片段长度（字节）
     */
    public function getLengthInBytes(): int
    {
        return match ($this->length) {
            self::LENGTH_512 => 512,
            self::LENGTH_1024 => 1024,
            self::LENGTH_2048 => 2048,
            self::LENGTH_4096 => 4096,
            default => throw new ExtensionLogicException('Invalid fragment length: ' . $this->length),
        };
    }

    /**
     * 将扩展编码为二进制数据
     *
     * @return string 编码后的二进制数据
     */
    public function encode(): string
    {
        // 扩展数据只包含一个字节的长度值
        return chr($this->length);
    }
}
