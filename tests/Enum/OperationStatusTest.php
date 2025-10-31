<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * OperationStatus 枚举类测试
 *
 * @internal
 */
#[CoversClass(OperationStatus::class)]
final class OperationStatusTest extends AbstractEnumTestCase
{
    public function testEnumCasesExist(): void
    {
        // Act & Assert
        $this->assertTrue(enum_exists(OperationStatus::class));

        $cases = OperationStatus::cases();
        $this->assertCount(5, $cases);

        $caseValues = [];
        foreach ($cases as $case) {
            $caseValues[] = $case->value;
        }

        $this->assertContains('pending', $caseValues);
        $this->assertContains('processing', $caseValues);
        $this->assertContains('completed', $caseValues);
        $this->assertContains('failed', $caseValues);
        $this->assertContains('cancelled', $caseValues);
    }

    public function testPendingCaseProperties(): void
    {
        // Act
        $status = OperationStatus::PENDING;

        // Assert
        $this->assertSame('pending', $status->value);
        $this->assertSame('等待中', $status->getDescription());
        $this->assertFalse($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function testProcessingCaseProperties(): void
    {
        // Act
        $status = OperationStatus::PROCESSING;

        // Assert
        $this->assertSame('processing', $status->value);
        $this->assertSame('处理中', $status->getDescription());
        $this->assertFalse($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function testCompletedCaseProperties(): void
    {
        // Act
        $status = OperationStatus::COMPLETED;

        // Assert
        $this->assertSame('completed', $status->value);
        $this->assertSame('已完成', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertTrue($status->isSuccessful());
    }

    public function testFailedCaseProperties(): void
    {
        // Act
        $status = OperationStatus::FAILED;

        // Assert
        $this->assertSame('failed', $status->value);
        $this->assertSame('失败', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function testCancelledCaseProperties(): void
    {
        // Act
        $status = OperationStatus::CANCELLED;

        // Assert
        $this->assertSame('cancelled', $status->value);
        $this->assertSame('已取消', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function testGetDescriptionReturnsCorrectStrings(): void
    {
        // Act & Assert
        $this->assertSame('等待中', OperationStatus::PENDING->getDescription());
        $this->assertSame('处理中', OperationStatus::PROCESSING->getDescription());
        $this->assertSame('已完成', OperationStatus::COMPLETED->getDescription());
        $this->assertSame('失败', OperationStatus::FAILED->getDescription());
        $this->assertSame('已取消', OperationStatus::CANCELLED->getDescription());
    }

    public function testIsFinalReturnsCorrectBoolean(): void
    {
        // Act & Assert
        $this->assertFalse(OperationStatus::PENDING->isFinal());
        $this->assertFalse(OperationStatus::PROCESSING->isFinal());
        $this->assertTrue(OperationStatus::COMPLETED->isFinal());
        $this->assertTrue(OperationStatus::FAILED->isFinal());
        $this->assertTrue(OperationStatus::CANCELLED->isFinal());
    }

    public function testIsSuccessfulReturnsCorrectBoolean(): void
    {
        // Act & Assert
        $this->assertFalse(OperationStatus::PENDING->isSuccessful());
        $this->assertFalse(OperationStatus::PROCESSING->isSuccessful());
        $this->assertTrue(OperationStatus::COMPLETED->isSuccessful());
        $this->assertFalse(OperationStatus::FAILED->isSuccessful());
        $this->assertFalse(OperationStatus::CANCELLED->isSuccessful());
    }

    public function testEnumValuesAreUnique(): void
    {
        // Arrange
        $cases = OperationStatus::cases();
        $values = [];
        foreach ($cases as $case) {
            $values[] = $case->value;
        }

        // Act & Assert
        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues, '枚举值应该是唯一的');
    }

    public function testEnumCanBeConstructedFromValue(): void
    {
        // Act & Assert
        $this->assertSame(OperationStatus::PENDING, OperationStatus::from('pending'));
        $this->assertSame(OperationStatus::PROCESSING, OperationStatus::from('processing'));
        $this->assertSame(OperationStatus::COMPLETED, OperationStatus::from('completed'));
        $this->assertSame(OperationStatus::FAILED, OperationStatus::from('failed'));
        $this->assertSame(OperationStatus::CANCELLED, OperationStatus::from('cancelled'));
    }

    public function testEnumTryFromWithValidValues(): void
    {
        // Act & Assert
        $this->assertSame(OperationStatus::PENDING, OperationStatus::tryFrom('pending'));
        $this->assertSame(OperationStatus::PROCESSING, OperationStatus::tryFrom('processing'));
        $this->assertSame(OperationStatus::COMPLETED, OperationStatus::tryFrom('completed'));
        $this->assertSame(OperationStatus::FAILED, OperationStatus::tryFrom('failed'));
        $this->assertSame(OperationStatus::CANCELLED, OperationStatus::tryFrom('cancelled'));
    }

    public function testEnumTryFromWithInvalidValue(): void
    {
        // Act & Assert
        $this->assertNull(OperationStatus::tryFrom('invalid'));
        $this->assertNull(OperationStatus::tryFrom(''));
        $this->assertNull(OperationStatus::tryFrom('PENDING')); // 大小写敏感
    }

    public function testEnumFromThrowsExceptionWithInvalidValue(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        OperationStatus::from('invalid');
    }

    public function testEnumComparison(): void
    {
        // Arrange
        $pending = OperationStatus::PENDING;
        $completed = OperationStatus::COMPLETED;

        // Act & Assert
        // 枚举是单例的，相同的枚举值总是相同的实例
        $this->assertSame($pending, OperationStatus::PENDING);
        /* @phpstan-ignore-next-line */
        $this->assertFalse($pending === $completed);
        $this->assertEquals($pending->value, 'pending');
        $this->assertNotEquals($pending->value, $completed->value);
    }

    public function testEnumSerialization(): void
    {
        // Arrange
        $status = OperationStatus::PROCESSING;

        // Act
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        // Assert
        $this->assertSame($status, $unserialized);
        $this->assertSame($status->value, $unserialized->value);
    }

    public function testEnumJsonSerialization(): void
    {
        // Arrange
        $status = OperationStatus::FAILED;

        // Act
        $json = json_encode($status);
        if (false === $json) {
            self::fail('Failed to encode enum to JSON');
        }
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"failed"', $json);
        $this->assertSame('failed', $decoded);
    }

    public function testEnumStringRepresentation(): void
    {
        // Act & Assert
        $this->assertSame('pending', (string) OperationStatus::PENDING->value);
        $this->assertSame('processing', (string) OperationStatus::PROCESSING->value);
        $this->assertSame('completed', (string) OperationStatus::COMPLETED->value);
        $this->assertSame('failed', (string) OperationStatus::FAILED->value);
        $this->assertSame('cancelled', (string) OperationStatus::CANCELLED->value);
    }

    public function testEnumInArrayOperations(): void
    {
        // Arrange
        $statuses = [OperationStatus::PENDING, OperationStatus::PROCESSING];

        // Act & Assert
        $this->assertContains(OperationStatus::PENDING, $statuses);
        $this->assertContains(OperationStatus::PROCESSING, $statuses);
        $this->assertNotContains(OperationStatus::COMPLETED, $statuses);
    }

    public function testEnumInMatchExpression(): void
    {
        // Arrange & Act & Assert
        foreach (OperationStatus::cases() as $status) {
            $result = match ($status) {
                OperationStatus::PENDING => 'pending_result',
                OperationStatus::PROCESSING => 'processing_result',
                OperationStatus::COMPLETED => 'completed_result',
                OperationStatus::FAILED => 'failed_result',
                OperationStatus::CANCELLED => 'cancelled_result',
            };

            $expectedResult = match ($status) {
                OperationStatus::PENDING => 'pending_result',
                OperationStatus::PROCESSING => 'processing_result',
                OperationStatus::COMPLETED => 'completed_result',
                OperationStatus::FAILED => 'failed_result',
                OperationStatus::CANCELLED => 'cancelled_result',
            };

            $this->assertSame($expectedResult, $result);
        }
    }

    public function testAllCasesHaveDescriptions(): void
    {
        // Act & Assert
        foreach (OperationStatus::cases() as $status) {
            $description = $status->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    public function testOnlyCompletedIsSuccessful(): void
    {
        // Arrange
        $successfulCount = 0;
        $nonSuccessfulCount = 0;

        // Act
        foreach (OperationStatus::cases() as $status) {
            if ($status->isSuccessful()) {
                ++$successfulCount;
            } else {
                ++$nonSuccessfulCount;
            }
        }

        // Assert
        $this->assertSame(1, $successfulCount, '只有一个状态应该是成功的');
        $this->assertSame(4, $nonSuccessfulCount, '应该有四个状态不成功');
        $this->assertTrue(OperationStatus::COMPLETED->isSuccessful());
    }

    public function testFinalStatesCount(): void
    {
        // Arrange
        $finalCount = 0;
        $nonFinalCount = 0;

        // Act
        foreach (OperationStatus::cases() as $status) {
            if ($status->isFinal()) {
                ++$finalCount;
            } else {
                ++$nonFinalCount;
            }
        }

        // Assert
        $this->assertSame(3, $finalCount, '应该有三个终态');
        $this->assertSame(2, $nonFinalCount, '应该有两个非终态');
    }

    public function testEnumBackedByString(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(OperationStatus::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function testBusinessLogicConsistency(): void
    {
        // Assert - 验证业务逻辑一致性
        // 只有已完成状态是成功的
        $this->assertTrue(OperationStatus::COMPLETED->isSuccessful());
        $this->assertTrue(OperationStatus::COMPLETED->isFinal());

        // 失败和取消是终态但不成功
        $this->assertTrue(OperationStatus::FAILED->isFinal());
        $this->assertFalse(OperationStatus::FAILED->isSuccessful());
        $this->assertTrue(OperationStatus::CANCELLED->isFinal());
        $this->assertFalse(OperationStatus::CANCELLED->isSuccessful());

        // 等待和处理中不是终态也不成功
        $this->assertFalse(OperationStatus::PENDING->isFinal());
        $this->assertFalse(OperationStatus::PENDING->isSuccessful());
        $this->assertFalse(OperationStatus::PROCESSING->isFinal());
        $this->assertFalse(OperationStatus::PROCESSING->isSuccessful());
    }

    public function testStateTransitionLogic(): void
    {
        // Assert - 测试状态转换的逻辑性
        $nonFinalStates = [OperationStatus::PENDING, OperationStatus::PROCESSING];
        $finalStates = [OperationStatus::COMPLETED, OperationStatus::FAILED, OperationStatus::CANCELLED];

        foreach ($nonFinalStates as $state) {
            $this->assertFalse($state->isFinal(), "非终态 {$state->value} 不应该是终态");
        }

        foreach ($finalStates as $state) {
            $this->assertTrue($state->isFinal(), "终态 {$state->value} 应该是终态");
        }
    }

    public function testToArray(): void
    {
        // Act - 测试每个枚举实例的toArray方法
        foreach (OperationStatus::cases() as $status) {
            $result = $status->toArray();

            // Assert
            $this->assertIsArray($result);
            $this->assertArrayHasKey('value', $result);
            $this->assertArrayHasKey('label', $result);
            $this->assertSame($status->value, $result['value']);
            $this->assertSame($status->getDescription(), $result['label']);
        }

        // 验证具体的值
        $this->assertSame(['value' => 'pending', 'label' => '等待中'], OperationStatus::PENDING->toArray());
        $this->assertSame(['value' => 'processing', 'label' => '处理中'], OperationStatus::PROCESSING->toArray());
        $this->assertSame(['value' => 'completed', 'label' => '已完成'], OperationStatus::COMPLETED->toArray());
        $this->assertSame(['value' => 'failed', 'label' => '失败'], OperationStatus::FAILED->toArray());
        $this->assertSame(['value' => 'cancelled', 'label' => '已取消'], OperationStatus::CANCELLED->toArray());
    }

    public function testGenOptions(): void
    {
        // Act - 测试静态方法genOptions
        $result = OperationStatus::genOptions();

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(5, $result);

        // 验证每个选项的结构
        foreach ($result as $option) {
            $this->assertIsArray($option);
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('text', $option);
            $this->assertArrayHasKey('value', $option);
        }

        // 验证具体的值
        $values = array_column($result, 'value');
        $this->assertContains('pending', $values);
        $this->assertContains('processing', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('failed', $values);
        $this->assertContains('cancelled', $values);
    }
}
