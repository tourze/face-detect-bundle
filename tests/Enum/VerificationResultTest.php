<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * VerificationResult 枚举类测试
 *
 * @internal
 */
#[CoversClass(VerificationResult::class)]
final class VerificationResultTest extends AbstractEnumTestCase
{
    public function testEnumCasesExist(): void
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

    public function testSuccessCaseProperties(): void
    {
        // Act
        $result = VerificationResult::SUCCESS;

        // Assert
        $this->assertSame('success', $result->value);
        $this->assertSame('验证成功', $result->getDescription());
        $this->assertTrue($result->isSuccessful());
        $this->assertFalse($result->isFailure());
    }

    public function testFailedCaseProperties(): void
    {
        // Act
        $result = VerificationResult::FAILED;

        // Assert
        $this->assertSame('failed', $result->value);
        $this->assertSame('验证失败', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isFailure());
    }

    public function testSkippedCaseProperties(): void
    {
        // Act
        $result = VerificationResult::SKIPPED;

        // Assert
        $this->assertSame('skipped', $result->value);
        $this->assertSame('跳过验证', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isFailure());
    }

    public function testTimeoutCaseProperties(): void
    {
        // Act
        $result = VerificationResult::TIMEOUT;

        // Assert
        $this->assertSame('timeout', $result->value);
        $this->assertSame('验证超时', $result->getDescription());
        $this->assertFalse($result->isSuccessful());
        $this->assertTrue($result->isFailure());
    }

    public function testGetDescriptionReturnsCorrectStrings(): void
    {
        // Act & Assert
        $this->assertSame('验证成功', VerificationResult::SUCCESS->getDescription());
        $this->assertSame('验证失败', VerificationResult::FAILED->getDescription());
        $this->assertSame('跳过验证', VerificationResult::SKIPPED->getDescription());
        $this->assertSame('验证超时', VerificationResult::TIMEOUT->getDescription());
    }

    public function testIsSuccessfulReturnsCorrectBoolean(): void
    {
        // Act & Assert
        $this->assertTrue(VerificationResult::SUCCESS->isSuccessful());
        $this->assertFalse(VerificationResult::FAILED->isSuccessful());
        $this->assertFalse(VerificationResult::SKIPPED->isSuccessful());
        $this->assertFalse(VerificationResult::TIMEOUT->isSuccessful());
    }

    public function testIsFailureReturnsCorrectBoolean(): void
    {
        // Act & Assert
        $this->assertFalse(VerificationResult::SUCCESS->isFailure());
        $this->assertTrue(VerificationResult::FAILED->isFailure());
        $this->assertFalse(VerificationResult::SKIPPED->isFailure());
        $this->assertTrue(VerificationResult::TIMEOUT->isFailure());
    }

    public function testEnumValuesAreUnique(): void
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

    public function testEnumCanBeConstructedFromValue(): void
    {
        // Act & Assert
        $this->assertSame(VerificationResult::SUCCESS, VerificationResult::from('success'));
        $this->assertSame(VerificationResult::FAILED, VerificationResult::from('failed'));
        $this->assertSame(VerificationResult::SKIPPED, VerificationResult::from('skipped'));
        $this->assertSame(VerificationResult::TIMEOUT, VerificationResult::from('timeout'));
    }

    public function testEnumTryFromWithValidValues(): void
    {
        // Act & Assert
        $this->assertSame(VerificationResult::SUCCESS, VerificationResult::tryFrom('success'));
        $this->assertSame(VerificationResult::FAILED, VerificationResult::tryFrom('failed'));
        $this->assertSame(VerificationResult::SKIPPED, VerificationResult::tryFrom('skipped'));
        $this->assertSame(VerificationResult::TIMEOUT, VerificationResult::tryFrom('timeout'));
    }

    public function testEnumTryFromWithInvalidValue(): void
    {
        // Act & Assert
        $this->assertNull(VerificationResult::tryFrom('invalid'));
        $this->assertNull(VerificationResult::tryFrom(''));
        $this->assertNull(VerificationResult::tryFrom('SUCCESS')); // 大小写敏感
    }

    public function testEnumFromThrowsExceptionWithInvalidValue(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        VerificationResult::from('invalid');
    }

    public function testEnumComparison(): void
    {
        // Arrange
        $success = VerificationResult::SUCCESS;
        $failed = VerificationResult::FAILED;

        // Act & Assert
        // 枚举是单例的，相同的枚举值总是相同的实例
        $this->assertSame($success, VerificationResult::SUCCESS);
        /* @phpstan-ignore-next-line */
        $this->assertFalse($success === $failed);
        $this->assertEquals($success->value, 'success');
        $this->assertNotEquals($success->value, $failed->value);
    }

    public function testEnumSerialization(): void
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

    public function testEnumJsonSerialization(): void
    {
        // Arrange
        $result = VerificationResult::SKIPPED;

        // Act
        $json = json_encode($result);
        if (false === $json) {
            self::fail('Failed to encode enum to JSON');
        }
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"skipped"', $json);
        $this->assertSame('skipped', $decoded);
    }

    public function testEnumStringRepresentation(): void
    {
        // Act & Assert
        $this->assertSame('success', (string) VerificationResult::SUCCESS->value);
        $this->assertSame('failed', (string) VerificationResult::FAILED->value);
        $this->assertSame('skipped', (string) VerificationResult::SKIPPED->value);
        $this->assertSame('timeout', (string) VerificationResult::TIMEOUT->value);
    }

