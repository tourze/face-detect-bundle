<?php

namespace Tourze\FaceDetectBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;
use Tourze\FaceDetectBundle\Exception\VerificationException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * FaceDetectException 异常类测试
 *
 * @internal
 */
#[CoversClass(FaceDetectException::class)]
final class FaceDetectExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructionWithDefaultValues(): void
    {
        // Act
        $exception = new VerificationException();

        // Assert
        $this->assertSame('人脸验证失败', $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_FAILED, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructionWithCustomMessage(): void
    {
        // Arrange
        $message = 'Custom error message';

        // Act
        $exception = new VerificationException($message);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(VerificationException::ERROR_VERIFICATION_FAILED, $exception->getCode());
    }

    public function testConstructionWithCustomCode(): void
    {
        // Arrange
        $code = FaceDetectException::ERROR_INVALID_PARAMETER;

        // Act
        $exception = new VerificationException('', $code);

        // Assert
        $this->assertSame('参数无效', $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testConstructionWithCustomMessageAndCode(): void
    {
        // Arrange
        $message = 'Custom message';
        $code = FaceDetectException::ERROR_CONFIGURATION_MISSING;

        // Act
        $exception = new VerificationException($message, $code);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testConstructionWithPreviousException(): void
    {
        // Arrange
        $previous = new \RuntimeException('Previous error');
        $message = 'Current error';
        $code = FaceDetectException::ERROR_SERVICE_UNAVAILABLE;

        // Act
        $exception = new VerificationException($message, $code, $previous);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructionWithEmptyMessageUsesDefaultForKnownCode(): void
    {
        // Arrange
        $knownCodes = [
            FaceDetectException::ERROR_UNKNOWN => '未知错误',
            FaceDetectException::ERROR_INVALID_PARAMETER => '参数无效',
            FaceDetectException::ERROR_CONFIGURATION_MISSING => '配置缺失',
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE => '服务不可用',
        ];

        // Act & Assert
        foreach ($knownCodes as $code => $expectedMessage) {
            $exception = new VerificationException('', $code);
            $this->assertSame($expectedMessage, $exception->getMessage());
            $this->assertSame($code, $exception->getCode());
        }
    }

    public function testConstructionWithUnknownCode(): void
    {
        // Arrange
        $unknownCode = 9999;

        // Act
        $exception = new VerificationException('', $unknownCode);

        // Assert
        $this->assertSame('', $exception->getMessage());
        $this->assertSame($unknownCode, $exception->getCode());
    }

    public function testGetErrorMessageWithKnownCodes(): void
    {
        // Arrange
        $expectedMessages = [
            FaceDetectException::ERROR_UNKNOWN => '未知错误',
            FaceDetectException::ERROR_INVALID_PARAMETER => '参数无效',
            FaceDetectException::ERROR_CONFIGURATION_MISSING => '配置缺失',
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE => '服务不可用',
        ];

        // Act & Assert
        foreach ($expectedMessages as $code => $expectedMessage) {
            $message = FaceDetectException::getErrorMessage($code);
            $this->assertSame($expectedMessage, $message);
        }
    }

    public function testGetErrorMessageWithUnknownCode(): void
    {
        // Arrange
        $unknownCode = 8888;

        // Act
        $message = FaceDetectException::getErrorMessage($unknownCode);

        // Assert
        $this->assertSame('未知错误', $message);
    }

    public function testInvalidParameterFactoryMethod(): void
    {
        // Arrange
        $parameter = 'user_id';

        // Act
        $exception = VerificationException::invalidParameter($parameter);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString('参数', $exception->getMessage());
        $this->assertStringContainsString('无效', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function testInvalidParameterFactoryMethodWithReason(): void
    {
        // Arrange
        $parameter = 'email';
        $reason = '格式不正确';

        // Act
        $exception = VerificationException::invalidParameter($parameter, $reason);

        // Assert
        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function testInvalidParameterFactoryMethodWithEmptyParameter(): void
    {
        // Arrange
        $parameter = '';

        // Act
        $exception = VerificationException::invalidParameter($parameter);

        // Assert
        $this->assertStringContainsString("参数 '' 无效", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function testConfigurationMissingFactoryMethod(): void
    {
        // Arrange
        $configKey = 'database.host';

        // Act
        $exception = VerificationException::configurationMissing($configKey);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($configKey, $exception->getMessage());
        $this->assertStringContainsString('配置项', $exception->getMessage());
        $this->assertStringContainsString('缺失', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_CONFIGURATION_MISSING, $exception->getCode());
    }

    public function testConfigurationMissingFactoryMethodWithEmptyKey(): void
    {
        // Arrange
        $configKey = '';

        // Act
        $exception = VerificationException::configurationMissing($configKey);

        // Assert
        $this->assertStringContainsString("配置项 '' 缺失", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_CONFIGURATION_MISSING, $exception->getCode());
    }

    public function testServiceUnavailableFactoryMethod(): void
    {
        // Arrange
        $service = 'FaceRecognitionService';

        // Act
        $exception = VerificationException::serviceUnavailable($service);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($service, $exception->getMessage());
        $this->assertStringContainsString('服务', $exception->getMessage());
        $this->assertStringContainsString('不可用', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function testServiceUnavailableFactoryMethodWithReason(): void
    {
        // Arrange
        $service = 'DatabaseService';
        $reason = '连接超时';

        // Act
        $exception = VerificationException::serviceUnavailable($service, $reason);

        // Assert
        $this->assertStringContainsString($service, $exception->getMessage());
        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function testServiceUnavailableFactoryMethodWithEmptyService(): void
    {
        // Arrange
        $service = '';

        // Act
        $exception = VerificationException::serviceUnavailable($service);

        // Assert
        $this->assertStringContainsString("服务 '' 不可用", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function testErrorCodeConstantsAreUnique(): void
    {
        // Arrange
        $errorCodes = [
            FaceDetectException::ERROR_UNKNOWN,
            FaceDetectException::ERROR_INVALID_PARAMETER,
            FaceDetectException::ERROR_CONFIGURATION_MISSING,
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE,
        ];

        // Act & Assert
        $uniqueCodes = array_unique($errorCodes);
        $this->assertCount(count($errorCodes), $uniqueCodes, '错误码应该是唯一的');
    }

    public function testErrorCodeConstantsAreIntegers(): void
    {
        // Arrange
        $errorCodes = [
            FaceDetectException::ERROR_UNKNOWN,
            FaceDetectException::ERROR_INVALID_PARAMETER,
            FaceDetectException::ERROR_CONFIGURATION_MISSING,
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE,
        ];

        // Act & Assert
        foreach ($errorCodes as $code) {
            $this->assertGreaterThan(0, $code);
            $this->assertLessThan(10000, $code);
        }
    }

    public function testInheritanceFromException(): void
    {
        // Act
        $exception = new VerificationException();

        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCanBeThrownAndCaught(): void
    {
        // Arrange
        $message = 'Test exception';
        $code = FaceDetectException::ERROR_INVALID_PARAMETER;

        // Act & Assert
        $this->expectException(FaceDetectException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        throw new VerificationException($message, $code);
    }

    public function testFactoryMethodsReturnThrowableExceptions(): void
    {
        // Act
        $parameterException = VerificationException::invalidParameter('test');
        $configException = VerificationException::configurationMissing('test.key');
        $serviceException = VerificationException::serviceUnavailable('TestService');

        // Assert
        $this->assertInstanceOf(\Throwable::class, $parameterException);
        $this->assertInstanceOf(\Throwable::class, $configException);
        $this->assertInstanceOf(\Throwable::class, $serviceException);
    }

    public function testComplexExceptionChain(): void
    {
        // Arrange
        $rootCause = new \InvalidArgumentException('Root cause');
        $intermediateCause = new \RuntimeException('Intermediate', 0, $rootCause);
        $finalException = new VerificationException(
            'Final error',
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE,
            $intermediateCause
        );

        // Act & Assert
        $this->assertSame($intermediateCause, $finalException->getPrevious());
        $this->assertSame($rootCause, $finalException->getPrevious()->getPrevious());
        $this->assertNull($finalException->getPrevious()->getPrevious()->getPrevious());
    }

    public function testSpecialCharactersInFactoryMethods(): void
    {
        // Arrange
        $parameterWithSpecialChars = 'user@domain.com';
        $configWithSpecialChars = 'app.config[db.host]';
        $serviceWithSpecialChars = 'Service-Name_123';

        // Act
        $parameterException = VerificationException::invalidParameter($parameterWithSpecialChars);
        $configException = VerificationException::configurationMissing($configWithSpecialChars);
        $serviceException = VerificationException::serviceUnavailable($serviceWithSpecialChars);

        // Assert
        $this->assertStringContainsString($parameterWithSpecialChars, $parameterException->getMessage());
        $this->assertStringContainsString($configWithSpecialChars, $configException->getMessage());
        $this->assertStringContainsString($serviceWithSpecialChars, $serviceException->getMessage());
    }
}
