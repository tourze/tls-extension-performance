<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionPerformance\Extension;

use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionPerformance\Exception\InvalidExtensionDataException;
use Tourze\TLSExtensionPerformance\Exception\InvalidPaddingException;

/**
 * 填充扩展实现
 *
 * 允许客户端向ClientHello消息添加填充以防止指纹识别
 * 填充数据应该是全零字节
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7685
 */
class PaddingExtension extends AbstractExtension
{
    /**
     * 最大填充长度（65535字节）
     */
    public const MAX_PADDING_LENGTH = 65535;

    /**
     * 填充长度（字节）
     */
    private int $paddingLength;

    /**
     * 构造函数
     *
     * @param int $paddingLength 填充长度（字节）
     */
    public function __construct(int $paddingLength = 0)
    {
        $this->setPaddingLength($paddingLength);
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
        $length = strlen($data);

        // 验证所有字节都是零
        for ($i = 0; $i < $length; ++$i) {
            if ("\x00" !== $data[$i]) {
                throw new InvalidExtensionDataException(sprintf('Padding extension data must contain only zero bytes, found non-zero byte at position %d', $i));
            }
        }

        return new static($length); // @phpstan-ignore-line
    }

    /**
     * 创建一个填充扩展以使消息达到目标大小
     *
     * @param int $currentSize 当前消息大小
     * @param int $targetSize  目标消息大小
     *
     * @return static 填充扩展
     *
     * @throws InvalidPaddingException 如果目标大小小于当前大小
     */
    public static function createForTargetSize(int $currentSize, int $targetSize): static
    {
        if ($targetSize < $currentSize) {
            throw new InvalidPaddingException(sprintf('Target size (%d) must be greater than or equal to current size (%d)', $targetSize, $currentSize));
        }

        // 计算需要的填充长度
        // 需要考虑扩展头部的4字节（2字节类型 + 2字节长度）
        $extensionHeaderSize = 4;
        $paddingNeeded = $targetSize - $currentSize - $extensionHeaderSize;

        if ($paddingNeeded < 0) {
            $paddingNeeded = 0;
        }

        return new static($paddingNeeded); // @phpstan-ignore-line
    }

    /**
     * 获取扩展类型
     *
     * @return int 扩展类型值
     */
    public function getType(): int
    {
        return ExtensionType::PADDING->value;
    }

    /**
     * 获取填充长度
     *
     * @return int 填充长度（字节）
     */
    public function getPaddingLength(): int
    {
        return $this->paddingLength;
    }

    /**
     * 设置填充长度
     *
     * @param int $paddingLength 填充长度（字节）
     *
     * @throws InvalidPaddingException 如果填充长度无效
     */
    public function setPaddingLength(int $paddingLength): void
    {
        if ($paddingLength < 0) {
            throw new InvalidPaddingException('Padding length cannot be negative');
        }

        if ($paddingLength > self::MAX_PADDING_LENGTH) {
            throw new InvalidPaddingException(sprintf('Padding length cannot exceed %d bytes, %d given', self::MAX_PADDING_LENGTH, $paddingLength));
        }

        $this->paddingLength = $paddingLength;
    }

    /**
     * 将扩展编码为二进制数据
     *
     * @return string 编码后的二进制数据
     */
    public function encode(): string
    {
        // 填充扩展的内容是指定长度的零字节
        return str_repeat("\x00", $this->paddingLength);
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
        // 填充扩展可以用于所有TLS版本
        return true;
    }
}
