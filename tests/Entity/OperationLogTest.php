<?php

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * OperationLog 实体测试类
 *
 * @internal
 */
#[CoversClass(OperationLog::class)]
final class OperationLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $entity = new OperationLog();
        $entity->setUserId('test_user');
        $entity->setOperationId('test_operation');
        $entity->setOperationType('test_type');

        return $entity;
    }

    private function createOperationLog(string $userId, string $operationId, string $operationType): OperationLog
    {
        $log = new OperationLog();
        $log->setUserId($userId);
        $log->setOperationId($operationId);
        $log->setOperationType($operationType);

        return $log;
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'operationType' => ['operationType', 'new_operation_type'];
        yield 'businessContext' => ['businessContext', ['key' => 'value']];
        yield 'verificationRequired' => ['verificationRequired', true];
        yield 'verificationCompleted' => ['verificationCompleted', true];
        yield 'verificationCount' => ['verificationCount', 5];
        yield 'minVerificationCount' => ['minVerificationCount', 3];
        yield 'status' => ['status', OperationStatus::COMPLETED];
    }

    public function testConstructionWithValidData(): void
    {
        // Arrange
        $userId = 'user123';
        $operationId = 'op456';
        $operationType = 'login';

        // Act
        $operationLog = new OperationLog();
        $operationLog->setUserId($userId);
        $operationLog->setOperationId($operationId);
        $operationLog->setOperationType($operationType);

        // Assert
        $this->assertSame($userId, $operationLog->getUserId());
        $this->assertSame($operationId, $operationLog->getOperationId());
        $this->assertSame($operationType, $operationLog->getOperationType());
        $this->assertNull($operationLog->getBusinessContext());
        $this->assertFalse($operationLog->isVerificationRequired());
        $this->assertFalse($operationLog->isVerificationCompleted());
        $this->assertSame(0, $operationLog->getVerificationCount());
        $this->assertSame(1, $operationLog->getMinVerificationCount());
        $this->assertSame(OperationStatus::PENDING, $operationLog->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $operationLog->getStartedTime());
        $this->assertNull($operationLog->getCompletedTime());
    }

    public function testConstructionWithEmptyStrings(): void
    {
        // Arrange
        $userId = '';
        $operationId = '';
        $operationType = '';

        // Act
        $operationLog = new OperationLog();
        $operationLog->setUserId($userId);
        $operationLog->setOperationId($operationId);
        $operationLog->setOperationType($operationType);

        // Assert
        $this->assertSame('', $operationLog->getUserId());
        $this->assertSame('', $operationLog->getOperationId());
        $this->assertSame('', $operationLog->getOperationType());
    }

    public function testSetOperationType(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act
        $newType = 'logout';
        $operationLog->setOperationType($newType);

        // Assert
        $this->assertSame($newType, $operationLog->getOperationType());
    }

    public function testSetBusinessContextWithArray(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $context = [
            'ip' => '192.168.1.1',
            'browser' => 'Chrome',
            'amount' => 1000.50,
        ];

        // Act
        $operationLog->setBusinessContext($context);

        // Assert
        $this->assertSame($context, $operationLog->getBusinessContext());
    }

    public function testSetBusinessContextWithNull(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act
        $operationLog->setBusinessContext(null);

        // Assert
        $this->assertNull($operationLog->getBusinessContext());
    }

    public function testSetBusinessContextWithEmptyArray(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act
        $operationLog->setBusinessContext([]);

        // Assert
        $this->assertSame([], $operationLog->getBusinessContext());
    }

    public function testSetVerificationRequired(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setVerificationRequired(true);
        $this->assertTrue($operationLog->isVerificationRequired());

        $operationLog->setVerificationRequired(false);
        $this->assertFalse($operationLog->isVerificationRequired());
    }

    public function testSetVerificationCompleted(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setVerificationCompleted(true);
        $this->assertTrue($operationLog->isVerificationCompleted());

        $operationLog->setVerificationCompleted(false);
        $this->assertFalse($operationLog->isVerificationCompleted());
    }

    public function testSetVerificationCount(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert - 测试各种计数值
        $operationLog->setVerificationCount(0);
        $this->assertSame(0, $operationLog->getVerificationCount());

        $operationLog->setVerificationCount(5);
        $this->assertSame(5, $operationLog->getVerificationCount());

        $operationLog->setVerificationCount(100);
        $this->assertSame(100, $operationLog->getVerificationCount());
    }

    public function testSetMinVerificationCount(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setMinVerificationCount(3);
        $this->assertSame(3, $operationLog->getMinVerificationCount());

        $operationLog->setMinVerificationCount(0);
        $this->assertSame(0, $operationLog->getMinVerificationCount());
    }

    public function testSetStatusWithAllEnumValues(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        foreach (OperationStatus::cases() as $status) {
            $operationLog->setStatus($status);
            $this->assertSame($status, $operationLog->getStatus());
        }
    }

    public function testSetStatusUpdatesCompletedTimeForFinalStates(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $this->assertNull($operationLog->getCompletedTime());

        // Act & Assert - 终态应该设置完成时间
        $finalStates = [OperationStatus::COMPLETED, OperationStatus::FAILED, OperationStatus::CANCELLED];

        foreach ($finalStates as $status) {
            $operationLog->setStatus($status);
            $this->assertInstanceOf(\DateTimeInterface::class, $operationLog->getCompletedTime());
            $this->assertSame($status, $operationLog->getStatus());
        }
    }

    public function testSetStatusDoesNotUpdateCompletedTimeForNonFinalStates(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert - 非终态不应该设置完成时间
        $nonFinalStates = [OperationStatus::PENDING, OperationStatus::PROCESSING];

        foreach ($nonFinalStates as $status) {
            $operationLog->setStatus($status);
            $this->assertNull($operationLog->getCompletedTime());
        }
    }

    public function testIncrementVerificationCount(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $this->assertSame(0, $operationLog->getVerificationCount());

        // Act & Assert
        $operationLog->incrementVerificationCount();
        $this->assertSame(1, $operationLog->getVerificationCount());

        $operationLog->incrementVerificationCount();
        $this->assertSame(2, $operationLog->getVerificationCount());

        $operationLog->incrementVerificationCount();
        $this->assertSame(3, $operationLog->getVerificationCount());
    }

    public function testIsVerificationSatisfiedWhenNotRequired(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setVerificationRequired(false);

        // Act & Assert
        $this->assertTrue($operationLog->isVerificationSatisfied());
    }

    public function testIsVerificationSatisfiedWhenRequiredButNotCompleted(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setVerificationRequired(true);
        $operationLog->setVerificationCompleted(false);

        // Act & Assert
        $this->assertFalse($operationLog->isVerificationSatisfied());
    }

    public function testIsVerificationSatisfiedWhenCompletedButCountInsufficient(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setVerificationRequired(true);
        $operationLog->setVerificationCompleted(true);
        $operationLog->setMinVerificationCount(3);
        $operationLog->setVerificationCount(2);

        // Act & Assert
        $this->assertFalse($operationLog->isVerificationSatisfied());
    }

    public function testIsVerificationSatisfiedWhenFullySatisfied(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setVerificationRequired(true);
        $operationLog->setVerificationCompleted(true);
        $operationLog->setMinVerificationCount(2);
        $operationLog->setVerificationCount(3);

        // Act & Assert
        $this->assertTrue($operationLog->isVerificationSatisfied());
    }

    public function testIsVerificationSatisfiedWhenCountEqualsMinimum(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setVerificationRequired(true);
        $operationLog->setVerificationCompleted(true);
        $operationLog->setMinVerificationCount(2);
        $operationLog->setVerificationCount(2);

        // Act & Assert
        $this->assertTrue($operationLog->isVerificationSatisfied());
    }

    public function testGetBusinessContextValueWithExistingKey(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $context = ['ip' => '192.168.1.1', 'amount' => 1500];
        $operationLog->setBusinessContext($context);

        // Act & Assert
        $this->assertSame('192.168.1.1', $operationLog->getBusinessContextValue('ip'));
        $this->assertSame(1500, $operationLog->getBusinessContextValue('amount'));
    }

    public function testGetBusinessContextValueWithNonExistingKey(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(['ip' => '192.168.1.1']);

        // Act & Assert
        $this->assertNull($operationLog->getBusinessContextValue('non_existing'));
    }

    public function testGetBusinessContextValueWithDefault(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(['ip' => '192.168.1.1']);

        // Act & Assert
        $this->assertSame('default_value', $operationLog->getBusinessContextValue('non_existing', 'default_value'));
        $this->assertSame(0, $operationLog->getBusinessContextValue('count', 0));
    }

    public function testGetBusinessContextValueWithNullContext(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(null);

        // Act & Assert
        $this->assertNull($operationLog->getBusinessContextValue('any_key'));
        $this->assertSame('default', $operationLog->getBusinessContextValue('any_key', 'default'));
    }

    public function testSetBusinessContextValueWithNullContext(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(null);

        // Act
        $operationLog->setBusinessContextValue('new_key', 'new_value');

        // Assert
        $this->assertSame(['new_key' => 'new_value'], $operationLog->getBusinessContext());
    }

    public function testSetBusinessContextValueWithExistingContext(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(['existing' => 'value']);

        // Act
        $operationLog->setBusinessContextValue('new_key', 'new_value');

        // Assert
        $expected = ['existing' => 'value', 'new_key' => 'new_value'];
        $this->assertSame($expected, $operationLog->getBusinessContext());
    }

    public function testSetBusinessContextValueOverwritesExisting(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setBusinessContext(['key' => 'old_value']);

        // Act
        $operationLog->setBusinessContextValue('key', 'new_value');

        // Assert
        $this->assertSame('new_value', $operationLog->getBusinessContextValue('key'));
    }

    public function testIsCompleted(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setStatus(OperationStatus::COMPLETED);
        $this->assertTrue($operationLog->isCompleted());

        $operationLog->setStatus(OperationStatus::PENDING);
        $this->assertFalse($operationLog->isCompleted());
    }

    public function testIsFailed(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setStatus(OperationStatus::FAILED);
        $this->assertTrue($operationLog->isFailed());

        $operationLog->setStatus(OperationStatus::COMPLETED);
        $this->assertFalse($operationLog->isFailed());
    }

    public function testIsCancelled(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $operationLog->setStatus(OperationStatus::CANCELLED);
        $this->assertTrue($operationLog->isCancelled());

        $operationLog->setStatus(OperationStatus::PROCESSING);
        $this->assertFalse($operationLog->isCancelled());
    }

    public function testGetDurationWithNullCompletedTime(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // Act & Assert
        $this->assertNull($operationLog->getDuration());
    }

    public function testGetDurationWithCompletedTime(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');

        // 设置一个较早的开始时间
        $reflection = new \ReflectionClass($operationLog);
        $startedTimeProperty = $reflection->getProperty('startedTime');
        $startedTimeProperty->setAccessible(true);
        $startTime = new \DateTimeImmutable('-10 seconds');
        $startedTimeProperty->setValue($operationLog, $startTime);

        // Act
        $operationLog->setStatus(OperationStatus::COMPLETED); // 这会设置完成时间

        // Assert
        $duration = $operationLog->getDuration();
        $this->assertIsFloat($duration);
        $this->assertGreaterThan(0, $duration);
        $this->assertLessThan(20, $duration); // 应该在合理范围内
    }

    public function testToStringMethod(): void
    {
        // Arrange
        $operationId = 'test_op_123';
        $operationType = 'payment';
        $operationLog = $this->createOperationLog('user123', $operationId, $operationType);

        // Act
        $result = (string) $operationLog;

        // Assert
        $this->assertStringContainsString('OperationLog', $result);
        $this->assertStringContainsString($operationId, $result);
        $this->assertStringContainsString($operationType, $result);
        $this->assertStringContainsString('pending', $result);
    }

    public function testToStringWithDifferentStatus(): void
    {
        // Arrange
        $operationLog = $this->createOperationLog('user123', 'op456', 'login');
        $operationLog->setStatus(OperationStatus::COMPLETED);

        // Act
        $result = (string) $operationLog;

        // Assert
        $this->assertStringContainsString('completed', $result);
    }

    public function testComplexVerificationScenario(): void
    {
        // Arrange - 复杂验证场景
        $operationLog = $this->createOperationLog('user123', 'payment_001', 'payment');
        $operationLog->setVerificationRequired(true);
        $operationLog->setMinVerificationCount(3);
        $operationLog->setBusinessContext([
            'amount' => 5000.00,
            'currency' => 'USD',
            'ip' => '192.168.1.100',
        ]);

        // Act & Assert - 逐步完成验证
        $this->assertFalse($operationLog->isVerificationSatisfied());

        $operationLog->incrementVerificationCount(); // 1
        $this->assertFalse($operationLog->isVerificationSatisfied());

        $operationLog->incrementVerificationCount(); // 2
        $this->assertFalse($operationLog->isVerificationSatisfied());

        $operationLog->incrementVerificationCount(); // 3
        $this->assertFalse($operationLog->isVerificationSatisfied()); // 还未标记为完成

        $operationLog->setVerificationCompleted(true);
        $this->assertTrue($operationLog->isVerificationSatisfied()); // 现在满足了
    }
}
