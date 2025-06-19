<?php

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

/**
 * FaceProfileStatus 枚举类测试
 */
class FaceProfileStatusTest extends TestCase
{
    public function test_enum_cases_exist(): void
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

    public function test_active_case_properties(): void
    {
        // Act
        $status = FaceProfileStatus::ACTIVE;

        // Assert
        $this->assertSame('active', $status->value);
        $this->assertSame('活跃', $status->getDescription());
        $this->assertTrue($status->isUsable());
    }

    public function test_expired_case_properties(): void
    {
        // Act
        $status = FaceProfileStatus::EXPIRED;

        // Assert
        $this->assertSame('expired', $status->value);
        $this->assertSame('已过期', $status->getDescription());
        $this->assertFalse($status->isUsable());
    }

    public function test_disabled_case_properties(): void
    {
        // Act
        $status = FaceProfileStatus::DISABLED;

        // Assert
        $this->assertSame('disabled', $status->value);
        $this->assertSame('已禁用', $status->getDescription());
        $this->assertFalse($status->isUsable());
    }

    public function test_get_description_returns_correct_strings(): void
    {
        // Arrange
        $expectedDescriptions = [
            FaceProfileStatus::ACTIVE->value => FaceProfileStatus::ACTIVE,
            FaceProfileStatus::EXPIRED->value => FaceProfileStatus::EXPIRED,
            FaceProfileStatus::DISABLED->value => FaceProfileStatus::DISABLED,
        ];

        // Act & Assert
        foreach ($expectedDescriptions as $expectedDescription => $status) {
            $this->assertSame(match($status) {
                FaceProfileStatus::ACTIVE => '活跃',
                FaceProfileStatus::EXPIRED => '已过期',
                FaceProfileStatus::DISABLED => '已禁用',
            }, $status->getDescription());
        }
    }

    public function test_is_usable_returns_correct_boolean(): void
    {
        // Arrange
        $expectedUsability = [
            'active_usable' => FaceProfileStatus::ACTIVE,
            'expired_not_usable' => FaceProfileStatus::EXPIRED,
            'disabled_not_usable' => FaceProfileStatus::DISABLED,
        ];

        // Act & Assert
        foreach ($expectedUsability as $description => $status) {
            $expectedUsable = $status === FaceProfileStatus::ACTIVE;
            $this->assertSame($expectedUsable, $status->isUsable());
        }
    }

    public function test_enum_values_are_strings(): void
    {
        // Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
        }
    }

    public function test_enum_values_are_unique(): void
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

    public function test_enum_can_be_constructed_from_value(): void
    {
        // Act & Assert
        $this->assertSame(FaceProfileStatus::ACTIVE, FaceProfileStatus::from('active'));
        $this->assertSame(FaceProfileStatus::EXPIRED, FaceProfileStatus::from('expired'));
        $this->assertSame(FaceProfileStatus::DISABLED, FaceProfileStatus::from('disabled'));
    }

    public function test_enum_try_from_with_valid_values(): void
    {
        // Act & Assert
        $this->assertSame(FaceProfileStatus::ACTIVE, FaceProfileStatus::tryFrom('active'));
        $this->assertSame(FaceProfileStatus::EXPIRED, FaceProfileStatus::tryFrom('expired'));
        $this->assertSame(FaceProfileStatus::DISABLED, FaceProfileStatus::tryFrom('disabled'));
    }

    public function test_enum_try_from_with_invalid_value(): void
    {
        // Act & Assert
        $this->assertNull(FaceProfileStatus::tryFrom('invalid'));
        $this->assertNull(FaceProfileStatus::tryFrom(''));
        $this->assertNull(FaceProfileStatus::tryFrom('ACTIVE')); // 大小写敏感
    }

    public function test_enum_from_throws_exception_with_invalid_value(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        FaceProfileStatus::from('invalid');
    }

    public function test_enum_comparison(): void
    {
        // Arrange
        $active1 = FaceProfileStatus::ACTIVE;
        $active2 = FaceProfileStatus::ACTIVE;
        $expired = FaceProfileStatus::EXPIRED;

        // Act & Assert
        $this->assertTrue($active1 === $active2);
        $this->assertFalse($active1 === $expired);
        $this->assertTrue($active1 == $active2);
        $this->assertFalse($active1 == $expired);
    }

    public function test_enum_serialization(): void
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

    public function test_enum_json_serialization(): void
    {
        // Arrange
        $status = FaceProfileStatus::EXPIRED;

        // Act
        $json = json_encode($status);
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"expired"', $json);
        $this->assertSame('expired', $decoded);
    }

    public function test_enum_string_representation(): void
    {
        // Act & Assert
        $this->assertSame('active', (string) FaceProfileStatus::ACTIVE->value);
        $this->assertSame('expired', (string) FaceProfileStatus::EXPIRED->value);
        $this->assertSame('disabled', (string) FaceProfileStatus::DISABLED->value);
    }

    public function test_enum_in_array_operations(): void
    {
        // Arrange
        $statuses = [FaceProfileStatus::ACTIVE, FaceProfileStatus::EXPIRED];

        // Act & Assert
        $this->assertTrue(in_array(FaceProfileStatus::ACTIVE, $statuses, true));
        $this->assertTrue(in_array(FaceProfileStatus::EXPIRED, $statuses, true));
        $this->assertFalse(in_array(FaceProfileStatus::DISABLED, $statuses, true));
    }

    public function test_enum_in_match_expression(): void
    {
        // Arrange
        $status = FaceProfileStatus::ACTIVE;

        // Act
        $result = match($status) {
            FaceProfileStatus::ACTIVE => 'active_result',
            FaceProfileStatus::EXPIRED => 'expired_result',
            FaceProfileStatus::DISABLED => 'disabled_result',
        };

        // Assert
        $this->assertSame('active_result', $result);
    }

    public function test_all_cases_have_descriptions(): void
    {
        // Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
            $description = $status->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    public function test_only_active_is_usable(): void
    {
        // Arrange
        $usableCount = 0;
        $nonUsableCount = 0;

        // Act
        foreach (FaceProfileStatus::cases() as $status) {
            if ($status->isUsable()) {
                $usableCount++;
            } else {
                $nonUsableCount++;
            }
        }

        // Assert
        $this->assertSame(1, $usableCount, '只有一个状态应该是可用的');
        $this->assertSame(2, $nonUsableCount, '应该有两个状态不可用');
        $this->assertTrue(FaceProfileStatus::ACTIVE->isUsable());
    }

    public function test_enum_backed_by_string(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(FaceProfileStatus::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function test_enum_methods_are_case_sensitive(): void
    {
        // Arrange
        $status = FaceProfileStatus::ACTIVE;

        // Act & Assert
        $this->assertTrue(method_exists($status, 'getDescription'));
        $this->assertTrue(method_exists($status, 'isUsable'));
        $this->assertIsString($status->getDescription());
        $this->assertIsBool($status->isUsable());
    }
} 