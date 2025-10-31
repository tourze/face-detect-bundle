<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * FaceProfileStatus 枚举类测试
 *
 * @internal
 */
#[CoversClass(FaceProfileStatus::class)]
final class FaceProfileStatusTest extends AbstractEnumTestCase
{
    public function testEnumCasesExist(): void
    {
        // Act & Assert
        $this->assertTrue(enum_exists(FaceProfileStatus::class));

        $cases = FaceProfileStatus::cases();
        $this->assertCount(3, $cases);

        $caseValues = [];
        foreach ($cases as $case) {
            $caseValues[] = $case->value;
        }

        $this->assertContains('active', $caseValues);
        $this->assertContains('expired', $caseValues);
        $this->assertContains('disabled', $caseValues);
    }

    public function testActiveCaseProperties(): void
    {
        // Act
        $status = FaceProfileStatus::ACTIVE;

        // Assert
        $this->assertSame('active', $status->value);
        $this->assertSame('活跃', $status->getDescription());
        $this->assertTrue($status->isUsable());
    }

    public function testExpiredCaseProperties(): void
    {
        // Act
        $status = FaceProfileStatus::EXPIRED;

        // Assert
        $this->assertSame('expired', $status->value);
        $this->assertSame('已过期', $status->getDescription());
        $this->assertFalse($status->isUsable());
    }

    public function testDisabledCaseProperties(): void
    {
        // Act
        $status = FaceProfileStatus::DISABLED;

        // Assert
        $this->assertSame('disabled', $status->value);
        $this->assertSame('已禁用', $status->getDescription());
        $this->assertFalse($status->isUsable());
    }

    public function testGetDescriptionReturnsCorrectStrings(): void
    {
        // Arrange
        $expectedDescriptions = [
            FaceProfileStatus::ACTIVE->value => FaceProfileStatus::ACTIVE,
            FaceProfileStatus::EXPIRED->value => FaceProfileStatus::EXPIRED,
            FaceProfileStatus::DISABLED->value => FaceProfileStatus::DISABLED,
        ];

        // Act & Assert
        $this->assertSame('活跃', FaceProfileStatus::ACTIVE->getDescription());
        $this->assertSame('已过期', FaceProfileStatus::EXPIRED->getDescription());
        $this->assertSame('已禁用', FaceProfileStatus::DISABLED->getDescription());
    }

    public function testIsUsableReturnsCorrectBoolean(): void
    {
        // Arrange
        $expectedUsability = [
            'active_usable' => FaceProfileStatus::ACTIVE,
            'expired_not_usable' => FaceProfileStatus::EXPIRED,
            'disabled_not_usable' => FaceProfileStatus::DISABLED,
        ];

        // Act & Assert
        foreach ($expectedUsability as $status) {
            $expectedUsable = FaceProfileStatus::ACTIVE === $status;
            $this->assertSame($expectedUsable, $status->isUsable());
        }
    }

    public function testEnumValuesAreUnique(): void
    {
        // Arrange
        $cases = FaceProfileStatus::cases();
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
        $this->assertSame(FaceProfileStatus::ACTIVE, FaceProfileStatus::from('active'));
        $this->assertSame(FaceProfileStatus::EXPIRED, FaceProfileStatus::from('expired'));
        $this->assertSame(FaceProfileStatus::DISABLED, FaceProfileStatus::from('disabled'));
    }

    public function testEnumTryFromWithValidValues(): void
    {
        // Act & Assert
        $this->assertSame(FaceProfileStatus::ACTIVE, FaceProfileStatus::tryFrom('active'));
        $this->assertSame(FaceProfileStatus::EXPIRED, FaceProfileStatus::tryFrom('expired'));
        $this->assertSame(FaceProfileStatus::DISABLED, FaceProfileStatus::tryFrom('disabled'));
    }

    public function testEnumTryFromWithInvalidValue(): void
    {
        // Act & Assert
        $this->assertNull(FaceProfileStatus::tryFrom('invalid'));
        $this->assertNull(FaceProfileStatus::tryFrom(''));
        $this->assertNull(FaceProfileStatus::tryFrom('ACTIVE')); // 大小写敏感
    }

    public function testEnumFromThrowsExceptionWithInvalidValue(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        FaceProfileStatus::from('invalid');
    }

    public function testEnumComparison(): void
    {
        // Arrange
        $active1 = FaceProfileStatus::ACTIVE;
        $expired = FaceProfileStatus::EXPIRED;

        // Act & Assert
        // 枚举是单例的，相同的枚举值总是相同的实例
        $this->assertSame($active1, FaceProfileStatus::ACTIVE);
        /* @phpstan-ignore-next-line */
        $this->assertFalse($active1 === $expired);
        $this->assertEquals($active1->value, 'active');
        $this->assertNotEquals($active1->value, $expired->value);
    }

