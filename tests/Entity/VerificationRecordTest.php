<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Enum\VerificationType;

/**
 * VerificationRecord 实体单元测试
 *
 * 测试验证记录实体的核心功能：
 * - 构造函数和基本属性
 * - 验证结果和类型管理
 * - 客户端信息处理
 * - 错误信息设置
 * - 业务逻辑方法
 * - 边界条件和异常场景
 */
class VerificationRecordTest extends TestCase
{
    private VerificationStrategy $mockStrategy;

    protected function setUp(): void
    {
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);
        $this->mockStrategy = $strategy;
    }

    /**
     * 测试构造函数创建基本记录
     */
    public function testConstructorWithMinimalParameters(): void
    {
        // Arrange
        $userId = 'user123';
        $businessType = 'login';
        $result = VerificationResult::SUCCESS;

        // Act
        $record = new VerificationRecord($userId, $this->mockStrategy, $businessType, $result);

        // Assert
        $this->assertSame($userId, $record->getUserId());
        $this->assertSame($this->mockStrategy, $record->getStrategy());
        $this->assertSame($businessType, $record->getBusinessType());
        $this->assertSame($result, $record->getResult());
        $this->assertSame(VerificationType::REQUIRED, $record->getVerificationType());
        $this->assertNull($record->getOperationId());
        $this->assertNull($record->getConfidenceScore());
        $this->assertNull($record->getVerificationTime());
        $this->assertNull($record->getClientInfo());
        $this->assertNull($record->getErrorCode());
        $this->assertNull($record->getErrorMessage());
        $this->assertNull($record->getCreateTime());
    }

    /**
     * 测试构造函数处理空字符串
     */
    public function testConstructorWithEmptyStrings(): void
    {
        // Arrange & Act
        $record = new VerificationRecord('', $this->mockStrategy, '', VerificationResult::FAILED);

        // Assert
        $this->assertSame('', $record->getUserId());
        $this->assertSame('', $record->getBusinessType());
    }

    /**
     * 测试__toString()方法无ID时的表现
     */
    public function testToStringWithoutId(): void
    {
        // Arrange
        $userId = 'test_user_123';
        $record = new VerificationRecord($userId, $this->mockStrategy, 'payment', VerificationResult::SUCCESS);

        // Act
        $result = (string) $record;

        // Assert
        $this->assertStringContainsString('VerificationRecord', $result);
        $this->assertStringContainsString($userId, $result);
        $this->assertStringContainsString('success', $result);
        $this->assertStringContainsString('0', $result); // ID应该是0
    }

    /**
     * 测试__toString()方法含有ID时的表现（使用反射设置ID）
     */
    public function testToStringWithId(): void
    {
        // Arrange
        $record = new VerificationRecord('user456', $this->mockStrategy, 'transfer', VerificationResult::FAILED);
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass($record);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($record, 789);

        // Act
        $result = (string) $record;

        // Assert
        $this->assertStringContainsString('VerificationRecord[789]', $result);
    }

    /**
     * 测试策略设置
     */
    public function testSetStrategy(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $newStrategy */
        $newStrategy = $this->createMock(VerificationStrategy::class);

        // Act
        $result = $record->setStrategy($newStrategy);

        // Assert
        $this->assertSame($record, $result); // 测试链式调用
        $this->assertSame($newStrategy, $record->getStrategy());
    }

    /**
     * 测试业务类型设置
     */
    public function testSetBusinessType(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act
        $result = $record->setBusinessType('payment');

        // Assert
        $this->assertSame($record, $result);
        $this->assertSame('payment', $record->getBusinessType());
    }

    /**
     * 测试操作ID设置
     */
    public function testSetOperationId(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'transfer', VerificationResult::SUCCESS);

        // Act & Assert
        $record->setOperationId('op_123456');
        $this->assertSame('op_123456', $record->getOperationId());

        $record->setOperationId(null);
        $this->assertNull($record->getOperationId());
    }

    /**
     * 测试验证类型设置
     */
    public function testSetVerificationType(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert
        foreach (VerificationType::cases() as $type) {
            $result = $record->setVerificationType($type);
            $this->assertSame($record, $result); // 链式调用
            $this->assertSame($type, $record->getVerificationType());
        }
    }

    /**
     * 测试验证结果设置
     */
    public function testSetResult(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert
        foreach (VerificationResult::cases() as $result) {
            $returnValue = $record->setResult($result);
            $this->assertSame($record, $returnValue); // 链式调用
            $this->assertSame($result, $record->getResult());
        }
    }

    /**
     * 测试置信度评分设置
     */
    public function testSetConfidenceScore(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert - 有效范围
        $record->setConfidenceScore(0.0);
        $this->assertSame(0.0, $record->getConfidenceScore());

        $record->setConfidenceScore(1.0);
        $this->assertSame(1.0, $record->getConfidenceScore());

        $record->setConfidenceScore(0.95);
        $this->assertSame(0.95, $record->getConfidenceScore());

        $record->setConfidenceScore(null);
        $this->assertNull($record->getConfidenceScore());
    }

    /**
     * 测试验证耗时设置
     */
    public function testSetVerificationTime(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert
        $record->setVerificationTime(1.5);
        $this->assertSame(1.5, $record->getVerificationTime());

        $record->setVerificationTime(0.001);
        $this->assertSame(0.001, $record->getVerificationTime());

        $record->setVerificationTime(null);
        $this->assertNull($record->getVerificationTime());
    }

    /**
     * 测试客户端信息设置
     */
    public function testSetClientInfo(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $clientInfo = [
            'browser' => 'Chrome',
            'os' => 'Windows 10',
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0...'
        ];

        // Act
        $result = $record->setClientInfo($clientInfo);

        // Assert
        $this->assertSame($record, $result);
        $this->assertSame($clientInfo, $record->getClientInfo());
    }

    /**
     * 测试客户端信息设置为空值
     */
    public function testSetClientInfoWithNullAndEmpty(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert
        $record->setClientInfo(null);
        $this->assertNull($record->getClientInfo());

        $record->setClientInfo([]);
        $this->assertSame([], $record->getClientInfo());
    }

    /**
     * 测试错误码设置
     */
    public function testSetErrorCode(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::FAILED);

        // Act & Assert
        $record->setErrorCode('E001');
        $this->assertSame('E001', $record->getErrorCode());

        $record->setErrorCode(null);
        $this->assertNull($record->getErrorCode());
    }

    /**
     * 测试错误信息设置
     */
    public function testSetErrorMessage(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::FAILED);

        // Act & Assert
        $record->setErrorMessage('Face detection failed');
        $this->assertSame('Face detection failed', $record->getErrorMessage());

        $record->setErrorMessage(null);
        $this->assertNull($record->getErrorMessage());
    }

    /**
     * 测试isSuccessful()方法
     */
    public function testIsSuccessful(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);

        // Act & Assert
        $this->assertTrue($record->isSuccessful());

        // 测试其他结果
        $record->setResult(VerificationResult::FAILED);
        $this->assertFalse($record->isSuccessful());

        $record->setResult(VerificationResult::TIMEOUT);
        $this->assertFalse($record->isSuccessful());

        $record->setResult(VerificationResult::SKIPPED);
        $this->assertFalse($record->isSuccessful());
    }

    /**
     * 测试isFailed()方法
     */
    public function testIsFailed(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::FAILED);

        // Act & Assert
        $this->assertTrue($record->isFailed());

        // 测试其他结果
        $record->setResult(VerificationResult::SUCCESS);
        $this->assertFalse($record->isFailed());

        $record->setResult(VerificationResult::TIMEOUT);
        $this->assertFalse($record->isFailed());

        $record->setResult(VerificationResult::SKIPPED);
        $this->assertFalse($record->isFailed());
    }

    /**
     * 测试setError()方法
     */
    public function testSetError(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::FAILED);
        $errorCode = 'E404';
        $errorMessage = 'Face template not found';

        // Act
        $result = $record->setError($errorCode, $errorMessage);

        // Assert
        $this->assertSame($record, $result); // 链式调用
        $this->assertSame($errorCode, $record->getErrorCode());
        $this->assertSame($errorMessage, $record->getErrorMessage());
    }

    /**
     * 测试getClientInfoValue()方法 - 存在的键
     */
    public function testGetClientInfoValueExistingKey(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $clientInfo = [
            'browser' => 'Firefox',
            'version' => '95.0',
            'mobile' => true,
            'screen' => ['width' => 1920, 'height' => 1080]
        ];
        $record->setClientInfo($clientInfo);

        // Act & Assert
        $this->assertSame('Firefox', $record->getClientInfoValue('browser'));
        $this->assertSame('95.0', $record->getClientInfoValue('version'));
        $this->assertTrue($record->getClientInfoValue('mobile'));
        $this->assertSame(['width' => 1920, 'height' => 1080], $record->getClientInfoValue('screen'));
    }

    /**
     * 测试getClientInfoValue()方法 - 不存在的键
     */
    public function testGetClientInfoValueNonExistingKey(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $record->setClientInfo(['browser' => 'Chrome']);

        // Act & Assert
        $this->assertNull($record->getClientInfoValue('non_existing'));
        $this->assertSame('default', $record->getClientInfoValue('missing', 'default'));
        $this->assertSame(0, $record->getClientInfoValue('count', 0));
    }

    /**
     * 测试getClientInfoValue()方法 - null客户端信息
     */
    public function testGetClientInfoValueWithNullClientInfo(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $record->setClientInfo(null);

        // Act & Assert
        $this->assertNull($record->getClientInfoValue('any_key'));
        $this->assertSame('fallback', $record->getClientInfoValue('any_key', 'fallback'));
    }

    /**
     * 测试setClientInfoValue()方法 - null客户端信息
     */
    public function testSetClientInfoValueWithNullClientInfo(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $record->setClientInfo(null);

        // Act
        $result = $record->setClientInfoValue('new_key', 'new_value');

        // Assert
        $this->assertSame($record, $result); // 链式调用
        $this->assertSame(['new_key' => 'new_value'], $record->getClientInfo());
    }

    /**
     * 测试setClientInfoValue()方法 - 现有客户端信息
     */
    public function testSetClientInfoValueWithExistingClientInfo(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $record->setClientInfo(['existing' => 'value']);

        // Act
        $record->setClientInfoValue('new_key', 'new_value');

        // Assert
        $expected = ['existing' => 'value', 'new_key' => 'new_value'];
        $this->assertSame($expected, $record->getClientInfo());
    }

    /**
     * 测试setClientInfoValue()方法 - 覆盖现有值
     */
    public function testSetClientInfoValueOverwriteExisting(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'login', VerificationResult::SUCCESS);
        $record->setClientInfo(['key' => 'old_value']);

        // Act
        $record->setClientInfoValue('key', 'new_value');

        // Assert
        $this->assertSame('new_value', $record->getClientInfoValue('key'));
    }

    /**
     * 测试复杂的验证场景
     */
    public function testComplexVerificationScenario(): void
    {
        // Arrange
        $record = new VerificationRecord('premium_user_001', $this->mockStrategy, 'high_value_transfer', VerificationResult::SUCCESS);
        
        // Act - 设置完整的验证信息
        $record->setOperationId('txn_20241226_001')
               ->setVerificationType(VerificationType::FORCED)
               ->setConfidenceScore(0.98)
               ->setVerificationTime(2.35)
               ->setClientInfo([
                   'device_id' => 'device_12345',
                   'browser' => 'Safari',
                   'os' => 'iOS 17.2',
                   'location' => ['lat' => 40.7128, 'lng' => -74.0060],
                   'biometric_data' => ['face_quality' => 'high', 'liveness_check' => true]
               ]);

        // Assert
        $this->assertTrue($record->isSuccessful());
        $this->assertFalse($record->isFailed());
        $this->assertSame('txn_20241226_001', $record->getOperationId());
        $this->assertSame(VerificationType::FORCED, $record->getVerificationType());
        $this->assertSame(0.98, $record->getConfidenceScore());
        $this->assertSame(2.35, $record->getVerificationTime());
        $this->assertSame('device_12345', $record->getClientInfoValue('device_id'));
        $this->assertTrue($record->getClientInfoValue('biometric_data')['liveness_check']);
    }

    /**
     * 测试失败验证场景
     */
    public function testFailedVerificationScenario(): void
    {
        // Arrange
        $record = new VerificationRecord('suspicious_user', $this->mockStrategy, 'login_attempt', VerificationResult::FAILED);

        // Act
        $record->setVerificationType(VerificationType::REQUIRED)
               ->setConfidenceScore(0.12) // 低置信度
               ->setVerificationTime(5.2) // 较长时间
               ->setError('FACE_MISMATCH', 'Face does not match stored template')
               ->setClientInfo([
                   'ip' => '192.168.1.100',
                   'suspicious_patterns' => ['multiple_attempts', 'unusual_timing'],
                   'risk_score' => 0.85
               ]);

        // Assert
        $this->assertFalse($record->isSuccessful());
        $this->assertTrue($record->isFailed());
        $this->assertSame('FACE_MISMATCH', $record->getErrorCode());
        $this->assertSame('Face does not match stored template', $record->getErrorMessage());
        $this->assertSame(0.85, $record->getClientInfoValue('risk_score'));
    }

    /**
     * 测试边界条件 - 极值测试
     */
    public function testBoundaryConditions(): void
    {
        // Arrange
        $record = new VerificationRecord('boundary_user', $this->mockStrategy, 'test', VerificationResult::SUCCESS);

        // Act & Assert - 置信度边界
        $record->setConfidenceScore(0.0);
        $this->assertSame(0.0, $record->getConfidenceScore());

        $record->setConfidenceScore(1.0);
        $this->assertSame(1.0, $record->getConfidenceScore());

        // Act & Assert - 验证时间边界
        $record->setVerificationTime(0.001); // 1毫秒
        $this->assertSame(0.001, $record->getVerificationTime());

        $record->setVerificationTime(999.999); // 接近1000秒
        $this->assertSame(999.999, $record->getVerificationTime());
    }

    /**
     * 测试时间戳的初始值
     */
    public function testTimestampInitialValues(): void
    {
        // Arrange
        $record = new VerificationRecord('user123', $this->mockStrategy, 'test', VerificationResult::SUCCESS);

        // Act & Assert - 新创建的实体时间戳应该为null，直到被持久化
        $this->assertNull($record->getCreateTime());
    }

    /**
     * 测试特殊字符和极端数据处理
     */
    public function testSpecialCharactersAndExtremeData(): void
    {
        // Arrange
        $specialUserId = 'user@domain.com+test';
        $specialBusinessType = 'payment/transfer-高风险';
        $record = new VerificationRecord($specialUserId, $this->mockStrategy, $specialBusinessType, VerificationResult::SUCCESS);

        // Act
        $record->setOperationId('op_123/456@789')
               ->setError('E001', 'Error with special chars: <>?"{}[]')
               ->setClientInfoValue('special_key', 'value with 中文 and émojis 🎉');

        // Assert
        $this->assertSame($specialUserId, $record->getUserId());
        $this->assertSame($specialBusinessType, $record->getBusinessType());
        $this->assertSame('op_123/456@789', $record->getOperationId());
        $this->assertStringContainsString('special chars', $record->getErrorMessage());
        $this->assertStringContainsString('🎉', $record->getClientInfoValue('special_key'));
    }
} 