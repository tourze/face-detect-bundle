<?php

namespace Tourze\FaceDetectBundle\Enum;

/**
 * 操作状态枚举
 */
enum OperationStatus: string
{
    case PENDING = 'pending';        // 等待中
    case PROCESSING = 'processing';  // 处理中
    case COMPLETED = 'completed';    // 已完成
    case FAILED = 'failed';          // 失败
    case CANCELLED = 'cancelled';    // 已取消

    /**
     * 获取状态描述
     */
    public function getDescription(): string
    {
        return match($this) {
            self::PENDING => '等待中',
            self::PROCESSING => '处理中',
            self::COMPLETED => '已完成',
            self::FAILED => '失败',
            self::CANCELLED => '已取消',
        };
    }

    /**
     * 检查是否为终态
     */
    public function isFinal(): bool
    {
        return $this === self::COMPLETED || $this === self::FAILED || $this === self::CANCELLED;
    }

    /**
     * 检查是否为成功状态
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }
}
