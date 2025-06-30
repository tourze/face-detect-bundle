<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * StrategyRule 实体单元测试
 *
 * 测试策略规则实体的核心功能：
 * - 构造函数和基本属性
 * - 规则条件和动作管理
 * - 优先级和启用状态处理
 * - 时间戳更新机制
 * - 业务逻辑方法
 * - 边界条件和异常场景
 */
class StrategyRuleTest extends TestCase
{
    /**
     * 测试构造函数创建基本规则
     */
    public function testConstructorWithMinimalParameters(): void
    {
        // Arrange & Act
        $rule = new StrategyRule('time', 'Working Hours Rule');

        // Assert
        $this->assertSame('time', $rule->getRuleType());
        $this->assertSame('Working Hours Rule', $rule->getRuleName());
        $this->assertSame([], $rule->getConditions());
        $this->assertSame([], $rule->getActions());
        $this->assertTrue($rule->isEnabled());
        $this->assertSame(0, $rule->getPriority());
        $this->assertNull($rule->getStrategy());
        $this->assertNull($rule->getCreateTime());
        $this->assertNull($rule->getUpdateTime());
    }

    /**
     * 测试构造函数创建完整规则
     */
    public function testConstructorWithFullParameters(): void
    {
        // Arrange
        $conditions = ['start_hour' => 9, 'end_hour' => 17];
        $actions = ['block' => true, 'message' => 'Outside working hours'];

        // Act
        $rule = new StrategyRule('time', 'Working Hours Rule', $conditions, $actions);

        // Assert
        $this->assertSame('time', $rule->getRuleType());
        $this->assertSame('Working Hours Rule', $rule->getRuleName());
        $this->assertSame($conditions, $rule->getConditions());
        $this->assertSame($actions, $rule->getActions());
    }

    /**
     * 测试构造函数处理空字符串参数
     */
    public function testConstructorWithEmptyStrings(): void
    {
        // Arrange & Act
        $rule = new StrategyRule('', '');

        // Assert
        $this->assertSame('', $rule->getRuleType());
        $this->assertSame('', $rule->getRuleName());
    }

    /**
     * 测试__toString()方法无ID时的表现
     */
    public function testToStringWithoutId(): void
    {
        // Arrange
        $rule = new StrategyRule('frequency', 'Rate Limit Rule');

        // Act
        $result = (string) $rule;

        // Assert
        $this->assertSame('StrategyRule[0]: Rate Limit Rule (frequency)', $result);
    }

    /**
     * 测试__toString()方法含有ID时的表现（使用反射设置ID）
     */
    public function testToStringWithId(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'High Risk Check');
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass($rule);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($rule, 123);

        // Act
        $result = (string) $rule;

