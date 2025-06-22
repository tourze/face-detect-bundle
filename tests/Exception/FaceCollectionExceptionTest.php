<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Exception\FaceCollectionException;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;

/**
 * FaceCollectionException 异常类单元测试
 * 
 * 测试人脸采集异常类的核心功能：
 * - 构造函数和错误码映射
 * - 工厂方法创建特定异常
 * - 异常继承关系
 * - 边界条件和特殊场景
 */
class FaceCollectionExceptionTest extends TestCase
{
    /**
     * 测试构造函数默认行为
     */
    public function testConstructorWithDefaults(): void
    {
        // Act
        $exception = new FaceCollectionException();

        // Assert
        $this->assertSame('人脸采集失败', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_COLLECTION_FAILED, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试构造函数自定义消息
     */
    public function testConstructorWithCustomMessage(): void
    {
        // Arrange
        $message = 'Custom collection error message';

        // Act
        $exception = new FaceCollectionException($message);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_COLLECTION_FAILED, $exception->getCode());
    }

    /**
     * 测试构造函数所有错误码的默认消息
     */
    public function testConstructorWithAllErrorCodes(): void
    {
        // Arrange
        $expectedMessages = [
            FaceCollectionException::ERROR_FACE_NOT_DETECTED => '未检测到人脸',
            FaceCollectionException::ERROR_MULTIPLE_FACES => '检测到多张人脸',
            FaceCollectionException::ERROR_FACE_QUALITY_LOW => '人脸质量过低',
            FaceCollectionException::ERROR_IMAGE_FORMAT_INVALID => '图片格式无效',
            FaceCollectionException::ERROR_IMAGE_SIZE_INVALID => '图片尺寸无效',
            FaceCollectionException::ERROR_FACE_ALREADY_EXISTS => '人脸信息已存在',
            FaceCollectionException::ERROR_COLLECTION_FAILED => '人脸采集失败',
        ];

        // Act & Assert
        foreach ($expectedMessages as $code => $expectedMessage) {
            $exception = new FaceCollectionException('', $code);
            $this->assertSame($expectedMessage, $exception->getMessage());
            $this->assertSame($code, $exception->getCode());
        }
    }

    /**
     * 测试构造函数未知错误码
     */
    public function testConstructorWithUnknownErrorCode(): void
    {
        // Arrange
        $unknownCode = 9999;

        // Act
        $exception = new FaceCollectionException('', $unknownCode);

        // Assert
        $this->assertSame('', $exception->getMessage());
        $this->assertSame($unknownCode, $exception->getCode());
    }

    /**
     * 测试构造函数携带前置异常
     */
    public function testConstructorWithPreviousException(): void
    {
        // Arrange
        $previous = new \RuntimeException('Previous error');
        $message = 'Current error';
        $code = FaceCollectionException::ERROR_FACE_NOT_DETECTED;

        // Act
        $exception = new FaceCollectionException($message, $code, $previous);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试faceNotDetected()工厂方法
     */
    public function testFaceNotDetectedFactory(): void
    {
        // Act
        $exception = FaceCollectionException::faceNotDetected();

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertSame('未检测到人脸', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_FACE_NOT_DETECTED, $exception->getCode());
    }

    /**
     * 测试multipleFaces()工厂方法
     */
    public function testMultipleFacesFactory(): void
    {
        // Arrange
        $faceCount = 3;

        // Act
        $exception = FaceCollectionException::multipleFaces($faceCount);

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertStringContainsString('检测到', $exception->getMessage());
        $this->assertStringContainsString((string)$faceCount, $exception->getMessage());
        $this->assertStringContainsString('张人脸', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_MULTIPLE_FACES, $exception->getCode());
    }

    /**
     * 测试multipleFaces()工厂方法不同数量
     */
    public function testMultipleFacesFactoryWithDifferentCounts(): void
    {
        // Arrange
        $testCases = [2, 5, 10, 100];

        // Act & Assert
        foreach ($testCases as $count) {
            $exception = FaceCollectionException::multipleFaces($count);
            $this->assertStringContainsString((string)$count, $exception->getMessage());
            $this->assertSame(FaceCollectionException::ERROR_MULTIPLE_FACES, $exception->getCode());
        }
    }

    /**
     * 测试faceQualityLow()工厂方法
     */
    public function testFaceQualityLowFactory(): void
    {
        // Arrange
        $qualityScore = 0.65;
        $threshold = 0.8;

        // Act
        $exception = FaceCollectionException::faceQualityLow($qualityScore, $threshold);

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertStringContainsString('人脸质量评分', $exception->getMessage());
        $this->assertStringContainsString((string)$qualityScore, $exception->getMessage());
        $this->assertStringContainsString((string)$threshold, $exception->getMessage());
        $this->assertStringContainsString('低于阈值', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_FACE_QUALITY_LOW, $exception->getCode());
    }

    /**
     * 测试faceQualityLow()工厂方法边界值
     */
    public function testFaceQualityLowFactoryWithBoundaryValues(): void
    {
        // Act & Assert - 最低质量
        $exception1 = FaceCollectionException::faceQualityLow(0.0, 0.5);
        $this->assertStringContainsString('0', $exception1->getMessage());

        // Act & Assert - 接近阈值
        $exception2 = FaceCollectionException::faceQualityLow(0.79, 0.8);
        $this->assertStringContainsString('0.79', $exception2->getMessage());
        $this->assertStringContainsString('0.8', $exception2->getMessage());
    }

    /**
     * 测试imageFormatInvalid()工厂方法
     */
    public function testImageFormatInvalidFactory(): void
    {
        // Arrange
        $format = 'bmp';

        // Act
        $exception = FaceCollectionException::imageFormatInvalid($format);

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertStringContainsString('不支持的图片格式', $exception->getMessage());
        $this->assertStringContainsString($format, $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_IMAGE_FORMAT_INVALID, $exception->getCode());
    }

    /**
     * 测试imageFormatInvalid()工厂方法不同格式
     */
    public function testImageFormatInvalidFactoryWithDifferentFormats(): void
    {
        // Arrange
        $formats = ['tiff', 'webp', 'svg', 'pdf', 'unknown'];

        // Act & Assert
        foreach ($formats as $format) {
            $exception = FaceCollectionException::imageFormatInvalid($format);
            $this->assertStringContainsString($format, $exception->getMessage());
            $this->assertSame(FaceCollectionException::ERROR_IMAGE_FORMAT_INVALID, $exception->getCode());
        }
    }

    /**
     * 测试imageSizeInvalid()工厂方法
     */
    public function testImageSizeInvalidFactory(): void
    {
        // Arrange
        $width = 5000;
        $height = 3000;
        $maxSize = 4096;

        // Act
        $exception = FaceCollectionException::imageSizeInvalid($width, $height, $maxSize);

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertStringContainsString('图片尺寸', $exception->getMessage());
        $this->assertStringContainsString("{$width}x{$height}", $exception->getMessage());
        $this->assertStringContainsString((string)$maxSize, $exception->getMessage());
        $this->assertStringContainsString('超过限制', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_IMAGE_SIZE_INVALID, $exception->getCode());
    }

    /**
     * 测试imageSizeInvalid()工厂方法不同尺寸
     */
    public function testImageSizeInvalidFactoryWithDifferentSizes(): void
    {
        // Arrange
        $testCases = [
            [1920, 1080, 1500],
            [8192, 4096, 4096],
            [100000, 50000, 10000],
        ];

        // Act & Assert
        foreach ($testCases as [$width, $height, $maxSize]) {
            $exception = FaceCollectionException::imageSizeInvalid($width, $height, $maxSize);
            $this->assertStringContainsString("{$width}x{$height}", $exception->getMessage());
            $this->assertStringContainsString((string)$maxSize, $exception->getMessage());
        }
    }

    /**
     * 测试faceAlreadyExists()工厂方法
     */
    public function testFaceAlreadyExistsFactory(): void
    {
        // Arrange
        $userId = 'user123';

        // Act
        $exception = FaceCollectionException::faceAlreadyExists($userId);

        // Assert
        $this->assertInstanceOf(FaceCollectionException::class, $exception);
        $this->assertStringContainsString('用户', $exception->getMessage());
        $this->assertStringContainsString($userId, $exception->getMessage());
        $this->assertStringContainsString('人脸信息已存在', $exception->getMessage());
        $this->assertSame(FaceCollectionException::ERROR_FACE_ALREADY_EXISTS, $exception->getCode());
    }

    /**
     * 测试faceAlreadyExists()工厂方法不同用户ID
     */
    public function testFaceAlreadyExistsFactoryWithDifferentUserIds(): void
    {
        // Arrange
        $userIds = ['admin', 'user@domain.com', '12345', 'special_user_123'];

        // Act & Assert
        foreach ($userIds as $userId) {
            $exception = FaceCollectionException::faceAlreadyExists($userId);
            $this->assertStringContainsString($userId, $exception->getMessage());
            $this->assertSame(FaceCollectionException::ERROR_FACE_ALREADY_EXISTS, $exception->getCode());
        }
    }

    /**
     * 测试异常继承关系
     */
    public function testInheritanceHierarchy(): void
    {
        // Act
        $exception = new FaceCollectionException();

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    /**
     * 测试错误码常量是否唯一
     */
    public function testErrorCodeConstantsAreUnique(): void
    {
        // Arrange
        $errorCodes = [
            FaceCollectionException::ERROR_FACE_NOT_DETECTED,
            FaceCollectionException::ERROR_MULTIPLE_FACES,
            FaceCollectionException::ERROR_FACE_QUALITY_LOW,
            FaceCollectionException::ERROR_IMAGE_FORMAT_INVALID,
            FaceCollectionException::ERROR_IMAGE_SIZE_INVALID,
            FaceCollectionException::ERROR_FACE_ALREADY_EXISTS,
            FaceCollectionException::ERROR_COLLECTION_FAILED,
        ];

        // Act
        $uniqueCodes = array_unique($errorCodes);

        // Assert
        $this->assertCount(count($errorCodes), $uniqueCodes, '错误码应该是唯一的');
    }

    /**
     * 测试所有错误码都是整数
     */
    public function testErrorCodeConstantsAreIntegers(): void
    {
        // Arrange
        $errorCodes = [
            FaceCollectionException::ERROR_FACE_NOT_DETECTED,
            FaceCollectionException::ERROR_MULTIPLE_FACES,
            FaceCollectionException::ERROR_FACE_QUALITY_LOW,
            FaceCollectionException::ERROR_IMAGE_FORMAT_INVALID,
            FaceCollectionException::ERROR_IMAGE_SIZE_INVALID,
            FaceCollectionException::ERROR_FACE_ALREADY_EXISTS,
            FaceCollectionException::ERROR_COLLECTION_FAILED,
        ];

        // Act & Assert
        foreach ($errorCodes as $code) {
            $this->assertGreaterThan(2000, $code);
            $this->assertLessThan(3000, $code);
        }
    }

    /**
     * 测试异常可以被抛出和捕获
     */
    public function testExceptionCanBeThrownAndCaught(): void
    {
        // Arrange
        $message = 'Test collection exception';
        $code = FaceCollectionException::ERROR_FACE_NOT_DETECTED;

        // Act & Assert
        $this->expectException(FaceCollectionException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        throw new FaceCollectionException($message, $code);
    }

    /**
     * 测试特殊字符处理
     */
    public function testSpecialCharacterHandling(): void
    {
        // Arrange
        $specialUserId = 'user@domain.com+test';
        $specialFormat = 'image/format-with-symbols';

        // Act
        $exception1 = FaceCollectionException::faceAlreadyExists($specialUserId);
        $exception2 = FaceCollectionException::imageFormatInvalid($specialFormat);

        // Assert
        $this->assertStringContainsString($specialUserId, $exception1->getMessage());
        $this->assertStringContainsString($specialFormat, $exception2->getMessage());
    }

    /**
     * 测试空字符串和边界值处理
     */
    public function testEmptyStringAndBoundaryValues(): void
    {
        // Act & Assert - 空用户ID
        $exception1 = FaceCollectionException::faceAlreadyExists('');
        $this->assertStringContainsString("用户  的", $exception1->getMessage());

        // Act & Assert - 空格式
        $exception2 = FaceCollectionException::imageFormatInvalid('');
        $this->assertStringContainsString('不支持的图片格式: ', $exception2->getMessage());

        // Act & Assert - 零值
        $exception3 = FaceCollectionException::multipleFaces(0);
        $this->assertStringContainsString('0', $exception3->getMessage());

        // Act & Assert - 负值质量评分
        $exception4 = FaceCollectionException::faceQualityLow(-0.1, 0.8);
        $this->assertStringContainsString('-0.1', $exception4->getMessage());

        // Act & Assert - 零尺寸
        $exception5 = FaceCollectionException::imageSizeInvalid(0, 0, 100);
        $this->assertStringContainsString('0x0', $exception5->getMessage());
    }

    /**
     * 测试工厂方法返回正确的异常类型
     */
    public function testFactoryMethodsReturnCorrectType(): void
    {
        // Arrange
        $factoryMethods = [
            fn() => FaceCollectionException::faceNotDetected(),
            fn() => FaceCollectionException::multipleFaces(2),
            fn() => FaceCollectionException::faceQualityLow(0.5, 0.8),
            fn() => FaceCollectionException::imageFormatInvalid('bmp'),
            fn() => FaceCollectionException::imageSizeInvalid(2000, 1500, 1000),
            fn() => FaceCollectionException::faceAlreadyExists('user123'),
        ];

        // Act & Assert
        foreach ($factoryMethods as $factory) {
            $exception = $factory();
            $this->assertInstanceOf(FaceCollectionException::class, $exception);
            $this->assertInstanceOf(\Throwable::class, $exception);
        }
    }

    /**
     * 测试数值格式化和精度
     */
    public function testNumericFormattingAndPrecision(): void
    {
        // Act & Assert - 小数质量评分
        $exception1 = FaceCollectionException::faceQualityLow(0.12345, 0.6789);
        $this->assertStringContainsString('0.12345', $exception1->getMessage());
        $this->assertStringContainsString('0.6789', $exception1->getMessage());

        // Act & Assert - 大数值尺寸
        $exception2 = FaceCollectionException::imageSizeInvalid(99999, 88888, 10000);
        $this->assertStringContainsString('99999x88888', $exception2->getMessage());
        $this->assertStringContainsString('10000', $exception2->getMessage());
    }

    /**
     * 测试消息内容的完整性
     */
    public function testMessageContentCompleteness(): void
    {
        // Act
        $exception1 = FaceCollectionException::multipleFaces(5);
        $exception2 = FaceCollectionException::faceQualityLow(0.65, 0.8);
        $exception3 = FaceCollectionException::imageSizeInvalid(2000, 1500, 1000);

        // Assert - 确保消息包含所有必要信息
        $this->assertStringContainsString('检测到', $exception1->getMessage());
        $this->assertStringContainsString('5', $exception1->getMessage());
        $this->assertStringContainsString('张人脸', $exception1->getMessage());
        $this->assertStringContainsString('请确保图片中只有一张人脸', $exception1->getMessage());

        $this->assertStringContainsString('人脸质量评分', $exception2->getMessage());
        $this->assertStringContainsString('0.65', $exception2->getMessage());
        $this->assertStringContainsString('低于阈值', $exception2->getMessage());
        $this->assertStringContainsString('0.8', $exception2->getMessage());

        $this->assertStringContainsString('图片尺寸', $exception3->getMessage());
        $this->assertStringContainsString('2000x1500', $exception3->getMessage());
        $this->assertStringContainsString('超过限制', $exception3->getMessage());
        $this->assertStringContainsString('1000', $exception3->getMessage());
    }
} 