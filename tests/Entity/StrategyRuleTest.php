<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

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
 *
 * @internal
 */
#[CoversClass(StrategyRule::class)]
final class StrategyRuleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $rule = new StrategyRule();
        $rule->setRuleType('test_rule_type');
        $rule->setRuleName('test_rule_name');
        $rule->setConditions(['condition1' => 'value1']);
        $rule->setActions(['action1' => 'result1']);

        return $rule;
    }

    /**
     * 创建StrategyRule实体的辅助方法，使用setter方法设置属性
     *
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $actions
     */
    private function createStrategyRule(string $ruleType, string $ruleName, array $conditions = [], array $actions = []): StrategyRule
    {
        $rule = new StrategyRule();
        $rule->setRuleType($ruleType);
        $rule->setRuleName($ruleName);
        $rule->setConditions($conditions);
        $rule->setActions($actions);

        return $rule;
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'ruleType' => ['ruleType', 'new_rule_type'];
        yield 'ruleName' => ['ruleName', 'new_rule_name'];
        yield 'conditions' => ['conditions', ['new_condition' => 'new_value']];
        yield 'actions' => ['actions', ['new_action' => 'new_result']];
        yield 'priority' => ['priority', 10];
        yield 'enabled' => ['enabled', false];
    }

    /**
     * 测试构造函数创建基本规则
     */
    public function testConstructorWithMinimalParameters(): void
    {
        // Arrange & Act
        $rule = $this->createStrategyRule('time', 'Working Hours Rule');

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
        $rule = $this->createStrategyRule('time', 'Working Hours Rule', $conditions, $actions);

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
        $rule = $this->createStrategyRule('', '');

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
        $rule = $this->createStrategyRule('frequency', 'Rate Limit Rule');

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
        $rule = $this->createStrategyRule('risk', 'High Risk Check');

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
        $rule = $this->createStrategyRule('amount', 'Amount Limit Rule');
        /*
         * 使用具体类 VerificationStrategy 进行 mock 的原因：
         * 1. VerificationStrategy 是一个 Doctrine 实体类，没有对应的接口
         * 2. 在测试 StrategyRule 时需要模拟策略对象的行为
         * 3. 这是测试实体关联关系的标准做法，因为 Doctrine 实体通常不实现接口
         * 4. Mock 对象可以避免创建真实的数据库记录，保持测试的独立性
         */
        $strategy = $this->createMock(VerificationStrategy::class);

        // Act
        $rule->setStrategy($strategy);

        // Assert
        $this->assertSame($strategy, $rule->getStrategy());
    }

    /**
     * 测试策略关联设置为null
     */
    public function testStrategyRelationshipSetToNull(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('time', 'Test Rule');
        /*
         * 使用具体类 VerificationStrategy 进行 mock 的原因：
         * 1. VerificationStrategy 是一个 Doctrine 实体类，没有对应的接口
         * 2. 在测试 StrategyRule 时需要模拟策略对象的行为
         * 3. 这是测试实体关联关系的标准做法，因为 Doctrine 实体通常不实现接口
         * 4. Mock 对象可以避免创建真实的数据库记录，保持测试的独立性
         */
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
        $rule = $this->createStrategyRule('time', 'Test Rule');

        // Act
        $rule->setRuleType('frequency');

        // Assert
        $this->assertSame('frequency', $rule->getRuleType());
    }

    /**
     * 测试规则名称设置
     */
    public function testSetRuleName(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('risk', 'Original Name');

        // Act
        $rule->setRuleName('Updated Name');

        // Assert
        $this->assertSame('Updated Name', $rule->getRuleName());
    }

    /**
     * 测试条件设置
     */
    public function testSetConditions(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('amount', 'Amount Rule');
        $newConditions = ['min_amount' => 100, 'max_amount' => 10000];

        // Act
        $rule->setConditions($newConditions);

        // Assert
        $this->assertSame($newConditions, $rule->getConditions());
    }

    /**
     * 测试动作设置
     */
    public function testSetActions(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('frequency', 'Rate Rule');
        $newActions = ['block' => true, 'wait_seconds' => 60];

        // Act
        $rule->setActions($newActions);

        // Assert
        $this->assertSame($newActions, $rule->getActions());
    }

    /**
     * 测试启用状态设置
     */
    public function testSetEnabled(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('time', 'Time Rule');

        // Act
        $rule->setEnabled(false);

        // Assert
        $this->assertFalse($rule->isEnabled());
    }

    /**
     * 测试优先级设置
     */
    public function testSetPriority(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('risk', 'Risk Rule');

        // Act
        $rule->setPriority(100);

        // Assert
        $this->assertSame(100, $rule->getPriority());
    }

    /**
     * 测试负数优先级设置
     */
    public function testSetNegativePriority(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('amount', 'Amount Rule');

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
        $rule = $this->createStrategyRule('amount', 'Amount Rule', $conditions);

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
        $rule = $this->createStrategyRule('frequency', 'Rate Rule');

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
        $rule = $this->createStrategyRule('time', 'Time Rule');

        // Act
        $rule->setConditionValue('start_time', '09:00');

        // Assert
        $this->assertSame('09:00', $rule->getConditionValue('start_time'));
    }

    /**
     * 测试设置多个条件值
     */
    public function testSetMultipleConditionValues(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('risk', 'Risk Rule');

        // Act
        $rule->setConditionValue('score_threshold', 80);
        $rule->setConditionValue('ip_whitelist', ['192.168.1.1', '10.0.0.1']);
        $rule->setConditionValue('check_enabled', true);

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
        $rule = $this->createStrategyRule('frequency', 'Rate Rule', [], $actions);

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
        $rule = $this->createStrategyRule('amount', 'Amount Rule');

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
        $rule = $this->createStrategyRule('frequency', 'Rate Rule');

        // Act
        $rule->setActionValue('block', true);

        // Assert
        $this->assertTrue($rule->getActionValue('block'));
    }

    /**
     * 测试设置多个动作值
     */
    public function testSetMultipleActionValues(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('risk', 'Risk Rule');

        // Act
        $rule->setActionValue('block', true);
        $rule->setActionValue('redirect_url', '/security/warning');
        $rule->setActionValue('log_level', 'warning');

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
        $rule = $this->createStrategyRule('time', 'Time Rule');
        /*
         * 使用具体类 VerificationStrategy 进行 mock 的原因：
         * 1. VerificationStrategy 是一个 Doctrine 实体类，没有对应的接口
         * 2. 在测试 StrategyRule 时需要模拟策略对象的行为
         * 3. 这是测试实体关联关系的标准做法，因为 Doctrine 实体通常不实现接口
         * 4. Mock 对象可以避免创建真实的数据库记录，保持测试的独立性
         */
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
        $rule = $this->createStrategyRule('frequency', 'Rate Rule');
        /*
         * 使用具体类 VerificationStrategy 进行 mock 的原因：
         * 1. VerificationStrategy 是一个 Doctrine 实体类，没有对应的接口
         * 2. 在测试 StrategyRule 时需要模拟策略对象的行为
         * 3. 这是测试实体关联关系的标准做法，因为 Doctrine 实体通常不实现接口
         * 4. Mock 对象可以避免创建真实的数据库记录，保持测试的独立性
         */
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
        $rule = $this->createStrategyRule('risk', 'Risk Rule');
        $rule->setEnabled(false);
        /*
         * 使用具体类 VerificationStrategy 进行 mock 的原因：
         * 1. VerificationStrategy 是一个 Doctrine 实体类，没有对应的接口
         * 2. 在测试 StrategyRule 时需要模拟策略对象的行为
         * 3. 这是测试实体关联关系的标准做法，因为 Doctrine 实体通常不实现接口
         * 4. Mock 对象可以避免创建真实的数据库记录，保持测试的独立性
         */
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
        $rule = $this->createStrategyRule('amount', 'Amount Rule');

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
                'blacklist' => ['10.0.0.100'],
            ],
            'thresholds' => [
                'min_amount' => 1,
                'max_amount' => 10000,
                'daily_limit' => 5,
            ],
        ];

        $complexActions = [
            'primary' => [
                'action' => 'block',
                'message' => 'Verification required',
            ],
            'fallback' => [
                'action' => 'allow',
                'conditions' => ['user_verified' => true],
            ],
            'logging' => [
                'level' => 'warning',
                'include_details' => true,
            ],
        ];

        // Act
        $rule = $this->createStrategyRule('complex', 'Complex Rule', $complexConditions, $complexActions);

        // Assert
        $this->assertSame($complexConditions, $rule->getConditions());
        $this->assertSame($complexActions, $rule->getActions());

        // 测试嵌套访问
        $this->assertSame(['start' => '09:00', 'end' => '17:00'], $rule->getConditionValue('time_range'));
        $primaryAction = $rule->getActionValue('primary');
        $this->assertIsArray($primaryAction);
        $this->assertSame('block', $primaryAction['action']);
    }

    /**
     * 测试边界情况 - 极大优先级值
     */
    public function testExtremePriorityValues(): void
    {
        // Arrange
        $rule = $this->createStrategyRule('test', 'Test Rule');

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
        $rule = $this->createStrategyRule('empty', 'Empty Rule');

        // Act
        $rule->setConditions([]);
        $rule->setActions([]);
        $rule->setConditionValue('null_value', null);
        $rule->setActionValue('empty_array', []);

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
        $rule = $this->createStrategyRule('time', 'Time Rule');

        // Assert - 新创建的实体时间戳应该为null，直到被持久化
        $this->assertNull($rule->getCreateTime());
        $this->assertNull($rule->getUpdateTime());
    }
}
