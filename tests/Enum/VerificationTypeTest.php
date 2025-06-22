<?php

namespace Tourze\FaceDetectBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Enum\VerificationType;

/**
 * VerificationType 枚举类测试
 */
class VerificationTypeTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        // Act & Assert
        $this->assertTrue(enum_exists(VerificationType::class));
        
        $cases = VerificationType::cases();
        $this->assertCount(3, $cases);
        
        $caseValues = [];
        foreach ($cases as $case) {
            $caseValues[] = $case->value;
        }
        
        $this->assertContains('required', $caseValues);
        $this->assertContains('optional', $caseValues);
        $this->assertContains('forced', $caseValues);
    }

    public function test_required_case_properties(): void
    {
        // Act
        $type = VerificationType::REQUIRED;

        // Assert
        $this->assertSame('required', $type->value);
        $this->assertSame('必需验证', $type->getDescription());
        $this->assertTrue($type->isMandatory());
    }

    public function test_optional_case_properties(): void
    {
        // Act
        $type = VerificationType::OPTIONAL;

        // Assert
        $this->assertSame('optional', $type->value);
        $this->assertSame('可选验证', $type->getDescription());
        $this->assertFalse($type->isMandatory());
    }

    public function test_forced_case_properties(): void
    {
        // Act
        $type = VerificationType::FORCED;

        // Assert
        $this->assertSame('forced', $type->value);
        $this->assertSame('强制验证', $type->getDescription());
        $this->assertTrue($type->isMandatory());
    }

    public function test_get_description_returns_correct_strings(): void
    {
        // Act & Assert
        $this->assertSame('必需验证', VerificationType::REQUIRED->getDescription());
        $this->assertSame('可选验证', VerificationType::OPTIONAL->getDescription());
        $this->assertSame('强制验证', VerificationType::FORCED->getDescription());
    }

    public function test_is_mandatory_returns_correct_boolean(): void
    {
        // Act & Assert
        $this->assertTrue(VerificationType::REQUIRED->isMandatory());
        $this->assertFalse(VerificationType::OPTIONAL->isMandatory());
        $this->assertTrue(VerificationType::FORCED->isMandatory());
    }


    public function test_enum_values_are_unique(): void
    {
        // Arrange
        $cases = VerificationType::cases();
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
        $this->assertSame(VerificationType::REQUIRED, VerificationType::from('required'));
        $this->assertSame(VerificationType::OPTIONAL, VerificationType::from('optional'));
        $this->assertSame(VerificationType::FORCED, VerificationType::from('forced'));
    }

    public function test_enum_try_from_with_valid_values(): void
    {
        // Act & Assert
        $this->assertSame(VerificationType::REQUIRED, VerificationType::tryFrom('required'));
        $this->assertSame(VerificationType::OPTIONAL, VerificationType::tryFrom('optional'));
        $this->assertSame(VerificationType::FORCED, VerificationType::tryFrom('forced'));
    }

    public function test_enum_try_from_with_invalid_value(): void
    {
        // Act & Assert
        $this->assertNull(VerificationType::tryFrom('invalid'));
        $this->assertNull(VerificationType::tryFrom(''));
        $this->assertNull(VerificationType::tryFrom('REQUIRED')); // 大小写敏感
    }

    public function test_enum_from_throws_exception_with_invalid_value(): void
    {
        // Arrange & Act & Assert
        $this->expectException(\ValueError::class);
        VerificationType::from('invalid');
    }

    public function test_enum_comparison(): void
    {
        // Arrange
        $required = VerificationType::REQUIRED;
        $optional = VerificationType::OPTIONAL;

        // Act & Assert
        // 枚举是单例的，相同的枚举值总是相同的实例
        $this->assertSame($required, VerificationType::REQUIRED);
        $this->assertNotSame($required, $optional);
        $this->assertEquals($required->value, 'required');
        $this->assertNotEquals($required->value, $optional->value);
    }

    public function test_enum_serialization(): void
    {
        // Arrange
        $type = VerificationType::FORCED;

        // Act
        $serialized = serialize($type);
        $unserialized = unserialize($serialized);

        // Assert
        $this->assertSame($type, $unserialized);
        $this->assertSame($type->value, $unserialized->value);
    }

    public function test_enum_json_serialization(): void
    {
        // Arrange
        $type = VerificationType::OPTIONAL;

        // Act
        $json = json_encode($type);
        $decoded = json_decode($json, true);

        // Assert
        $this->assertSame('"optional"', $json);
        $this->assertSame('optional', $decoded);
    }

    public function test_enum_string_representation(): void
    {
        // Act & Assert
        $this->assertSame('required', (string) VerificationType::REQUIRED->value);
        $this->assertSame('optional', (string) VerificationType::OPTIONAL->value);
        $this->assertSame('forced', (string) VerificationType::FORCED->value);
    }

    public function test_enum_in_array_operations(): void
    {
        // Arrange
        $types = [VerificationType::REQUIRED, VerificationType::FORCED];

        // Act & Assert
        $this->assertContains(VerificationType::REQUIRED, $types);
        $this->assertContains(VerificationType::FORCED, $types);
        $this->assertNotContains(VerificationType::OPTIONAL, $types);
    }

    public function test_enum_in_match_expression(): void
    {
        // Arrange
        $type = VerificationType::FORCED;

        // Act
        $result = match($type) {
            VerificationType::REQUIRED => 'required_result',
            VerificationType::OPTIONAL => 'optional_result',
            VerificationType::FORCED => 'forced_result',
        };

        // Assert
        $this->assertSame('forced_result', $result);
    }

    public function test_all_cases_have_descriptions(): void
    {
        // Act & Assert
        foreach (VerificationType::cases() as $type) {
            $description = $type->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    public function test_mandatory_types_count(): void
    {
        // Arrange
        $mandatoryCount = 0;
        $nonMandatoryCount = 0;

        // Act
        foreach (VerificationType::cases() as $type) {
            if ($type->isMandatory()) {
                $mandatoryCount++;
            } else {
                $nonMandatoryCount++;
            }
        }

        // Assert
        $this->assertSame(2, $mandatoryCount, '应该有两个强制类型');
        $this->assertSame(1, $nonMandatoryCount, '应该有一个非强制类型');
    }

    public function test_enum_backed_by_string(): void
    {
        // Act & Assert
        $reflection = new \ReflectionEnum(VerificationType::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertSame('string', $reflection->getBackingType()->getName());
    }

    public function test_business_logic_consistency(): void
    {
        // Assert - 验证业务逻辑一致性
        // REQUIRED 和 FORCED 都是强制的
        $this->assertTrue(VerificationType::REQUIRED->isMandatory());
        $this->assertTrue(VerificationType::FORCED->isMandatory());
        
        // OPTIONAL 不是强制的
        $this->assertFalse(VerificationType::OPTIONAL->isMandatory());
    }

    public function test_type_categorization(): void
    {
        // Assert - 测试类型分类的正确性
        $mandatoryTypes = [VerificationType::REQUIRED, VerificationType::FORCED];
        $optionalTypes = [VerificationType::OPTIONAL];
        
        foreach ($mandatoryTypes as $type) {
            $this->assertTrue($type->isMandatory(), "强制类型 {$type->value} 应该是强制的");
        }
        
        foreach ($optionalTypes as $type) {
            $this->assertFalse($type->isMandatory(), "可选类型 {$type->value} 不应该是强制的");
        }
    }

    public function test_type_filtering_by_mandatory(): void
    {
        // Arrange
        $allTypes = VerificationType::cases();
        
        // Act
        $mandatoryTypes = array_filter($allTypes, fn($type) => $type->isMandatory());
        $nonMandatoryTypes = array_filter($allTypes, fn($type) => !$type->isMandatory());
        
        // Assert
        $this->assertCount(2, $mandatoryTypes);
        $this->assertCount(1, $nonMandatoryTypes);
        $this->assertContains(VerificationType::REQUIRED, $mandatoryTypes);
        $this->assertContains(VerificationType::FORCED, $mandatoryTypes);
        $this->assertContains(VerificationType::OPTIONAL, $nonMandatoryTypes);
    }

    public function test_verification_requirement_levels(): void
    {
        // Assert - 测试验证要求级别
        // FORCED 应该是最高级别的要求
        $this->assertTrue(VerificationType::FORCED->isMandatory());
        
        // REQUIRED 是标准要求
        $this->assertTrue(VerificationType::REQUIRED->isMandatory());
        
        // OPTIONAL 是最低级别
        $this->assertFalse(VerificationType::OPTIONAL->isMandatory());
    }

    public function test_enum_provides_sufficient_coverage(): void
    {
        // Assert - 验证枚举覆盖了必要的验证类型
        $cases = VerificationType::cases();
        $values = array_map(fn($case) => $case->value, $cases);
        
        // 必须包含基础的验证类型
        $this->assertContains('required', $values, '应该包含必需验证类型');
        $this->assertContains('optional', $values, '应该包含可选验证类型');
        $this->assertContains('forced', $values, '应该包含强制验证类型');
        
        // 验证类型数量合理
        $this->assertGreaterThanOrEqual(3, count($values), '应该至少有3种验证类型');
        $this->assertLessThanOrEqual(5, count($values), '验证类型不应该过多');
    }

    public function test_enum_semantics_are_meaningful(): void
    {
        // Assert - 验证枚举语义的合理性
        
        // 检查描述文本是否有意义
        foreach (VerificationType::cases() as $type) {
            $description = $type->getDescription();
            $this->assertStringContainsString('验证', $description, '描述应包含"验证"关键词');
            $this->assertGreaterThan(2, mb_strlen($description), '描述应该有足够的长度');
        }
        
        // 检查强制性逻辑是否合理
        $mandatoryCount = 0;
        foreach (VerificationType::cases() as $type) {
            if ($type->isMandatory()) {
                $mandatoryCount++;
            }
        }
        
        $this->assertGreaterThan(0, $mandatoryCount, '至少应该有一种强制类型');
        $this->assertLessThan(count(VerificationType::cases()), $mandatoryCount, '不应该所有类型都是强制的');
    }
} 