    public function testEnumSerialization(): void
    {
        // Arrange
        $status = FaceProfileStatus::ACTIVE;

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
        $status = FaceProfileStatus::EXPIRED;

        // Act
        $json = json_encode($status);
        if (false === $json) {
            self::fail('Failed to encode enum to JSON');
        }
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"expired"', $json);
        $this->assertSame('expired', $decoded);
    }

    public function testEnumStringRepresentation(): void
    {
        // Act & Assert
        $this->assertSame('active', (string) FaceProfileStatus::ACTIVE->value);
        $this->assertSame('expired', (string) FaceProfileStatus::EXPIRED->value);
        $this->assertSame('disabled', (string) FaceProfileStatus::DISABLED->value);
    }

    public function testEnumInArrayOperations(): void
    {
        // Arrange
        $statuses = [FaceProfileStatus::ACTIVE, FaceProfileStatus::EXPIRED];

        // Act & Assert
        $this->assertContains(FaceProfileStatus::ACTIVE, $statuses);
        $this->assertContains(FaceProfileStatus::EXPIRED, $statuses);
        $this->assertNotContains(FaceProfileStatus::DISABLED, $statuses);
    }

    public function testEnumInMatchExpression(): void
    {
        // Arrange & Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
            $result = match ($status) {
                FaceProfileStatus::ACTIVE => 'active_result',
                FaceProfileStatus::EXPIRED => 'expired_result',
                FaceProfileStatus::DISABLED => 'disabled_result',
            };

            $expectedResult = match ($status) {
                FaceProfileStatus::ACTIVE => 'active_result',
                FaceProfileStatus::EXPIRED => 'expired_result',
                FaceProfileStatus::DISABLED => 'disabled_result',
            };

            $this->assertSame($expectedResult, $result);
        }
    }

    public function testAllCasesHaveDescriptions(): void
    {
        // Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
            $description = $status->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    public function testOnlyActiveIsUsable(): void
    {
        // Arrange
        $usableCount = 0;
        $nonUsableCount = 0;

        // Act
        foreach (FaceProfileStatus::cases() as $status) {
            if ($status->isUsable()) {
                ++$usableCount;
            } else {
                ++$nonUsableCount;
            }
        }

        // Assert
        $this->assertSame(1, $usableCount, '只有一个状态应该是可用的');
        $this->assertSame(2, $nonUsableCount, '应该有两个状态不可用');
        $this->assertTrue(FaceProfileStatus::ACTIVE->isUsable());
    }

    public function testEnumBackedByString(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(FaceProfileStatus::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function testEnumMethodsAreCaseSensitive(): void
    {
        // Arrange
        $status = FaceProfileStatus::ACTIVE;

        // Act & Assert
        // 验证方法可以正常调用并返回预期类型
        $this->assertNotEmpty($status->getDescription());
        $this->assertSame('活跃', $status->getDescription());
        $this->assertTrue($status->isUsable());
    }

    public function testToArray(): void
    {
        // Act - 测试每个枚举实例的toArray方法
        foreach (FaceProfileStatus::cases() as $status) {
            $result = $status->toArray();

            // Assert
            $this->assertIsArray($result);
            $this->assertArrayHasKey('value', $result);
            $this->assertArrayHasKey('label', $result);
            $this->assertSame($status->value, $result['value']);
            $this->assertSame($status->getLabel(), $result['label']);
        }

        // 验证具体的值
        $activeResult = FaceProfileStatus::ACTIVE->toArray();
        $this->assertSame('active', $activeResult['value']);
        $this->assertSame('活跃', $activeResult['label']);

        $expiredResult = FaceProfileStatus::EXPIRED->toArray();
        $this->assertSame('expired', $expiredResult['value']);
        $this->assertSame('已过期', $expiredResult['label']);

        $disabledResult = FaceProfileStatus::DISABLED->toArray();
        $this->assertSame('disabled', $disabledResult['value']);
        $this->assertSame('已禁用', $disabledResult['label']);
    }

    public function testGenOptions(): void
    {
        // Act - 测试静态方法genOptions
        $result = FaceProfileStatus::genOptions();

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // 验证每个选项的结构
        foreach ($result as $option) {
            $this->assertIsArray($option);
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('text', $option);
            $this->assertArrayHasKey('value', $option);
        }

        // 验证具体的值
        $values = array_column($result, 'value');
        $this->assertContains('active', $values);
        $this->assertContains('expired', $values);
        $this->assertContains('disabled', $values);
    }
}
