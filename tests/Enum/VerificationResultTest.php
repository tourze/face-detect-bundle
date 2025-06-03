<?php

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Enum\VerificationResult;

/**
 * VerificationResult 枚举类测试
 */
class VerificationResultTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        // Act & Assert
        $this->assertTrue(enum_exists(VerificationResult::class));
        
        $cases = VerificationResult::cases();
        $this->assertCount(4, $cases);
        
        $caseValues = [];
        foreach ($cases as $case) {
            $caseValues[] = $case->value;
        }
        
        $this->assertContains('success', $caseValues);
        $this->assertContains('failed', $caseValues);
        $this->assertContains('skipped', $caseValues);
        $this->assertContains('timeout', $caseValues);
    }

    public function test_success_case_properties(): void
    {
        // Act
        $result = VerificationResult::SUCCESS;

        // Assert
        $this->assertSame('success', $result->value);
        $this->assertSame('验证成功', $result->getDescription());
        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->isFailure());
    }

    public function test_failed_case_properties(): void
    {
        // Act
        $result = VerificationResult::FAILED;

        // Assert
        $this->assertSame('failed', $result->value);
        $this->assertSame('验证失败', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isFailure());
    }

    public function test_skipped_case_properties(): void
    {
        // Act
        $result = VerificationResult::SKIPPED;

        // Assert
        $this->assertSame('skipped', $result->value);
        $this->assertSame('跳过验证', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isFailure());
    }

    public function test_timeout_case_properties(): void
    {
        // Act
        $result = VerificationResult::TIMEOUT;

        // Assert
        $this->assertSame('timeout', $result->value);
        $this->assertSame('验证超时', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isFailure());
    }

    public function test_get_description_returns_correct_strings(): void
    {
        // Act & Assert
        $this->assertSame('验证成功', VerificationResult::SUCCESS->getDescription());
        $this->assertSame('验证失败', VerificationResult::FAILED->getDescription());
        $this->assertSame('跳过验证', VerificationResult::SKIPPED->getDescription());
        $this->assertSame('验证超时', VerificationResult::TIMEOUT->getDescription());
    }

    public function test_is_successful_returns_correct_boolean(): void
    {
        // Act & Assert
        $this->assertTrue(VerificationResult::SUCCESS->isSuccessful());
        $this->assertFalse(VerificationResult::FAILED->isSuccessful());
        $this->assertFalse(VerificationResult::SKIPPED->isSuccessful());
        $this->assertFalse(VerificationResult::TIMEOUT->isSuccessful());
    }

    public function test_is_failure_returns_correct_boolean(): void
    {
        // Act & Assert
        $this->assertFalse(VerificationResult::SUCCESS->isFailure());
        $this->assertTrue(VerificationResult::FAILED->isFailure());
        $this->assertFalse(VerificationResult::SKIPPED->isFailure());
        $this->assertTrue(VerificationResult::TIMEOUT->isFailure());
    }

    public function test_enum_values_are_strings(): void
    {
        // Act & Assert
        foreach (VerificationResult::cases() as $result) {
            $this->assertIsString($result->value);
        }
    }

    public function test_enum_values_are_unique(): void
    {
        // Arrange
        $cases = VerificationResult::cases();
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
        $this->assertSame(VerificationResult::SUCCESS, VerificationResult::from('success'));
        $this->assertSame(VerificationResult::FAILED, VerificationResult::from('failed'));
        $this->assertSame(VerificationResult::SKIPPED, VerificationResult::from('skipped'));
        $this->assertSame(VerificationResult::TIMEOUT, VerificationResult::from('timeout'));
    }

    public function test_enum_try_from_with_valid_values(): void
    {
        // Act & Assert
        $this->assertSame(VerificationResult::SUCCESS, VerificationResult::tryFrom('success'));
        $this->assertSame(VerificationResult::FAILED, VerificationResult::tryFrom('failed'));
        $this->assertSame(VerificationResult::SKIPPED, VerificationResult::tryFrom('skipped'));
        $this->assertSame(VerificationResult::TIMEOUT, VerificationResult::tryFrom('timeout'));
    }

    public function test_enum_try_from_with_invalid_value(): void
    {
        // Act & Assert
        $this->assertNull(VerificationResult::tryFrom('invalid'));
        $this->assertNull(VerificationResult::tryFrom(''));
        $this->assertNull(VerificationResult::tryFrom('SUCCESS')); // 大小写敏感
    }

    public function test_enum_from_throws_exception_with_invalid_value(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        VerificationResult::from('invalid');
    }

    public function test_enum_comparison(): void
    {
        // Arrange
        $success1 = VerificationResult::SUCCESS;
        $success2 = VerificationResult::SUCCESS;
        $failed = VerificationResult::FAILED;

        // Act & Assert
        $this->assertTrue($success1 === $success2);
        $this->assertFalse($success1 === $failed);
        $this->assertTrue($success1 == $success2);
        $this->assertFalse($success1 == $failed);
    }

    public function test_enum_serialization(): void
    {
        // Arrange
        $result = VerificationResult::TIMEOUT;

        // Act
        $serialized = serialize($result);
        $unserialized = unserialize($serialized);

        // Assert
        $this->assertSame($result, $unserialized);
        $this->assertSame($result->value, $unserialized->value);
    }

    public function test_enum_json_serialization(): void
    {
        // Arrange
        $result = VerificationResult::SKIPPED;

        // Act
        $json = json_encode($result);
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"skipped"', $json);
        $this->assertSame('skipped', $decoded);
    }

    public function test_enum_string_representation(): void
    {
        // Act & Assert
        $this->assertSame('success', (string) VerificationResult::SUCCESS->value);
        $this->assertSame('failed', (string) VerificationResult::FAILED->value);
        $this->assertSame('skipped', (string) VerificationResult::SKIPPED->value);
        $this->assertSame('timeout', (string) VerificationResult::TIMEOUT->value);
    }

    public function test_enum_in_array_operations(): void
    {
        // Arrange
        $results = [VerificationResult::SUCCESS, VerificationResult::SKIPPED];

        // Act & Assert
        $this->assertTrue(in_array(VerificationResult::SUCCESS, $results, true));
        $this->assertTrue(in_array(VerificationResult::SKIPPED, $results, true));
        $this->assertFalse(in_array(VerificationResult::FAILED, $results, true));
    }

    public function test_enum_in_match_expression(): void
    {
        // Arrange
        $result = VerificationResult::TIMEOUT;

        // Act
        $matchResult = match($result) {
            VerificationResult::SUCCESS => 'success_result',
            VerificationResult::FAILED => 'failed_result',
            VerificationResult::SKIPPED => 'skipped_result',
            VerificationResult::TIMEOUT => 'timeout_result',
        };

        // Assert
        $this->assertSame('timeout_result', $matchResult);
    }

    public function test_all_cases_have_descriptions(): void
    {
        // Act & Assert
        foreach (VerificationResult::cases() as $result) {
            $description = $result->getDescription();
            $this->assertIsString($description);
            $this->assertNotEmpty($description);
        }
    }

    public function test_only_success_is_successful(): void
    {
        // Arrange
        $successfulCount = 0;
        $nonSuccessfulCount = 0;

        // Act
        foreach (VerificationResult::cases() as $result) {
            if ($result->isSuccessful()) {
                $successfulCount++;
            } else {
                $nonSuccessfulCount++;
            }
        }

        // Assert
        $this->assertSame(1, $successfulCount, '只有一个结果应该是成功的');
        $this->assertSame(3, $nonSuccessfulCount, '应该有三个结果不成功');
        $this->assertTrue(VerificationResult::SUCCESS->isSuccessful());
    }

    public function test_failure_cases_count(): void
    {
        // Arrange
        $failureCount = 0;
        $nonFailureCount = 0;

        // Act
        foreach (VerificationResult::cases() as $result) {
            if ($result->isFailure()) {
                $failureCount++;
            } else {
                $nonFailureCount++;
            }
        }

        // Assert
        $this->assertSame(2, $failureCount, '应该有两个失败结果');
        $this->assertSame(2, $nonFailureCount, '应该有两个非失败结果');
    }

    public function test_enum_backed_by_string(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(VerificationResult::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function test_business_logic_consistency(): void
    {
        // Assert - 验证业务逻辑一致性
        // 成功结果不应该是失败
        $this->assertTrue(VerificationResult::SUCCESS->isSuccessful());
        $this->assertFalse(VerificationResult::SUCCESS->isFailure());
        
        // 失败和超时都是失败，但不成功
        $this->assertTrue(VerificationResult::FAILED->isFailure());
        $this->assertFalse(VerificationResult::FAILED->isSuccessful());
        $this->assertTrue(VerificationResult::TIMEOUT->isFailure());
        $this->assertFalse(VerificationResult::TIMEOUT->isSuccessful());
        
        // 跳过既不成功也不失败
        $this->assertFalse(VerificationResult::SKIPPED->isSuccessful());
        $this->assertFalse(VerificationResult::SKIPPED->isFailure());
    }

    public function test_result_categorization(): void
    {
        // Assert - 测试结果分类的正确性
        $successResults = [VerificationResult::SUCCESS];
        $failureResults = [VerificationResult::FAILED, VerificationResult::TIMEOUT];
        $neutralResults = [VerificationResult::SKIPPED];
        
        foreach ($successResults as $result) {
            $this->assertTrue($result->isSuccessful(), "成功结果 {$result->value} 应该是成功的");
            $this->assertFalse($result->isFailure(), "成功结果 {$result->value} 不应该是失败的");
        }
        
        foreach ($failureResults as $result) {
            $this->assertFalse($result->isSuccessful(), "失败结果 {$result->value} 不应该是成功的");
            $this->assertTrue($result->isFailure(), "失败结果 {$result->value} 应该是失败的");
        }
        
        foreach ($neutralResults as $result) {
            $this->assertFalse($result->isSuccessful(), "中性结果 {$result->value} 不应该是成功的");
            $this->assertFalse($result->isFailure(), "中性结果 {$result->value} 不应该是失败的");
        }
    }

    public function test_result_filtering_by_success(): void
    {
        // Arrange
        $allResults = VerificationResult::cases();
        
        // Act
        $successfulResults = array_filter($allResults, fn($result) => $result->isSuccessful());
        $nonSuccessfulResults = array_filter($allResults, fn($result) => !$result->isSuccessful());
        
        // Assert
        $this->assertCount(1, $successfulResults);
        $this->assertCount(3, $nonSuccessfulResults);
        $this->assertContains(VerificationResult::SUCCESS, $successfulResults);
    }

    public function test_result_filtering_by_failure(): void
    {
        // Arrange
        $allResults = VerificationResult::cases();
        
        // Act
        $failureResults = array_filter($allResults, fn($result) => $result->isFailure());
        $nonFailureResults = array_filter($allResults, fn($result) => !$result->isFailure());
        
        // Assert
        $this->assertCount(2, $failureResults);
        $this->assertCount(2, $nonFailureResults);
        $this->assertContains(VerificationResult::FAILED, $failureResults);
        $this->assertContains(VerificationResult::TIMEOUT, $failureResults);
    }
} 