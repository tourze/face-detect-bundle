<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Exception;

/**
 * 人脸采集异常类
 */
class FaceCollectionException extends FaceDetectException
{
    /**
     * 人脸采集相关错误码
     */
    public const ERROR_FACE_NOT_DETECTED = 2001;
    public const ERROR_MULTIPLE_FACES = 2002;
    public const ERROR_FACE_QUALITY_LOW = 2003;
    public const ERROR_IMAGE_FORMAT_INVALID = 2004;
    public const ERROR_IMAGE_SIZE_INVALID = 2005;
    public const ERROR_FACE_ALREADY_EXISTS = 2006;
    public const ERROR_COLLECTION_FAILED = 2007;

    /**
     * 错误码映射
     */
    private const ERROR_MESSAGES = [
        self::ERROR_FACE_NOT_DETECTED => '未检测到人脸',
        self::ERROR_MULTIPLE_FACES => '检测到多张人脸',
        self::ERROR_FACE_QUALITY_LOW => '人脸质量过低',
        self::ERROR_IMAGE_FORMAT_INVALID => '图片格式无效',
        self::ERROR_IMAGE_SIZE_INVALID => '图片尺寸无效',
        self::ERROR_FACE_ALREADY_EXISTS => '人脸信息已存在',
        self::ERROR_COLLECTION_FAILED => '人脸采集失败',
    ];

    public function __construct(
        string $message = '',
        int $code = self::ERROR_COLLECTION_FAILED,
        ?\Throwable $previous = null,
    ) {
        if ('' === $message && isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * 创建未检测到人脸异常
     */
    public static function faceNotDetected(): self
    {
        return new self('', self::ERROR_FACE_NOT_DETECTED);
    }

    /**
     * 创建多张人脸异常
     */
    public static function multipleFaces(int $faceCount): self
    {
        return new self(
            "检测到 {$faceCount} 张人脸，请确保图片中只有一张人脸",
            self::ERROR_MULTIPLE_FACES
        );
    }

    /**
     * 创建人脸质量过低异常
     */
    public static function faceQualityLow(float $qualityScore, float $threshold): self
    {
        return new self(
            "人脸质量评分 {$qualityScore} 低于阈值 {$threshold}",
            self::ERROR_FACE_QUALITY_LOW
        );
    }

    /**
     * 创建图片格式无效异常
     */
    public static function imageFormatInvalid(string $format): self
    {
        return new self(
            "不支持的图片格式: {$format}",
            self::ERROR_IMAGE_FORMAT_INVALID
        );
    }

    /**
     * 创建图片尺寸无效异常
     */
    public static function imageSizeInvalid(int $width, int $height, int $maxSize): self
    {
        return new self(
            "图片尺寸 {$width}x{$height} 超过限制 {$maxSize}",
            self::ERROR_IMAGE_SIZE_INVALID
        );
    }

    /**
     * 创建人脸已存在异常
     */
    public static function faceAlreadyExists(string $userId): self
    {
        return new self(
            "用户 {$userId} 的人脸信息已存在",
            self::ERROR_FACE_ALREADY_EXISTS
        );
    }
}
