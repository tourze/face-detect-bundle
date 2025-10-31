<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Exception\BaiduAiException;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * BaiduAiException 异常类单元测试
 *
 * 测试百度AI异常类的核心功能：
 * - 构造函数和错误码映射
 * - 百度API错误码转换
 * - 工厂方法创建特定异常
 * - 异常继承关系
 * - 边界条件和特殊场景
 *
 * @internal
 */
#[CoversClass(BaiduAiException::class)]
final class BaiduAiExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试构造函数默认行为
     */
    public function testConstructorWithDefaults(): void
    {
        // Act
        $exception = new BaiduAiException();

        // Assert
        $this->assertSame('API请求失败', $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_REQUEST_FAILED, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    /**
     * 测试构造函数自定义消息
     */
    public function testConstructorWithCustomMessage(): void
    {
        // Arrange
        $message = 'Custom API error message';

        // Act
        $exception = new BaiduAiException($message);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_REQUEST_FAILED, $exception->getCode());
    }

    /**
     * 测试构造函数所有错误码的默认消息
     */
    public function testConstructorWithAllErrorCodes(): void
    {
        // Arrange
        $expectedMessages = [
            BaiduAiException::ERROR_API_REQUEST_FAILED => 'API请求失败',
            BaiduAiException::ERROR_ACCESS_TOKEN_INVALID => 'Access Token无效',
            BaiduAiException::ERROR_API_QUOTA_EXCEEDED => 'API配额超限',
            BaiduAiException::ERROR_API_RESPONSE_INVALID => 'API响应格式无效',
            BaiduAiException::ERROR_NETWORK_TIMEOUT => '网络请求超时',
            BaiduAiException::ERROR_API_RATE_LIMITED => 'API调用频率超限',
            BaiduAiException::ERROR_API_PERMISSION_DENIED => 'API权限不足',
        ];

        // Act & Assert
        foreach ($expectedMessages as $code => $expectedMessage) {
            $exception = new BaiduAiException('', $code);
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
        $exception = new BaiduAiException('', $unknownCode);

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
        $code = BaiduAiException::ERROR_NETWORK_TIMEOUT;

        // Act
        $exception = new BaiduAiException($message, $code, $previous);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试fromBaiduError()工厂方法 - 已知错误码
     */
    public function testFromBaiduErrorWithKnownErrorCodes(): void
    {
        // Arrange
        $testCases = [
            [110, BaiduAiException::ERROR_ACCESS_TOKEN_INVALID],
            [111, BaiduAiException::ERROR_ACCESS_TOKEN_INVALID],
            [17, BaiduAiException::ERROR_API_QUOTA_EXCEEDED],
            [18, BaiduAiException::ERROR_API_RATE_LIMITED],
            [19, BaiduAiException::ERROR_API_RATE_LIMITED],
            [216100, BaiduAiException::ERROR_INVALID_PARAMETER],
            [216101, BaiduAiException::ERROR_INVALID_PARAMETER],
            [216102, BaiduAiException::ERROR_INVALID_PARAMETER],
            [216103, BaiduAiException::ERROR_INVALID_PARAMETER],
            [216110, BaiduAiException::ERROR_API_REQUEST_FAILED],
            [216200, BaiduAiException::ERROR_API_PERMISSION_DENIED],
        ];

        // Act & Assert
        foreach ($testCases as [$baiduCode, $expectedCode]) {
            $exception = BaiduAiException::fromBaiduError($baiduCode);

            $this->assertInstanceOf(BaiduAiException::class, $exception);
            $this->assertSame($expectedCode, $exception->getCode());
            $this->assertStringContainsString("百度API错误 [{$baiduCode}]", $exception->getMessage());
        }
    }

    /**
     * 测试fromBaiduError()工厂方法 - 未知错误码
     */
    public function testFromBaiduErrorWithUnknownErrorCode(): void
    {
        // Arrange
        $unknownBaiduCode = 999999;

        // Act
        $exception = BaiduAiException::fromBaiduError($unknownBaiduCode);

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertSame(BaiduAiException::ERROR_API_REQUEST_FAILED, $exception->getCode());
        $this->assertStringContainsString("百度API错误 [{$unknownBaiduCode}]", $exception->getMessage());
    }

    /**
     * 测试fromBaiduError()工厂方法 - 自定义错误消息
     */
    public function testFromBaiduErrorWithCustomMessage(): void
    {
        // Arrange
        $baiduCode = 110;
        $customMessage = 'Custom baidu error message';

        // Act
        $exception = BaiduAiException::fromBaiduError($baiduCode, $customMessage);

        // Assert
        $this->assertStringContainsString("百度API错误 [{$baiduCode}]", $exception->getMessage());
        $this->assertStringContainsString($customMessage, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_ACCESS_TOKEN_INVALID, $exception->getCode());
    }

    /**
     * 测试apiRequestFailed()工厂方法
     */
    public function testApiRequestFailedFactory(): void
    {
        // Arrange
        $endpoint = '/face/v3/detect';

        // Act
        $exception = BaiduAiException::apiRequestFailed($endpoint);

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertStringContainsString('API请求失败', $exception->getMessage());
        $this->assertStringContainsString($endpoint, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_REQUEST_FAILED, $exception->getCode());
    }

    /**
     * 测试apiRequestFailed()工厂方法 - 带原因
     */
    public function testApiRequestFailedFactoryWithReason(): void
    {
        // Arrange
        $endpoint = '/face/v3/match';
        $reason = 'Connection refused';

        // Act
        $exception = BaiduAiException::apiRequestFailed($endpoint, $reason);

        // Assert
        $this->assertStringContainsString($endpoint, $exception->getMessage());
        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_REQUEST_FAILED, $exception->getCode());
    }

    /**
     * 测试accessTokenInvalid()工厂方法
     */
    public function testAccessTokenInvalidFactory(): void
    {
        // Act
        $exception = BaiduAiException::accessTokenInvalid();

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertSame('Access Token无效', $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_ACCESS_TOKEN_INVALID, $exception->getCode());
    }

    /**
     * 测试quotaExceeded()工厂方法
     */
    public function testQuotaExceededFactory(): void
    {
        // Act
        $exception = BaiduAiException::quotaExceeded();

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertSame('API配额超限', $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_QUOTA_EXCEEDED, $exception->getCode());
    }

    /**
     * 测试networkTimeout()工厂方法
     */
    public function testNetworkTimeoutFactory(): void
    {
        // Arrange
        $timeoutSeconds = 30;

        // Act
        $exception = BaiduAiException::networkTimeout($timeoutSeconds);

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertStringContainsString('网络请求超时', $exception->getMessage());
        $this->assertStringContainsString("超过 {$timeoutSeconds} 秒", $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_NETWORK_TIMEOUT, $exception->getCode());
    }

    /**
     * 测试networkTimeout()工厂方法 - 不同超时值
     */
    public function testNetworkTimeoutFactoryWithDifferentValues(): void
    {
        // Arrange
        $testCases = [1, 5, 60, 120];

        // Act & Assert
        foreach ($testCases as $timeout) {
            $exception = BaiduAiException::networkTimeout($timeout);
            $this->assertStringContainsString((string) $timeout, $exception->getMessage());
        }
    }

    /**
     * 测试invalidResponse()工厂方法
     */
    public function testInvalidResponseFactory(): void
    {
        // Arrange
        $response = '{"invalid": "json"';

        // Act
        $exception = BaiduAiException::invalidResponse($response);

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertStringContainsString('API响应格式无效', $exception->getMessage());
        $this->assertStringContainsString($response, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_RESPONSE_INVALID, $exception->getCode());
    }

    /**
     * 测试rateLimited()工厂方法
     */
    public function testRateLimitedFactory(): void
    {
        // Act
        $exception = BaiduAiException::rateLimited();

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertSame('API调用频率超限', $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_RATE_LIMITED, $exception->getCode());
    }

    /**
     * 测试permissionDenied()工厂方法
     */
    public function testPermissionDeniedFactory(): void
    {
        // Act
        $exception = BaiduAiException::permissionDenied();

        // Assert
        $this->assertInstanceOf(BaiduAiException::class, $exception);
        $this->assertSame('API权限不足', $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_PERMISSION_DENIED, $exception->getCode());
    }

    /**
     * 测试permissionDenied()工厂方法 - 指定操作
     */
    public function testPermissionDeniedFactoryWithOperation(): void
    {
        // Arrange
        $operation = 'face_detect';

        // Act
        $exception = BaiduAiException::permissionDenied($operation);

        // Assert
        $this->assertStringContainsString('API权限不足', $exception->getMessage());
        $this->assertStringContainsString($operation, $exception->getMessage());
        $this->assertSame(BaiduAiException::ERROR_API_PERMISSION_DENIED, $exception->getCode());
    }

    /**
     * 测试异常继承关系
     */
    public function testInheritanceHierarchy(): void
    {
        // Act
        $exception = new BaiduAiException();

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
            BaiduAiException::ERROR_API_REQUEST_FAILED,
            BaiduAiException::ERROR_ACCESS_TOKEN_INVALID,
            BaiduAiException::ERROR_API_QUOTA_EXCEEDED,
            BaiduAiException::ERROR_API_RESPONSE_INVALID,
            BaiduAiException::ERROR_NETWORK_TIMEOUT,
            BaiduAiException::ERROR_API_RATE_LIMITED,
            BaiduAiException::ERROR_API_PERMISSION_DENIED,
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
            BaiduAiException::ERROR_API_REQUEST_FAILED,
            BaiduAiException::ERROR_ACCESS_TOKEN_INVALID,
            BaiduAiException::ERROR_API_QUOTA_EXCEEDED,
            BaiduAiException::ERROR_API_RESPONSE_INVALID,
            BaiduAiException::ERROR_NETWORK_TIMEOUT,
            BaiduAiException::ERROR_API_RATE_LIMITED,
            BaiduAiException::ERROR_API_PERMISSION_DENIED,
        ];

        // Act & Assert
        foreach ($errorCodes as $code) {
            // 验证错误码是有效的整数范围
            $this->assertGreaterThan(0, $code, '错误码应该是正整数');
            $this->assertLessThan(10000, $code, '错误码应该在合理范围内');
        }
    }

    /**
     * 测试异常可以被抛出和捕获
     */
    public function testExceptionCanBeThrownAndCaught(): void
    {
        // Arrange
        $message = 'Test exception';
        $code = BaiduAiException::ERROR_API_REQUEST_FAILED;

        // Act & Assert
        $this->expectException(BaiduAiException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        throw new BaiduAiException($message, $code);
    }

    /**
     * 测试复杂的异常链
     */
    public function testComplexExceptionChain(): void
    {
        // Arrange
        $rootCause = new \InvalidArgumentException('Root cause');
        $intermediateCause = new \RuntimeException('Intermediate', 0, $rootCause);
        $finalException = new BaiduAiException('Network error', BaiduAiException::ERROR_NETWORK_TIMEOUT, $intermediateCause);

        // Act & Assert
        $this->assertSame($intermediateCause, $finalException->getPrevious());
        $this->assertSame($rootCause, $finalException->getPrevious()->getPrevious());
        $this->assertNull($finalException->getPrevious()->getPrevious()->getPrevious());
    }

    /**
     * 测试特殊字符处理
     */
    public function testSpecialCharacterHandling(): void
    {
        // Arrange
        $specialEndpoint = '/api/test@domain.com?param=中文&value=🎉';
        $specialReason = 'Error with "quotes" and <tags>';

        // Act
        $exception = BaiduAiException::apiRequestFailed($specialEndpoint, $specialReason);

        // Assert
        $this->assertStringContainsString($specialEndpoint, $exception->getMessage());
        $this->assertStringContainsString($specialReason, $exception->getMessage());
    }

    /**
     * 测试空字符串和null值处理
     */
    public function testEmptyStringAndNullHandling(): void
    {
        // Act & Assert - 空端点
        $exception1 = BaiduAiException::apiRequestFailed('');
        $this->assertStringContainsString('API请求失败', $exception1->getMessage());

        // Act & Assert - 空原因
        $exception2 = BaiduAiException::apiRequestFailed('/test', '');
        $this->assertStringNotContainsString(' - ', $exception2->getMessage());

        // Act & Assert - 空操作
        $exception3 = BaiduAiException::permissionDenied('');
        $this->assertStringNotContainsString(':', $exception3->getMessage());
    }

    /**
     * 测试工厂方法返回正确的异常类型
     */
    public function testFactoryMethodsReturnCorrectType(): void
    {
        // Arrange
        $factoryMethods = [
            fn () => BaiduAiException::accessTokenInvalid(),
            fn () => BaiduAiException::quotaExceeded(),
            fn () => BaiduAiException::rateLimited(),
            fn () => BaiduAiException::networkTimeout(30),
            fn () => BaiduAiException::invalidResponse('test'),
            fn () => BaiduAiException::apiRequestFailed('/test'),
            fn () => BaiduAiException::permissionDenied(),
            fn () => BaiduAiException::fromBaiduError(110),
        ];

        // Act & Assert
        foreach ($factoryMethods as $factory) {
            $exception = $factory();
            $this->assertInstanceOf(BaiduAiException::class, $exception);
            $this->assertInstanceOf(\Throwable::class, $exception);
        }
    }
}
