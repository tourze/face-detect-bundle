<?php

namespace Tourze\FaceDetectBundle\Entity;

/**
 * 人脸档案状态枚举
 */
enum FaceProfileStatus: string
{
    case ACTIVE = 'active';      // 活跃状态
    case EXPIRED = 'expired';    // 已过期
    case DISABLED = 'disabled';  // 已禁用

    /**
     * 获取状态描述
     */
    public function getDescription(): string
    {
        return match($this) {
            self::ACTIVE => '活跃',
            self::EXPIRED => '已过期',
            self::DISABLED => '已禁用',
        };
    }

    /**
     * 检查是否为可用状态
     */
    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }
}
