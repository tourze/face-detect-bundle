<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Exception;

/**
 * 百度AI接口异常类
 */
class BaiduAiException extends FaceDetectException
{
    /**
     * 百度AI相关错误码
     */
    public const ERROR_API_REQUEST_FAILED = 4001;
    public const ERROR_ACCESS_TOKEN_INVALID = 4002;
    public const ERROR_API_QUOTA_EXCEEDED = 4003;
    public const ERROR_API_RESPONSE_INVALID = 4004;
    public const ERROR_NETWORK_TIMEOUT = 4005;
    public const ERROR_API_RATE_LIMITED = 4006;
    public const ERROR_API_PERMISSION_DENIED = 4007;

    /**
     * 错误码映射
     */
    private const ERROR_MESSAGES = [
        self::ERROR_API_REQUEST_FAILED => 'API请求失败',
        self::ERROR_ACCESS_TOKEN_INVALID => 'Access Token无效',
        self::ERROR_API_QUOTA_EXCEEDED => 'API配额超限',
        self::ERROR_API_RESPONSE_INVALID => 'API响应格式无效',
        self::ERROR_NETWORK_TIMEOUT => '网络请求超时',
        self::ERROR_API_RATE_LIMITED => 'API调用频率超限',
        self::ERROR_API_PERMISSION_DENIED => 'API权限不足',
    ];

    /**
     * 百度API错误码映射
     */
    private const BAIDU_ERROR_MAPPING = [
        110 => self::ERROR_ACCESS_TOKEN_INVALID,
        111 => self::ERROR_ACCESS_TOKEN_INVALID,
        17 => self::ERROR_API_QUOTA_EXCEEDED,
        18 => self::ERROR_API_RATE_LIMITED,
        19 => self::ERROR_API_RATE_LIMITED,
        216100 => self::ERROR_INVALID_PARAMETER,
        216101 => self::ERROR_INVALID_PARAMETER,
        216102 => self::ERROR_INVALID_PARAMETER,
        216103 => self::ERROR_INVALID_PARAMETER,
        216110 => self::ERROR_API_REQUEST_FAILED,
        216200 => self::ERROR_API_PERMISSION_DENIED,
    ];

    public function __construct(
        string $message = '',
        int $code = self::ERROR_API_REQUEST_FAILED,
        ?\Throwable $previous = null
    ) {
        if (empty($message) && isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * 根据百度API错误码创建异常
     */
    public static function fromBaiduError(int $baiduErrorCode, string $baiduErrorMsg = ''): self
    {
        $code = self::BAIDU_ERROR_MAPPING[$baiduErrorCode] ?? self::ERROR_API_REQUEST_FAILED;
        $message = !empty($baiduErrorMsg) ? $baiduErrorMsg : self::ERROR_MESSAGES[$code];

        return new self(
            "百度API错误 [{$baiduErrorCode}]: {$message}",
            $code
        );
    }

    /**
     * 创建API请求失败异常
     */
    public static function apiRequestFailed(string $endpoint, string $reason = ''): self
    {
        $message = "API请求失败: {$endpoint}";
        if (!empty($reason)) {
            $message .= " - {$reason}";
        }

        return new self($message, self::ERROR_API_REQUEST_FAILED);
    }

    /**
     * 创建Access Token无效异常
     */
    public static function accessTokenInvalid(): self
    {
        return new self('', self::ERROR_ACCESS_TOKEN_INVALID);
    }

    /**
     * 创建API配额超限异常
     */
    public static function quotaExceeded(): self
    {
        return new self('', self::ERROR_API_QUOTA_EXCEEDED);
    }

    /**
     * 创建网络超时异常
     */
    public static function networkTimeout(int $timeoutSeconds): self
    {
        return new self(
            "网络请求超时，超过 {$timeoutSeconds} 秒",
            self::ERROR_NETWORK_TIMEOUT
        );
    }

    /**
     * 创建API响应无效异常
     */
    public static function invalidResponse(string $response): self
    {
        return new self(
            "API响应格式无效: {$response}",
            self::ERROR_API_RESPONSE_INVALID
        );
    }

    /**
     * 创建API调用频率超限异常
     */
    public static function rateLimited(): self
    {
        return new self('', self::ERROR_API_RATE_LIMITED);
    }

    /**
     * 创建API权限不足异常
     */
    public static function permissionDenied(string $operation = ''): self
    {
        $message = 'API权限不足';
        if (!empty($operation)) {
            $message .= ": {$operation}";
        }

        return new self($message, self::ERROR_API_PERMISSION_DENIED);
    }
} 