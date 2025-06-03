<?php

namespace Tourze\FaceDetectBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;

/**
 * FaceDetectException 异常类测试
 */
class FaceDetectExceptionTest extends TestCase
{
    public function test_construction_with_default_values(): void
    {
        // Act
        $exception = new FaceDetectException();

        // Assert
        $this->assertSame('未知错误', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_UNKNOWN, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_construction_with_custom_message(): void
    {
        // Arrange
        $message = 'Custom error message';

        // Act
        $exception = new FaceDetectException($message);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_UNKNOWN, $exception->getCode());
    }

    public function test_construction_with_custom_code(): void
    {
        // Arrange
        $code = FaceDetectException::ERROR_INVALID_PARAMETER;

        // Act
        $exception = new FaceDetectException('', $code);

        // Assert
        $this->assertSame('参数无效', $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function test_construction_with_custom_message_and_code(): void
    {
        // Arrange
        $message = 'Custom message';
        $code = FaceDetectException::ERROR_CONFIGURATION_MISSING;

        // Act
        $exception = new FaceDetectException($message, $code);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function test_construction_with_previous_exception(): void
    {
        // Arrange
        $previous = new \RuntimeException('Previous error');
        $message = 'Current error';
        $code = FaceDetectException::ERROR_SERVICE_UNAVAILABLE;

        // Act
        $exception = new FaceDetectException($message, $code, $previous);

        // Assert
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_construction_with_empty_message_uses_default_for_known_code(): void
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
            $exception = new FaceDetectException('', $code);
            $this->assertSame($expectedMessage, $exception->getMessage());
            $this->assertSame($code, $exception->getCode());
        }
    }

    public function test_construction_with_unknown_code(): void
    {
        // Arrange
        $unknownCode = 9999;

        // Act
        $exception = new FaceDetectException('', $unknownCode);

        // Assert
        $this->assertSame('', $exception->getMessage());
        $this->assertSame($unknownCode, $exception->getCode());
    }

    public function test_get_error_message_with_known_codes(): void
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

    public function test_get_error_message_with_unknown_code(): void
    {
        // Arrange
        $unknownCode = 8888;

        // Act
        $message = FaceDetectException::getErrorMessage($unknownCode);

        // Assert
        $this->assertSame('未知错误', $message);
    }

    public function test_invalid_parameter_factory_method(): void
    {
        // Arrange
        $parameter = 'user_id';

        // Act
        $exception = FaceDetectException::invalidParameter($parameter);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString('参数', $exception->getMessage());
        $this->assertStringContainsString('无效', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function test_invalid_parameter_factory_method_with_reason(): void
    {
        // Arrange
        $parameter = 'email';
        $reason = '格式不正确';

        // Act
        $exception = FaceDetectException::invalidParameter($parameter, $reason);

        // Assert
        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function test_invalid_parameter_factory_method_with_empty_parameter(): void
    {
        // Arrange
        $parameter = '';

        // Act
        $exception = FaceDetectException::invalidParameter($parameter);

        // Assert
        $this->assertStringContainsString("参数 '' 无效", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_INVALID_PARAMETER, $exception->getCode());
    }

    public function test_configuration_missing_factory_method(): void
    {
        // Arrange
        $configKey = 'database.host';

        // Act
        $exception = FaceDetectException::configurationMissing($configKey);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($configKey, $exception->getMessage());
        $this->assertStringContainsString('配置项', $exception->getMessage());
        $this->assertStringContainsString('缺失', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_CONFIGURATION_MISSING, $exception->getCode());
    }

    public function test_configuration_missing_factory_method_with_empty_key(): void
    {
        // Arrange
        $configKey = '';

        // Act
        $exception = FaceDetectException::configurationMissing($configKey);

        // Assert
        $this->assertStringContainsString("配置项 '' 缺失", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_CONFIGURATION_MISSING, $exception->getCode());
    }

    public function test_service_unavailable_factory_method(): void
    {
        // Arrange
        $service = 'FaceRecognitionService';

        // Act
        $exception = FaceDetectException::serviceUnavailable($service);

        // Assert
        $this->assertInstanceOf(FaceDetectException::class, $exception);
        $this->assertStringContainsString($service, $exception->getMessage());
        $this->assertStringContainsString('服务', $exception->getMessage());
        $this->assertStringContainsString('不可用', $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function test_service_unavailable_factory_method_with_reason(): void
    {
        // Arrange
        $service = 'DatabaseService';
        $reason = '连接超时';

        // Act
        $exception = FaceDetectException::serviceUnavailable($service, $reason);

        // Assert
        $this->assertStringContainsString($service, $exception->getMessage());
        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function test_service_unavailable_factory_method_with_empty_service(): void
    {
        // Arrange
        $service = '';

        // Act
        $exception = FaceDetectException::serviceUnavailable($service);

        // Assert
        $this->assertStringContainsString("服务 '' 不可用", $exception->getMessage());
        $this->assertSame(FaceDetectException::ERROR_SERVICE_UNAVAILABLE, $exception->getCode());
    }

    public function test_error_code_constants_are_unique(): void
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

    public function test_error_code_constants_are_integers(): void
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
            $this->assertIsInt($code, '错误码应该是整数');
        }
    }

    public function test_inheritance_from_exception(): void
    {
        // Act
        $exception = new FaceDetectException();

        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function test_exception_can_be_thrown_and_caught(): void
    {
        // Arrange
        $message = 'Test exception';
        $code = FaceDetectException::ERROR_INVALID_PARAMETER;

        // Act & Assert
        $this->expectException(FaceDetectException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        throw new FaceDetectException($message, $code);
    }

    public function test_factory_methods_return_throwable_exceptions(): void
    {
        // Act
        $parameterException = FaceDetectException::invalidParameter('test');
        $configException = FaceDetectException::configurationMissing('test.key');
        $serviceException = FaceDetectException::serviceUnavailable('TestService');

        // Assert
        $this->assertInstanceOf(\Throwable::class, $parameterException);
        $this->assertInstanceOf(\Throwable::class, $configException);
        $this->assertInstanceOf(\Throwable::class, $serviceException);
    }

    public function test_complex_exception_chain(): void
    {
        // Arrange
        $rootCause = new \InvalidArgumentException('Root cause');
        $intermediateCause = new \RuntimeException('Intermediate', 0, $rootCause);
        $finalException = new FaceDetectException(
            'Final error',
            FaceDetectException::ERROR_SERVICE_UNAVAILABLE,
            $intermediateCause
        );

        // Act & Assert
        $this->assertSame($intermediateCause, $finalException->getPrevious());
        $this->assertSame($rootCause, $finalException->getPrevious()->getPrevious());
        $this->assertNull($finalException->getPrevious()->getPrevious()->getPrevious());
    }

    public function test_special_characters_in_factory_methods(): void
    {
        // Arrange
        $parameterWithSpecialChars = 'user@domain.com';
        $configWithSpecialChars = 'app.config[db.host]';
        $serviceWithSpecialChars = 'Service-Name_123';

        // Act
        $parameterException = FaceDetectException::invalidParameter($parameterWithSpecialChars);
        $configException = FaceDetectException::configurationMissing($configWithSpecialChars);
        $serviceException = FaceDetectException::serviceUnavailable($serviceWithSpecialChars);

        // Assert
        $this->assertStringContainsString($parameterWithSpecialChars, $parameterException->getMessage());
        $this->assertStringContainsString($configWithSpecialChars, $configException->getMessage());
        $this->assertStringContainsString($serviceWithSpecialChars, $serviceException->getMessage());
    }
} 