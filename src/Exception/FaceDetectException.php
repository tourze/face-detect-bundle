<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Exception;

/**
 * 人脸识别Bundle基础异常类
 */
class FaceDetectException extends \Exception
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
        ?\Throwable $previous = null
    ) {
        if (empty($message) && isset(self::ERROR_MESSAGES[$code])) {
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

    /**
     * 创建参数无效异常
     */
    public static function invalidParameter(string $parameter, string $reason = ''): self
    {
        $message = "参数 '{$parameter}' 无效";
        if (!empty($reason)) {
            $message .= ": {$reason}";
        }

        return new self($message, self::ERROR_INVALID_PARAMETER);
    }

    /**
     * 创建配置缺失异常
     */
    public static function configurationMissing(string $configKey): self
    {
        return new self(
            "配置项 '{$configKey}' 缺失",
            self::ERROR_CONFIGURATION_MISSING
        );
    }

    /**
     * 创建服务不可用异常
     */
    public static function serviceUnavailable(string $service, string $reason = ''): self
    {
        $message = "服务 '{$service}' 不可用";
        if (!empty($reason)) {
            $message .= ": {$reason}";
        }

        return new self($message, self::ERROR_SERVICE_UNAVAILABLE);
    }
}
