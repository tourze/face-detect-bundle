<?php

namespace Tourze\FaceDetectBundle\Enum;

/**
 * 验证类型枚举
 */
enum VerificationType: string
{
    case REQUIRED = 'required';  // 必需验证
    case OPTIONAL = 'optional';  // 可选验证
    case FORCED = 'forced';      // 强制验证

    /**
     * 获取类型描述
     */
    public function getDescription(): string
    {
        return match($this) {
            self::REQUIRED => '必需验证',
            self::OPTIONAL => '可选验证',
            self::FORCED => '强制验证',
        };
    }

    /**
     * 检查是否为强制类型
     */
    public function isMandatory(): bool
    {
        return $this === self::REQUIRED || $this === self::FORCED;
    }
}