        // Assert
        $this->assertSame('StrategyRule[123]: High Risk Check (risk)', $result);
    }

    /**
     * 测试策略关联设置和获取
     */
    public function testStrategyRelationship(): void
    {
        // Arrange
        $rule = new StrategyRule('amount', 'Amount Limit Rule');
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);

        // Act
        $result = $rule->setStrategy($strategy);

        // Assert
        $this->assertSame($rule, $result); // 测试链式调用
        $this->assertSame($strategy, $rule->getStrategy());
    }

    /**
     * 测试策略关联设置为null
     */
    public function testStrategyRelationshipSetToNull(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Test Rule');
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);
        $rule->setStrategy($strategy);

        // Act
        $rule->setStrategy(null);

        // Assert
        $this->assertNull($rule->getStrategy());
    }

    /**
     * 测试规则类型设置
     */
    public function testSetRuleType(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Test Rule');

        // Act
        $result = $rule->setRuleType('frequency');

        // Assert
        $this->assertSame($rule, $result); // 测试链式调用
        $this->assertSame('frequency', $rule->getRuleType());
    }

    /**
     * 测试规则名称设置
     */
    public function testSetRuleName(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'Original Name');

        // Act
        $result = $rule->setRuleName('Updated Name');

        // Assert
        $this->assertSame($rule, $result);
        $this->assertSame('Updated Name', $rule->getRuleName());
    }

    /**
     * 测试条件设置
     */
    public function testSetConditions(): void
    {
        // Arrange
        $rule = new StrategyRule('amount', 'Amount Rule');
        $newConditions = ['min_amount' => 100, 'max_amount' => 10000];

        // Act
        $result = $rule->setConditions($newConditions);

        // Assert
        $this->assertSame($rule, $result);
        $this->assertSame($newConditions, $rule->getConditions());
    }

    /**
     * 测试动作设置
     */
    public function testSetActions(): void
    {
        // Arrange
        $rule = new StrategyRule('frequency', 'Rate Rule');
        $newActions = ['block' => true, 'wait_seconds' => 60];

        // Act
        $result = $rule->setActions($newActions);

        // Assert
        $this->assertSame($rule, $result);
        $this->assertSame($newActions, $rule->getActions());
    }

    /**
     * 测试启用状态设置
     */
    public function testSetEnabled(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Time Rule');

        // Act
        $result = $rule->setEnabled(false);

        // Assert
        $this->assertSame($rule, $result);
        $this->assertFalse($rule->isEnabled());
    }

    /**
     * 测试优先级设置
     */
    public function testSetPriority(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'Risk Rule');

        // Act
        $result = $rule->setPriority(100);

        // Assert
        $this->assertSame($rule, $result);
        $this->assertSame(100, $rule->getPriority());
    }

    /**
     * 测试负数优先级设置
     */
    public function testSetNegativePriority(): void
    {
        // Arrange
        $rule = new StrategyRule('amount', 'Amount Rule');

        // Act
        $rule->setPriority(-10);

        // Assert
        $this->assertSame(-10, $rule->getPriority());
    }

    /**
     * 测试获取条件值 - 存在的键
     */
    public function testGetConditionValueExistingKey(): void
    {
        // Arrange
        $conditions = ['min_value' => 100, 'max_value' => 1000, 'enabled' => true];
        $rule = new StrategyRule('amount', 'Amount Rule', $conditions);

        // Act & Assert
        $this->assertSame(100, $rule->getConditionValue('min_value'));
        $this->assertSame(1000, $rule->getConditionValue('max_value'));
        $this->assertTrue($rule->getConditionValue('enabled'));
    }

    /**
     * 测试获取条件值 - 不存在的键返回默认值
     */
    public function testGetConditionValueNonExistingKeyWithDefault(): void
    {
        // Arrange
        $rule = new StrategyRule('frequency', 'Rate Rule');

        // Act & Assert
        $this->assertSame('default', $rule->getConditionValue('non_existing', 'default'));
        $this->assertSame(42, $rule->getConditionValue('another_key', 42));
        $this->assertNull($rule->getConditionValue('null_key'));
    }

    /**
     * 测试设置条件值
     */
    public function testSetConditionValue(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Time Rule');

        // Act
        $result = $rule->setConditionValue('start_time', '09:00');

        // Assert
        $this->assertSame($rule, $result);
        $this->assertSame('09:00', $rule->getConditionValue('start_time'));
    }

    /**
     * 测试设置多个条件值
     */
    public function testSetMultipleConditionValues(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'Risk Rule');

        // Act
        $rule->setConditionValue('score_threshold', 80)
             ->setConditionValue('ip_whitelist', ['192.168.1.1', '10.0.0.1'])
             ->setConditionValue('check_enabled', true);

        // Assert
        $this->assertSame(80, $rule->getConditionValue('score_threshold'));
        $this->assertSame(['192.168.1.1', '10.0.0.1'], $rule->getConditionValue('ip_whitelist'));
        $this->assertTrue($rule->getConditionValue('check_enabled'));
    }

    /**
     * 测试获取动作值 - 存在的键
     */
    public function testGetActionValueExistingKey(): void
    {
        // Arrange
        $actions = ['block' => true, 'message' => 'Access denied', 'code' => 403];
        $rule = new StrategyRule('frequency', 'Rate Rule', [], $actions);

        // Act & Assert
        $this->assertTrue($rule->getActionValue('block'));
        $this->assertSame('Access denied', $rule->getActionValue('message'));
        $this->assertSame(403, $rule->getActionValue('code'));
    }

    /**
     * 测试获取动作值 - 不存在的键返回默认值
     */
    public function testGetActionValueNonExistingKeyWithDefault(): void
    {
        // Arrange
        $rule = new StrategyRule('amount', 'Amount Rule');

        // Act & Assert
        $this->assertSame('fallback', $rule->getActionValue('non_existing', 'fallback'));
        $this->assertSame(500, $rule->getActionValue('timeout', 500));
        $this->assertNull($rule->getActionValue('missing_action'));
    }

    /**
     * 测试设置动作值
     */
    public function testSetActionValue(): void
    {
        // Arrange
        $rule = new StrategyRule('frequency', 'Rate Rule');

        // Act
        $result = $rule->setActionValue('block', true);

        // Assert
        $this->assertSame($rule, $result);
        $this->assertTrue($rule->getActionValue('block'));
    }

    /**
     * 测试设置多个动作值
     */
    public function testSetMultipleActionValues(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'Risk Rule');

        // Act
        $rule->setActionValue('block', true)
             ->setActionValue('redirect_url', '/security/warning')
             ->setActionValue('log_level', 'warning');

        // Assert
        $this->assertTrue($rule->getActionValue('block'));
        $this->assertSame('/security/warning', $rule->getActionValue('redirect_url'));
        $this->assertSame('warning', $rule->getActionValue('log_level'));
    }

    /**
     * 测试isUsable()方法 - 规则启用且策略启用
     */
    public function testIsUsableWhenEnabledWithEnabledStrategy(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Time Rule');
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);
        $strategy->method('isEnabled')->willReturn(true);
        $rule->setStrategy($strategy);

        // Act & Assert
        $this->assertTrue($rule->isUsable());
    }

    /**
     * 测试isUsable()方法 - 规则启用但策略禁用
     */
    public function testIsUsableWhenEnabledWithDisabledStrategy(): void
    {
        // Arrange
        $rule = new StrategyRule('frequency', 'Rate Rule');
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);
        $strategy->method('isEnabled')->willReturn(false);
        $rule->setStrategy($strategy);

        // Act & Assert
        $this->assertFalse($rule->isUsable());
    }

    /**
     * 测试isUsable()方法 - 规则禁用
     */
    public function testIsUsableWhenDisabled(): void
    {
        // Arrange
        $rule = new StrategyRule('risk', 'Risk Rule');
        $rule->setEnabled(false);
        /** @var VerificationStrategy&\PHPUnit\Framework\MockObject\MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);
        $strategy->method('isEnabled')->willReturn(true);
        $rule->setStrategy($strategy);

        // Act & Assert
        $this->assertFalse($rule->isUsable());
    }

    /**
     * 测试isUsable()方法 - 无关联策略
     */
    public function testIsUsableWithoutStrategy(): void
    {
        // Arrange
        $rule = new StrategyRule('amount', 'Amount Rule');

        // Act & Assert
        $this->assertFalse($rule->isUsable());
    }

    /**
     * 测试复杂的条件和动作结构
     */
    public function testComplexConditionsAndActions(): void
    {
        // Arrange
        $complexConditions = [
            'time_range' => ['start' => '09:00', 'end' => '17:00'],
            'ip_rules' => [
                'whitelist' => ['192.168.1.0/24'],
                'blacklist' => ['10.0.0.100']
            ],
            'thresholds' => [
                'min_amount' => 1,
                'max_amount' => 10000,
                'daily_limit' => 5
            ]
        ];
        
        $complexActions = [
            'primary' => [
                'action' => 'block',
                'message' => 'Verification required'
            ],
            'fallback' => [
                'action' => 'allow',
                'conditions' => ['user_verified' => true]
            ],
            'logging' => [
                'level' => 'warning',
                'include_details' => true
            ]
        ];

        // Act
        $rule = new StrategyRule('complex', 'Complex Rule', $complexConditions, $complexActions);

        // Assert
        $this->assertSame($complexConditions, $rule->getConditions());
        $this->assertSame($complexActions, $rule->getActions());
        
        // 测试嵌套访问
        $this->assertSame(['start' => '09:00', 'end' => '17:00'], $rule->getConditionValue('time_range'));
        $this->assertSame('block', $rule->getActionValue('primary')['action']);
    }

    /**
     * 测试边界情况 - 极大优先级值
     */
    public function testExtremePriorityValues(): void
    {
        // Arrange
        $rule = new StrategyRule('test', 'Test Rule');

        // Act & Assert - 极大正数
        $rule->setPriority(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $rule->getPriority());

        // Act & Assert - 极小负数
        $rule->setPriority(PHP_INT_MIN);
        $this->assertSame(PHP_INT_MIN, $rule->getPriority());
    }

    /**
     * 测试空数组和null值处理
     */
    public function testEmptyArraysAndNullValues(): void
    {
        // Arrange
        $rule = new StrategyRule('empty', 'Empty Rule');

        // Act
        $rule->setConditions([])
             ->setActions([])
             ->setConditionValue('null_value', null)
             ->setActionValue('empty_array', []);

        // Assert
        $this->assertNull($rule->getConditionValue('null_value'));
        $this->assertSame([], $rule->getActionValue('empty_array'));
        
        // 检查条件数组包含了null_value
        $conditions = $rule->getConditions();
        $this->assertArrayHasKey('null_value', $conditions);
        $this->assertNull($conditions['null_value']);
        
        // 检查动作数组包含了empty_array
        $actions = $rule->getActions();
        $this->assertArrayHasKey('empty_array', $actions);
        $this->assertSame([], $actions['empty_array']);
    }

    /**
     * 测试时间戳初始值
     */
    public function testTimestampInitialValues(): void
    {
        // Arrange
        $rule = new StrategyRule('time', 'Time Rule');

        // Assert - 新创建的实体时间戳应该为null，直到被持久化
        $this->assertNull($rule->getCreateTime());
        $this->assertNull($rule->getUpdateTime());
    }
} 