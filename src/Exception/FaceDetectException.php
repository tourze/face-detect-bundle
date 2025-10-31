<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Exception;

/**
 * 人脸识别Bundle基础异常类
 */
abstract class FaceDetectException extends \Exception
{
    /**
     * 错误码常量
     */
    public const ERROR_UNKNOWN = 1000;
    public const ERROR_INVALID_PARAMETER = 1001;
    public const ERROR_CONFIGURATION_MISSING = 1002;
    public const ERROR_SERVICE_UNAVAILABLE = 1003;

    /**
     * 错误码映射
     */
    private const ERROR_MESSAGES = [
        self::ERROR_UNKNOWN => '未知错误',
        self::ERROR_INVALID_PARAMETER => '参数无效',
        self::ERROR_CONFIGURATION_MISSING => '配置缺失',
        self::ERROR_SERVICE_UNAVAILABLE => '服务不可用',
    ];

    public function __construct(
        string $message = '',
        int $code = self::ERROR_UNKNOWN,
        ?\Throwable $previous = null,
    ) {
        if ('' === $message && isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取错误码对应的默认消息
     */
    public static function getErrorMessage(int $code): string
    {
        return self::ERROR_MESSAGES[$code] ?? '未知错误';
    }
}
