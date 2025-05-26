<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Exception;

/**
 * 人脸验证异常类
 */
class VerificationException extends FaceDetectException
{
    /**
     * 人脸验证相关错误码
     */
    public const ERROR_VERIFICATION_FAILED = 3001;
    public const ERROR_SIMILARITY_TOO_LOW = 3002;
    public const ERROR_FACE_PROFILE_NOT_FOUND = 3003;
    public const ERROR_VERIFICATION_EXPIRED = 3004;
    public const ERROR_VERIFICATION_LIMIT_EXCEEDED = 3005;
    public const ERROR_STRATEGY_NOT_FOUND = 3006;
    public const ERROR_VERIFICATION_TIMEOUT = 3007;

    /**
     * 错误码映射
     */
    private const ERROR_MESSAGES = [
        self::ERROR_VERIFICATION_FAILED => '人脸验证失败',
        self::ERROR_SIMILARITY_TOO_LOW => '人脸相似度过低',
        self::ERROR_FACE_PROFILE_NOT_FOUND => '未找到人脸档案',
        self::ERROR_VERIFICATION_EXPIRED => '验证已过期',
        self::ERROR_VERIFICATION_LIMIT_EXCEEDED => '验证次数超限',
        self::ERROR_STRATEGY_NOT_FOUND => '未找到验证策略',
        self::ERROR_VERIFICATION_TIMEOUT => '验证超时',
    ];

    public function __construct(
        string $message = '',
        int $code = self::ERROR_VERIFICATION_FAILED,
        ?\Throwable $previous = null
    ) {
        if (empty($message) && isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * 创建相似度过低异常
     */
    public static function similarityTooLow(float $similarity, float $threshold): self
    {
        return new self(
            "人脸相似度 {$similarity} 低于阈值 {$threshold}",
            self::ERROR_SIMILARITY_TOO_LOW
        );
    }

    /**
     * 创建人脸档案未找到异常
     */
    public static function faceProfileNotFound(string $userId): self
    {
        return new self(
            "用户 {$userId} 的人脸档案不存在",
            self::ERROR_FACE_PROFILE_NOT_FOUND
        );
    }

    /**
     * 创建验证已过期异常
     */
    public static function verificationExpired(string $userId, string $businessType): self
    {
        return new self(
            "用户 {$userId} 在业务 {$businessType} 的验证已过期",
            self::ERROR_VERIFICATION_EXPIRED
        );
    }

    /**
     * 创建验证次数超限异常
     */
    public static function verificationLimitExceeded(int $currentCount, int $maxCount): self
    {
        return new self(
            "验证次数 {$currentCount} 超过限制 {$maxCount}",
            self::ERROR_VERIFICATION_LIMIT_EXCEEDED
        );
    }

    /**
     * 创建验证策略未找到异常
     */
    public static function strategyNotFound(string $businessType): self
    {
        return new self(
            "业务类型 {$businessType} 的验证策略不存在",
            self::ERROR_STRATEGY_NOT_FOUND
        );
    }

    /**
     * 创建验证超时异常
     */
    public static function verificationTimeout(int $timeoutSeconds): self
    {
        return new self(
            "验证超时，超过 {$timeoutSeconds} 秒",
            self::ERROR_VERIFICATION_TIMEOUT
        );
    }
} 