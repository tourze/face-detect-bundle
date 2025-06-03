<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;
use Tourze\FaceDetectBundle\Exception\VerificationException;

/**
 * VerificationException 异常类单元测试
 * 
 * 测试人脸验证异常类的核心功能：
 * - 构造函数和错误码映射
 * - 工厂方法创建特定异常
 * - 异常继承关系
 * - 边界条件和特殊场景
 */
class VerificationExceptionTest extends TestCase
{
    /**
     * 测试构造函数默认行为
     */
    public function testConstructorWithDefaults(): void
    {
        // Act
        $exception = new VerificationException();

        // Assert
        $this->assertSame('人脸验证失败', $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_FAILED, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试构造函数自定义消息
     */
    public function testConstructorWithCustomMessage(): void
    {
        // Arrange
        $message = 'Custom verification error message';

        // Act
        $exception = new VerificationException($message);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_FAILED, $exception->getCode());
    }

    /**
     * 测试构造函数所有错误码的默认消息
     */
    public function testConstructorWithAllErrorCodes(): void
    {
        // Arrange
        $expectedMessages = [
            VerificationException::ERROR_VERIFICATION_FAILED => '人脸验证失败',
            VerificationException::ERROR_SIMILARITY_TOO_LOW => '人脸相似度过低',
            VerificationException::ERROR_FACE_PROFILE_NOT_FOUND => '未找到人脸档案',
            VerificationException::ERROR_VERIFICATION_EXPIRED => '验证已过期',
            VerificationException::ERROR_VERIFICATION_LIMIT_EXCEEDED => '验证次数超限',
            VerificationException::ERROR_STRATEGY_NOT_FOUND => '未找到验证策略',
            VerificationException::ERROR_VERIFICATION_TIMEOUT => '验证超时',
        ];

        // Act & Assert
        foreach ($expectedMessages as $code => $expectedMessage) {
            $exception = new VerificationException('', $code);
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
        $exception = new VerificationException('', $unknownCode);

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
        $code = VerificationException::ERROR_SIMILARITY_TOO_LOW;

        // Act
        $exception = new VerificationException($message, $code, $previous);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试similarityTooLow()工厂方法
     */
    public function testSimilarityTooLowFactory(): void
    {
        // Arrange
        $similarity = 0.75;
        $threshold = 0.85;

        // Act
        $exception = VerificationException::similarityTooLow($similarity, $threshold);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('人脸相似度', $exception->getMessage());
        $this->assertStringContainsString((string)$similarity, $exception->getMessage());
        $this->assertStringContainsString('低于阈值', $exception->getMessage());
        $this->assertStringContainsString((string)$threshold, $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_SIMILARITY_TOO_LOW, $exception->getCode());
    }

    /**
     * 测试similarityTooLow()工厂方法边界值
     */
    public function testSimilarityTooLowFactoryWithBoundaryValues(): void
    {
        // Act & Assert - 最低相似度
        $exception1 = VerificationException::similarityTooLow(0.0, 0.5);
        $this->assertStringContainsString('0', $exception1->getMessage());

        // Act & Assert - 接近阈值
        $exception2 = VerificationException::similarityTooLow(0.849, 0.85);
        $this->assertStringContainsString('0.849', $exception2->getMessage());
        $this->assertStringContainsString('0.85', $exception2->getMessage());

        // Act & Assert - 高精度数值
        $exception3 = VerificationException::similarityTooLow(0.123456, 0.654321);
        $this->assertStringContainsString('0.123456', $exception3->getMessage());
        $this->assertStringContainsString('0.654321', $exception3->getMessage());
    }

    /**
     * 测试faceProfileNotFound()工厂方法
     */
    public function testFaceProfileNotFoundFactory(): void
    {
        // Arrange
        $userId = 'user123';

        // Act
        $exception = VerificationException::faceProfileNotFound($userId);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('用户', $exception->getMessage());
        $this->assertStringContainsString($userId, $exception->getMessage());
        $this->assertStringContainsString('人脸档案不存在', $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_FACE_PROFILE_NOT_FOUND, $exception->getCode());
    }

    /**
     * 测试faceProfileNotFound()工厂方法不同用户ID
     */
    public function testFaceProfileNotFoundFactoryWithDifferentUserIds(): void
    {
        // Arrange
        $userIds = ['admin', 'user@domain.com', '12345', 'special_user_123', ''];

        // Act & Assert
        foreach ($userIds as $userId) {
            $exception = VerificationException::faceProfileNotFound($userId);
            $this->assertStringContainsString($userId, $exception->getMessage());
            $this->assertSame(VerificationException::ERROR_FACE_PROFILE_NOT_FOUND, $exception->getCode());
        }
    }

    /**
     * 测试verificationExpired()工厂方法
     */
    public function testVerificationExpiredFactory(): void
    {
        // Arrange
        $userId = 'user456';
        $businessType = 'login';

        // Act
        $exception = VerificationException::verificationExpired($userId, $businessType);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('用户', $exception->getMessage());
        $this->assertStringContainsString($userId, $exception->getMessage());
        $this->assertStringContainsString('业务', $exception->getMessage());
        $this->assertStringContainsString($businessType, $exception->getMessage());
        $this->assertStringContainsString('验证已过期', $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_EXPIRED, $exception->getCode());
    }

    /**
     * 测试verificationExpired()工厂方法不同业务类型
     */
    public function testVerificationExpiredFactoryWithDifferentBusinessTypes(): void
    {
        // Arrange
        $testCases = [
            ['user1', 'login'],
            ['user2', 'payment'],
            ['user3', 'register'],
            ['user4', 'password_reset'],
            ['user5', ''],
        ];

        // Act & Assert
        foreach ($testCases as [$userId, $businessType]) {
            $exception = VerificationException::verificationExpired($userId, $businessType);
            $this->assertStringContainsString($userId, $exception->getMessage());
            $this->assertStringContainsString($businessType, $exception->getMessage());
            $this->assertSame(VerificationException::ERROR_VERIFICATION_EXPIRED, $exception->getCode());
        }
    }

    /**
     * 测试verificationLimitExceeded()工厂方法
     */
    public function testVerificationLimitExceededFactory(): void
    {
        // Arrange
        $currentCount = 6;
        $maxCount = 5;

        // Act
        $exception = VerificationException::verificationLimitExceeded($currentCount, $maxCount);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('验证次数', $exception->getMessage());
        $this->assertStringContainsString((string)$currentCount, $exception->getMessage());
        $this->assertStringContainsString('超过限制', $exception->getMessage());
        $this->assertStringContainsString((string)$maxCount, $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_LIMIT_EXCEEDED, $exception->getCode());
    }

    /**
     * 测试verificationLimitExceeded()工厂方法不同计数
     */
    public function testVerificationLimitExceededFactoryWithDifferentCounts(): void
    {
        // Arrange
        $testCases = [
            [1, 0],
            [10, 5],
            [100, 50],
            [1000, 999],
        ];

        // Act & Assert
        foreach ($testCases as [$current, $max]) {
            $exception = VerificationException::verificationLimitExceeded($current, $max);
            $this->assertStringContainsString((string)$current, $exception->getMessage());
            $this->assertStringContainsString((string)$max, $exception->getMessage());
            $this->assertSame(VerificationException::ERROR_VERIFICATION_LIMIT_EXCEEDED, $exception->getCode());
        }
    }

    /**
     * 测试strategyNotFound()工厂方法
     */
    public function testStrategyNotFoundFactory(): void
    {
        // Arrange
        $businessType = 'payment';

        // Act
        $exception = VerificationException::strategyNotFound($businessType);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('业务类型', $exception->getMessage());
        $this->assertStringContainsString($businessType, $exception->getMessage());
        $this->assertStringContainsString('验证策略不存在', $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_STRATEGY_NOT_FOUND, $exception->getCode());
    }

    /**
     * 测试strategyNotFound()工厂方法不同业务类型
     */
    public function testStrategyNotFoundFactoryWithDifferentBusinessTypes(): void
    {
        // Arrange
        $businessTypes = ['login', 'register', 'transfer', 'unknown', ''];

        // Act & Assert
        foreach ($businessTypes as $businessType) {
            $exception = VerificationException::strategyNotFound($businessType);
            $this->assertStringContainsString($businessType, $exception->getMessage());
            $this->assertSame(VerificationException::ERROR_STRATEGY_NOT_FOUND, $exception->getCode());
        }
    }

    /**
     * 测试verificationTimeout()工厂方法
     */
    public function testVerificationTimeoutFactory(): void
    {
        // Arrange
        $timeoutSeconds = 30;

        // Act
        $exception = VerificationException::verificationTimeout($timeoutSeconds);

        // Assert
        $this->assertInstanceOf(VerificationException::class, $exception);
        $this->assertStringContainsString('验证超时', $exception->getMessage());
        $this->assertStringContainsString("超过 {$timeoutSeconds} 秒", $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_TIMEOUT, $exception->getCode());
    }

    /**
     * 测试verificationTimeout()工厂方法不同超时值
     */
    public function testVerificationTimeoutFactoryWithDifferentValues(): void
    {
        // Arrange
        $timeoutValues = [5, 30, 60, 120, 300];

        // Act & Assert
        foreach ($timeoutValues as $timeout) {
            $exception = VerificationException::verificationTimeout($timeout);
            $this->assertStringContainsString((string)$timeout, $exception->getMessage());
            $this->assertSame(VerificationException::ERROR_VERIFICATION_TIMEOUT, $exception->getCode());
        }
    }

    /**
     * 测试异常继承关系
     */
    public function testInheritanceHierarchy(): void
    {
        // Act
        $exception = new VerificationException();

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
            VerificationException::ERROR_VERIFICATION_FAILED,
            VerificationException::ERROR_SIMILARITY_TOO_LOW,
            VerificationException::ERROR_FACE_PROFILE_NOT_FOUND,
            VerificationException::ERROR_VERIFICATION_EXPIRED,
            VerificationException::ERROR_VERIFICATION_LIMIT_EXCEEDED,
            VerificationException::ERROR_STRATEGY_NOT_FOUND,
            VerificationException::ERROR_VERIFICATION_TIMEOUT,
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
            VerificationException::ERROR_VERIFICATION_FAILED,
            VerificationException::ERROR_SIMILARITY_TOO_LOW,
            VerificationException::ERROR_FACE_PROFILE_NOT_FOUND,
            VerificationException::ERROR_VERIFICATION_EXPIRED,
            VerificationException::ERROR_VERIFICATION_LIMIT_EXCEEDED,
            VerificationException::ERROR_STRATEGY_NOT_FOUND,
            VerificationException::ERROR_VERIFICATION_TIMEOUT,
        ];

        // Act & Assert
        foreach ($errorCodes as $code) {
            $this->assertIsInt($code, '错误码应该是整数');
        }
    }

    /**
     * 测试异常可以被抛出和捕获
     */
    public function testExceptionCanBeThrownAndCaught(): void
    {
        // Arrange
        $message = 'Test verification exception';
        $code = VerificationException::ERROR_SIMILARITY_TOO_LOW;

        // Act & Assert
        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        throw new VerificationException($message, $code);
    }

    /**
     * 测试特殊字符和Unicode处理
     */
    public function testSpecialCharacterAndUnicodeHandling(): void
    {
        // Arrange
        $specialUserId = 'user@domain.com+测试';
        $unicodeBusinessType = '支付_业务';

        // Act
        $exception1 = VerificationException::faceProfileNotFound($specialUserId);
        $exception2 = VerificationException::strategyNotFound($unicodeBusinessType);

        // Assert
        $this->assertStringContainsString($specialUserId, $exception1->getMessage());
        $this->assertStringContainsString($unicodeBusinessType, $exception2->getMessage());
    }

    /**
     * 测试边界值和特殊情况
     */
    public function testBoundaryValuesAndSpecialCases(): void
    {
        // Act & Assert - 零值和负值超时
        $exception1 = VerificationException::verificationTimeout(0);
        $this->assertStringContainsString('0', $exception1->getMessage());

        // Act & Assert - 零计数
        $exception2 = VerificationException::verificationLimitExceeded(0, 0);
        $this->assertStringContainsString('0', $exception2->getMessage());

        // Act & Assert - 相似度边界值
        $exception3 = VerificationException::similarityTooLow(1.0, 1.0);
        $this->assertStringContainsString('1', $exception3->getMessage());

        // Act & Assert - 负值相似度
        $exception4 = VerificationException::similarityTooLow(-0.1, 0.8);
        $this->assertStringContainsString('-0.1', $exception4->getMessage());
    }

    /**
     * 测试工厂方法返回正确的异常类型
     */
    public function testFactoryMethodsReturnCorrectType(): void
    {
        // Arrange
        $factoryMethods = [
            fn() => VerificationException::similarityTooLow(0.7, 0.8),
            fn() => VerificationException::faceProfileNotFound('user123'),
            fn() => VerificationException::verificationExpired('user123', 'login'),
            fn() => VerificationException::verificationLimitExceeded(5, 3),
            fn() => VerificationException::strategyNotFound('payment'),
            fn() => VerificationException::verificationTimeout(30),
        ];

        // Act & Assert
        foreach ($factoryMethods as $factory) {
            $exception = $factory();
            $this->assertInstanceOf(VerificationException::class, $exception);
            $this->assertInstanceOf(\Throwable::class, $exception);
        }
    }

    /**
     * 测试消息格式的一致性
     */
    public function testMessageFormatConsistency(): void
    {
        // Act
        $exception1 = VerificationException::similarityTooLow(0.75, 0.85);
        $exception2 = VerificationException::verificationLimitExceeded(6, 5);
        $exception3 = VerificationException::verificationTimeout(30);

        // Assert - 检查消息格式是否包含必要的信息
        $this->assertStringContainsString('人脸相似度', $exception1->getMessage());
        $this->assertStringContainsString('低于阈值', $exception1->getMessage());
        
        $this->assertStringContainsString('验证次数', $exception2->getMessage());
        $this->assertStringContainsString('超过限制', $exception2->getMessage());
        
        $this->assertStringContainsString('验证超时', $exception3->getMessage());
        $this->assertStringContainsString('超过', $exception3->getMessage());
        $this->assertStringContainsString('秒', $exception3->getMessage());
    }

    /**
     * 测试高精度数值处理
     */
    public function testHighPrecisionNumericHandling(): void
    {
        // Act & Assert - 高精度相似度
        $exception1 = VerificationException::similarityTooLow(0.123456789, 0.987654321);
        $this->assertStringContainsString('0.123456789', $exception1->getMessage());
        $this->assertStringContainsString('0.987654321', $exception1->getMessage());

        // Act & Assert - 大数值计数
        $exception2 = VerificationException::verificationLimitExceeded(999999, 888888);
        $this->assertStringContainsString('999999', $exception2->getMessage());
        $this->assertStringContainsString('888888', $exception2->getMessage());
    }

    /**
     * 测试空字符串和null值的鲁棒性
     */
    public function testEmptyStringAndNullRobustness(): void
    {
        // Act & Assert - 空用户ID
        $exception1 = VerificationException::faceProfileNotFound('');
        $this->assertStringContainsString('用户  的', $exception1->getMessage());

        // Act & Assert - 空业务类型
        $exception2 = VerificationException::strategyNotFound('');
        $this->assertStringContainsString('业务类型  的', $exception2->getMessage());

        // Act & Assert - 空业务类型的过期验证
        $exception3 = VerificationException::verificationExpired('test', '');
        $this->assertStringContainsString('业务  的', $exception3->getMessage());
    }
} 