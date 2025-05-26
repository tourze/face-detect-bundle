<?php

namespace Tourze\FaceDetectBundle\Entity;

/**
 * 验证结果枚举
 */
enum VerificationResult: string
{
    case SUCCESS = 'success';  // 验证成功
    case FAILED = 'failed';    // 验证失败
    case SKIPPED = 'skipped';  // 跳过验证
    case TIMEOUT = 'timeout';  // 验证超时

    /**
     * 获取结果描述
     */
    public function getDescription(): string
    {
        return match($this) {
            self::SUCCESS => '验证成功',
            self::FAILED => '验证失败',
            self::SKIPPED => '跳过验证',
            self::TIMEOUT => '验证超时',
        };
    }

    /**
     * 检查是否为成功结果
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCESS;
    }

    /**
     * 检查是否为失败结果
     */
    public function isFailure(): bool
    {
        return $this === self::FAILED || $this === self::TIMEOUT;
    }
}
