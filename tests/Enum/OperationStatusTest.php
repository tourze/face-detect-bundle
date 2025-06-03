<?php

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

/**
 * OperationStatus 枚举类测试
 */
class OperationStatusTest extends TestCase
{
    public function test_enum_cases_exist(): void
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

    public function test_pending_case_properties(): void
    {
        // Act
        $status = OperationStatus::PENDING;

        // Assert
        $this->assertSame('pending', $status->value);
        $this->assertSame('等待中', $status->getDescription());
        $this->assertFalse($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function test_processing_case_properties(): void
    {
        // Act
        $status = OperationStatus::PROCESSING;

        // Assert
        $this->assertSame('processing', $status->value);
        $this->assertSame('处理中', $status->getDescription());
        $this->assertFalse($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function test_completed_case_properties(): void
    {
        // Act
        $status = OperationStatus::COMPLETED;

        // Assert
        $this->assertSame('completed', $status->value);
        $this->assertSame('已完成', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertTrue($status->isSuccessful());
    }

    public function test_failed_case_properties(): void
    {
        // Act
        $status = OperationStatus::FAILED;

        // Assert
        $this->assertSame('failed', $status->value);
        $this->assertSame('失败', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function test_cancelled_case_properties(): void
    {
        // Act
        $status = OperationStatus::CANCELLED;

        // Assert
        $this->assertSame('cancelled', $status->value);
        $this->assertSame('已取消', $status->getDescription());
        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->isSuccessful());
    }

    public function test_get_description_returns_correct_strings(): void
    {
        // Act & Assert
        $this->assertSame('等待中', OperationStatus::PENDING->getDescription());
        $this->assertSame('处理中', OperationStatus::PROCESSING->getDescription());
        $this->assertSame('已完成', OperationStatus::COMPLETED->getDescription());
        $this->assertSame('失败', OperationStatus::FAILED->getDescription());
        $this->assertSame('已取消', OperationStatus::CANCELLED->getDescription());
    }

    public function test_is_final_returns_correct_boolean(): void
    {
        // Act & Assert
        $this->assertFalse(OperationStatus::PENDING->isFinal());
        $this->assertFalse(OperationStatus::PROCESSING->isFinal());
        $this->assertTrue(OperationStatus::COMPLETED->isFinal());
        $this->assertTrue(OperationStatus::FAILED->isFinal());
        $this->assertTrue(OperationStatus::CANCELLED->isFinal());
    }

    public function test_is_successful_returns_correct_boolean(): void
    {
        // Act & Assert
        $this->assertFalse(OperationStatus::PENDING->isSuccessful());
        $this->assertFalse(OperationStatus::PROCESSING->isSuccessful());
        $this->assertTrue(OperationStatus::COMPLETED->isSuccessful());
        $this->assertFalse(OperationStatus::FAILED->isSuccessful());
        $this->assertFalse(OperationStatus::CANCELLED->isSuccessful());
    }

    public function test_enum_values_are_strings(): void
    {
        // Act & Assert
        foreach (OperationStatus::cases() as $status) {
            $this->assertIsString($status->value);
        }
    }

    public function test_enum_values_are_unique(): void
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

    public function test_enum_can_be_constructed_from_value(): void
    {
        // Act & Assert
        $this->assertSame(OperationStatus::PENDING, OperationStatus::from('pending'));
        $this->assertSame(OperationStatus::PROCESSING, OperationStatus::from('processing'));
        $this->assertSame(OperationStatus::COMPLETED, OperationStatus::from('completed'));
        $this->assertSame(OperationStatus::FAILED, OperationStatus::from('failed'));
        $this->assertSame(OperationStatus::CANCELLED, OperationStatus::from('cancelled'));
    }

    public function test_enum_try_from_with_valid_values(): void
    {
        // Act & Assert
        $this->assertSame(OperationStatus::PENDING, OperationStatus::tryFrom('pending'));
        $this->assertSame(OperationStatus::PROCESSING, OperationStatus::tryFrom('processing'));
        $this->assertSame(OperationStatus::COMPLETED, OperationStatus::tryFrom('completed'));
        $this->assertSame(OperationStatus::FAILED, OperationStatus::tryFrom('failed'));
        $this->assertSame(OperationStatus::CANCELLED, OperationStatus::tryFrom('cancelled'));
    }

    public function test_enum_try_from_with_invalid_value(): void
    {
        // Act & Assert
        $this->assertNull(OperationStatus::tryFrom('invalid'));
        $this->assertNull(OperationStatus::tryFrom(''));
        $this->assertNull(OperationStatus::tryFrom('PENDING')); // 大小写敏感
    }

    public function test_enum_from_throws_exception_with_invalid_value(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        OperationStatus::from('invalid');
    }

    public function test_enum_comparison(): void
    {
        // Arrange
        $pending1 = OperationStatus::PENDING;
        $pending2 = OperationStatus::PENDING;
        $completed = OperationStatus::COMPLETED;

        // Act & Assert
        $this->assertTrue($pending1 === $pending2);
        $this->assertFalse($pending1 === $completed);
        $this->assertTrue($pending1 == $pending2);
        $this->assertFalse($pending1 == $completed);
    }

    public function test_enum_serialization(): void
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

    public function test_enum_json_serialization(): void
    {
        // Arrange
        $status = OperationStatus::FAILED;

        // Act
        $json = json_encode($status);
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"failed"', $json);
        $this->assertSame('failed', $decoded);
    }

    public function test_enum_string_representation(): void
    {
        // Act & Assert
        $this->assertSame('pending', (string) OperationStatus::PENDING->value);
        $this->assertSame('processing', (string) OperationStatus::PROCESSING->value);
        $this->assertSame('completed', (string) OperationStatus::COMPLETED->value);
        $this->assertSame('failed', (string) OperationStatus::FAILED->value);
        $this->assertSame('cancelled', (string) OperationStatus::CANCELLED->value);
    }

    public function test_enum_in_array_operations(): void
    {
        // Arrange
        $statuses = [OperationStatus::PENDING, OperationStatus::PROCESSING];

        // Act & Assert
        $this->assertTrue(in_array(OperationStatus::PENDING, $statuses, true));
        $this->assertTrue(in_array(OperationStatus::PROCESSING, $statuses, true));
        $this->assertFalse(in_array(OperationStatus::COMPLETED, $statuses, true));
    }

    public function test_enum_in_match_expression(): void
    {
        // Arrange
        $status = OperationStatus::COMPLETED;

        // Act
        $result = match($status) {
            OperationStatus::PENDING => 'pending_result',
            OperationStatus::PROCESSING => 'processing_result',
            OperationStatus::COMPLETED => 'completed_result',
            OperationStatus::FAILED => 'failed_result',
            OperationStatus::CANCELLED => 'cancelled_result',
        };

        // Assert
        $this->assertSame('completed_result', $result);
    }

    public function test_all_cases_have_descriptions(): void
    {
        // Act & Assert
        foreach (OperationStatus::cases() as $status) {
            $description = $status->getDescription();
            $this->assertIsString($description);
            $this->assertNotEmpty($description);
        }
    }

    public function test_only_completed_is_successful(): void
    {
        // Arrange
        $successfulCount = 0;
        $nonSuccessfulCount = 0;

        // Act
        foreach (OperationStatus::cases() as $status) {
            if ($status->isSuccessful()) {
                $successfulCount++;
            } else {
                $nonSuccessfulCount++;
            }
        }

        // Assert
        $this->assertSame(1, $successfulCount, '只有一个状态应该是成功的');
        $this->assertSame(4, $nonSuccessfulCount, '应该有四个状态不成功');
        $this->assertTrue(OperationStatus::COMPLETED->isSuccessful());
    }

    public function test_final_states_count(): void
    {
        // Arrange
        $finalCount = 0;
        $nonFinalCount = 0;

        // Act
        foreach (OperationStatus::cases() as $status) {
            if ($status->isFinal()) {
                $finalCount++;
            } else {
                $nonFinalCount++;
            }
        }

        // Assert
        $this->assertSame(3, $finalCount, '应该有三个终态');
        $this->assertSame(2, $nonFinalCount, '应该有两个非终态');
    }

    public function test_enum_backed_by_string(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(OperationStatus::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function test_business_logic_consistency(): void
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

    public function test_state_transition_logic(): void
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
} 