    public function testEnumInArrayOperations(): void
    {
        // Arrange
        $results = [VerificationResult::SUCCESS, VerificationResult::SKIPPED];

        // Act & Assert
        $this->assertContains(VerificationResult::SUCCESS, $results);
        $this->assertContains(VerificationResult::SKIPPED, $results);
        $this->assertNotContains(VerificationResult::FAILED, $results);
    }

    public function testEnumInMatchExpression(): void
    {
        // Arrange & Act & Assert
        foreach (VerificationResult::cases() as $result) {
            $matchResult = match ($result) {
                VerificationResult::SUCCESS => 'success_result',
                VerificationResult::FAILED => 'failed_result',
                VerificationResult::SKIPPED => 'skipped_result',
                VerificationResult::TIMEOUT => 'timeout_result',
            };

            $expectedResult = match ($result) {
                VerificationResult::SUCCESS => 'success_result',
                VerificationResult::FAILED => 'failed_result',
                VerificationResult::SKIPPED => 'skipped_result',
                VerificationResult::TIMEOUT => 'timeout_result',
            };

            $this->assertSame($expectedResult, $matchResult);
        }
    }

    public function testAllCasesHaveDescriptions(): void
    {
        // Act & Assert
        foreach (VerificationResult::cases() as $result) {
            $description = $result->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    public function testOnlySuccessIsSuccessful(): void
    {
        // Arrange
        $successfulCount = 0;
        $nonSuccessfulCount = 0;

        // Act
        foreach (VerificationResult::cases() as $result) {
            if ($result->isSuccessful()) {
                ++$successfulCount;
            } else {
                ++$nonSuccessfulCount;
            }
        }

        // Assert
        $this->assertSame(1, $successfulCount, '只有一个结果应该是成功的');
        $this->assertSame(3, $nonSuccessfulCount, '应该有三个结果不成功');
        $this->assertTrue(VerificationResult::SUCCESS->isSuccessful());
    }

    public function testFailureCasesCount(): void
    {
        // Arrange
        $failureCount = 0;
        $nonFailureCount = 0;

        // Act
        foreach (VerificationResult::cases() as $result) {
            if ($result->isFailure()) {
                ++$failureCount;
            } else {
                ++$nonFailureCount;
            }
        }

        // Assert
        $this->assertSame(2, $failureCount, '应该有两个失败结果');
        $this->assertSame(2, $nonFailureCount, '应该有两个非失败结果');
    }

    public function testEnumBackedByString(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(VerificationResult::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function testBusinessLogicConsistency(): void
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

    public function testResultCategorization(): void
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

    public function testResultFilteringBySuccess(): void
    {
        // Arrange
        $allResults = VerificationResult::cases();

        // Act
        $successfulResults = array_filter($allResults, fn ($result) => $result->isSuccessful());
        $nonSuccessfulResults = array_filter($allResults, fn ($result) => !$result->isSuccessful());

        // Assert
        $this->assertCount(1, $successfulResults);
        $this->assertCount(3, $nonSuccessfulResults);
        $this->assertContains(VerificationResult::SUCCESS, $successfulResults);
    }

    public function testResultFilteringByFailure(): void
    {
        // Arrange
        $allResults = VerificationResult::cases();

        // Act
        $failureResults = array_filter($allResults, fn ($result) => $result->isFailure());
        $nonFailureResults = array_filter($allResults, fn ($result) => !$result->isFailure());

        // Assert
        $this->assertCount(2, $failureResults);
        $this->assertCount(2, $nonFailureResults);
        $this->assertContains(VerificationResult::FAILED, $failureResults);
        $this->assertContains(VerificationResult::TIMEOUT, $failureResults);
    }

    public function testToArray(): void
    {
        // Act - 测试每个枚举实例的toArray方法
        foreach (VerificationResult::cases() as $result) {
            $arrayResult = $result->toArray();

            // Assert
            $this->assertIsArray($arrayResult);
            $this->assertArrayHasKey('value', $arrayResult);
            $this->assertArrayHasKey('label', $arrayResult);
            $this->assertSame($result->value, $arrayResult['value']);
            $this->assertSame($result->getDescription(), $arrayResult['label']);
        }

        // 验证具体的值
        $this->assertSame(['value' => 'success', 'label' => '验证成功'], VerificationResult::SUCCESS->toArray());
        $this->assertSame(['value' => 'failed', 'label' => '验证失败'], VerificationResult::FAILED->toArray());
        $this->assertSame(['value' => 'skipped', 'label' => '跳过验证'], VerificationResult::SKIPPED->toArray());
        $this->assertSame(['value' => 'timeout', 'label' => '验证超时'], VerificationResult::TIMEOUT->toArray());
    }

    public function testGenOptions(): void
    {
        // Act - 测试静态方法genOptions
        $result = VerificationResult::genOptions();

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        // 验证每个选项的结构
        foreach ($result as $option) {
            $this->assertIsArray($option);
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('text', $option);
            $this->assertArrayHasKey('value', $option);
        }

        // 验证具体的值
        $values = array_column($result, 'value');
        $this->assertContains('success', $values);
        $this->assertContains('failed', $values);
        $this->assertContains('skipped', $values);
        $this->assertContains('timeout', $values);
    }
}
