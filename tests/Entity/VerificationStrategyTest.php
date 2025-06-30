<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * VerificationStrategy 实体单元测试
 *
 * 测试验证策略实体的核心功能：
 * - 构造函数和基本属性
 * - 策略配置管理
 * - 规则关联操作
 * - 业务逻辑方法
 * - 时间戳更新机制
 * - 边界条件和异常场景
 */
class VerificationStrategyTest extends TestCase
{
    /**
     * 测试构造函数创建基本策略
     */
    public function testConstructorWithMinimalParameters(): void
    {
        // Arrange
        $name = 'Login Strategy';
        $businessType = 'login';

        // Act
        $strategy = new VerificationStrategy($name, $businessType);

        // Assert
        $this->assertSame($name, $strategy->getName());
        $this->assertSame($businessType, $strategy->getBusinessType());
        $this->assertSame([], $strategy->getConfig());
        $this->assertNull($strategy->getDescription());
        $this->assertTrue($strategy->isEnabled());
        $this->assertSame(0, $strategy->getPriority());
        $this->assertNull($strategy->getCreateTime());
        $this->assertNull($strategy->getUpdateTime());
        $this->assertInstanceOf(ArrayCollection::class, $strategy->getRules());
        $this->assertInstanceOf(ArrayCollection::class, $strategy->getVerificationRecords());
        $this->assertCount(0, $strategy->getRules());
        $this->assertCount(0, $strategy->getVerificationRecords());
    }

    /**
     * 测试构造函数创建完整策略
     */
    public function testConstructorWithFullParameters(): void
    {
        // Arrange
        $name = 'Payment Strategy';
        $businessType = 'payment';
        $config = [
            'min_confidence' => 0.85,
            'timeout' => 30,
            'retry_attempts' => 3
        ];

        // Act
        $strategy = new VerificationStrategy($name, $businessType, $config);

        // Assert
        $this->assertSame($name, $strategy->getName());
        $this->assertSame($businessType, $strategy->getBusinessType());
        $this->assertSame($config, $strategy->getConfig());
    }

    /**
     * 测试构造函数处理空字符串
     */
    public function testConstructorWithEmptyStrings(): void
    {
        // Arrange & Act
        $strategy = new VerificationStrategy('', '');

        // Assert
        $this->assertSame('', $strategy->getName());
        $this->assertSame('', $strategy->getBusinessType());
    }

    /**
     * 测试__toString()方法无ID时的表现
     */
    public function testToStringWithoutId(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('High Security Strategy', 'transfer');

        // Act
        $result = (string) $strategy;

        // Assert
        $this->assertSame('VerificationStrategy[0]: High Security Strategy (transfer)', $result);
    }

    /**
     * 测试__toString()方法含有ID时的表现（使用反射设置ID）
     */
    public function testToStringWithId(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'test');
        
        // 使用反射设置ID
        $reflection = new \ReflectionClass($strategy);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($strategy, 456);

        // Act
        $result = (string) $strategy;

        // Assert
        $this->assertSame('VerificationStrategy[456]: Test Strategy (test)', $result);
    }

    /**
     * 测试名称设置
     */
    public function testSetName(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Original Name', 'login');

        // Act
        $result = $strategy->setName('Updated Name');

        // Assert
        $this->assertSame($strategy, $result); // 链式调用
        $this->assertSame('Updated Name', $strategy->getName());
    }

    /**
     * 测试业务类型设置
     */
    public function testSetBusinessType(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act
        $result = $strategy->setBusinessType('payment');

        // Assert
        $this->assertSame($strategy, $result);
        $this->assertSame('payment', $strategy->getBusinessType());
    }

    /**
     * 测试描述设置
     */
    public function testSetDescription(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act & Assert - 设置描述
        $result = $strategy->setDescription('This is a test strategy');
        $this->assertSame($strategy, $result);
        $this->assertSame('This is a test strategy', $strategy->getDescription());

        // Act & Assert - 设置为null
        $strategy->setDescription(null);
        $this->assertNull($strategy->getDescription());
    }

    /**
     * 测试启用状态设置
     */
    public function testSetEnabled(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act
        $result = $strategy->setEnabled(false);

        // Assert
        $this->assertSame($strategy, $result);
        $this->assertFalse($strategy->isEnabled());
    }

    /**
     * 测试优先级设置
     */
    public function testSetPriority(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act
        $result = $strategy->setPriority(100);

        // Assert
        $this->assertSame($strategy, $result);
        $this->assertSame(100, $strategy->getPriority());
    }

    /**
     * 测试负数和极值优先级设置
     */
    public function testSetPriorityWithExtremeValues(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act & Assert - 负数
        $strategy->setPriority(-50);
        $this->assertSame(-50, $strategy->getPriority());

        // Act & Assert - 极大值
        $strategy->setPriority(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $strategy->getPriority());

        // Act & Assert - 极小值
        $strategy->setPriority(PHP_INT_MIN);
        $this->assertSame(PHP_INT_MIN, $strategy->getPriority());
    }

    /**
     * 测试配置设置
     */
    public function testSetConfig(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        $newConfig = ['timeout' => 60, 'retries' => 5];

        // Act
        $result = $strategy->setConfig($newConfig);

        // Assert
        $this->assertSame($strategy, $result);
        $this->assertSame($newConfig, $strategy->getConfig());
    }

    /**
     * 测试配置设置为空数组
     */
    public function testSetConfigWithEmptyArray(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login', ['existing' => 'value']);

        // Act
        $strategy->setConfig([]);

        // Assert
        $this->assertSame([], $strategy->getConfig());
    }

    /**
     * 测试获取配置值 - 存在的键
     */
    public function testGetConfigValueExistingKey(): void
    {
        // Arrange
        $config = [
            'timeout' => 30,
            'min_confidence' => 0.8,
            'enabled_features' => ['liveness', 'quality'],
            'complex_config' => [
                'nested' => ['value' => 'test']
            ]
        ];
        $strategy = new VerificationStrategy('Test Strategy', 'login', $config);

        // Act & Assert
        $this->assertSame(30, $strategy->getConfigValue('timeout'));
        $this->assertSame(0.8, $strategy->getConfigValue('min_confidence'));
        $this->assertSame(['liveness', 'quality'], $strategy->getConfigValue('enabled_features'));
        $this->assertSame(['nested' => ['value' => 'test']], $strategy->getConfigValue('complex_config'));
    }

    /**
     * 测试获取配置值 - 不存在的键返回默认值
     */
    public function testGetConfigValueNonExistingKeyWithDefault(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act & Assert
        $this->assertNull($strategy->getConfigValue('non_existing'));
        $this->assertSame('default', $strategy->getConfigValue('missing', 'default'));
        $this->assertSame(42, $strategy->getConfigValue('number', 42));
        $this->assertSame([], $strategy->getConfigValue('array', []));
    }

    /**
     * 测试设置配置值
     */
    public function testSetConfigValue(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act
        $result = $strategy->setConfigValue('new_key', 'new_value');

        // Assert
        $this->assertSame($strategy, $result); // 链式调用
        $this->assertSame('new_value', $strategy->getConfigValue('new_key'));
    }

    /**
     * 测试设置多个配置值
     */
    public function testSetMultipleConfigValues(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act
        $strategy->setConfigValue('timeout', 60)
                 ->setConfigValue('retries', 3)
                 ->setConfigValue('features', ['face', 'voice']);

        // Assert
        $this->assertSame(60, $strategy->getConfigValue('timeout'));
        $this->assertSame(3, $strategy->getConfigValue('retries'));
        $this->assertSame(['face', 'voice'], $strategy->getConfigValue('features'));
    }

    /**
     * 测试设置配置值覆盖现有值
     */
    public function testSetConfigValueOverwriteExisting(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login', ['key' => 'old_value']);

        // Act
        $strategy->setConfigValue('key', 'new_value');

        // Assert
        $this->assertSame('new_value', $strategy->getConfigValue('key'));
    }

    /**
     * 测试添加规则
     */
    public function testAddRule(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $rule */
        $rule = $this->createMock(StrategyRule::class);
        $rule->expects($this->once())->method('setStrategy')->with($strategy);

        // Act
        $result = $strategy->addRule($rule);

        // Assert
        $this->assertSame($strategy, $result); // 链式调用
        $this->assertTrue($strategy->getRules()->contains($rule));
        $this->assertCount(1, $strategy->getRules());
    }

    /**
     * 测试添加重复规则
     */
    public function testAddDuplicateRule(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $rule */
        $rule = $this->createMock(StrategyRule::class);
        $rule->expects($this->once())->method('setStrategy')->with($strategy);

        // Act - 添加同一个规则两次
        $strategy->addRule($rule);
        $strategy->addRule($rule);

        // Assert - 只应该有一个
        $this->assertCount(1, $strategy->getRules());
    }

    /**
     * 测试移除规则
     */
    public function testRemoveRule(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $rule */
        $rule = $this->createMock(StrategyRule::class);
        $rule->method('getStrategy')->willReturn($strategy);
        $rule->expects($this->once())->method('setStrategy')->with(null);
        
        $strategy->getRules()->add($rule); // 直接添加到集合中

        // Act
        $result = $strategy->removeRule($rule);

        // Assert
        $this->assertSame($strategy, $result); // 链式调用
        $this->assertFalse($strategy->getRules()->contains($rule));
        $this->assertCount(0, $strategy->getRules());
    }

    /**
     * 测试移除不存在的规则
     */
    public function testRemoveNonExistentRule(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $rule */
        $rule = $this->createMock(StrategyRule::class);
        $rule->expects($this->never())->method('setStrategy');

        // Act
        $result = $strategy->removeRule($rule);

        // Assert
        $this->assertSame($strategy, $result);
        $this->assertCount(0, $strategy->getRules());
    }

    /**
     * 测试isUsable()方法
     */
    public function testIsUsable(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act & Assert - 默认启用
        $this->assertTrue($strategy->isUsable());

        // Act & Assert - 禁用后
        $strategy->setEnabled(false);
        $this->assertFalse($strategy->isUsable());

        // Act & Assert - 重新启用
        $strategy->setEnabled(true);
        $this->assertTrue($strategy->isUsable());
    }

    /**
     * 测试getEnabledRules()方法
     */
    public function testGetEnabledRules(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $enabledRule1 */
        $enabledRule1 = $this->createMock(StrategyRule::class);
        $enabledRule1->method('isEnabled')->willReturn(true);
        
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $enabledRule2 */
        $enabledRule2 = $this->createMock(StrategyRule::class);
        $enabledRule2->method('isEnabled')->willReturn(true);
        
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $disabledRule */
        $disabledRule = $this->createMock(StrategyRule::class);
        $disabledRule->method('isEnabled')->willReturn(false);

        // 直接添加到集合中
        $strategy->getRules()->add($enabledRule1);
        $strategy->getRules()->add($disabledRule);
        $strategy->getRules()->add($enabledRule2);

        // Act
        $enabledRules = $strategy->getEnabledRules();

        // Assert
        $this->assertCount(2, $enabledRules);
        $this->assertTrue($enabledRules->contains($enabledRule1));
        $this->assertTrue($enabledRules->contains($enabledRule2));
        $this->assertFalse($enabledRules->contains($disabledRule));
    }

    /**
     * 测试getEnabledRules()方法 - 无启用规则
     */
    public function testGetEnabledRulesWhenAllDisabled(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');
        
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $disabledRule */
        $disabledRule = $this->createMock(StrategyRule::class);
        $disabledRule->method('isEnabled')->willReturn(false);
        
        $strategy->getRules()->add($disabledRule);

        // Act
        $enabledRules = $strategy->getEnabledRules();

        // Assert
        $this->assertCount(0, $enabledRules);
    }

    /**
     * 测试复杂的策略配置场景
     */
    public function testComplexStrategyConfiguration(): void
    {
        // Arrange
        $complexConfig = [
            'face_detection' => [
                'min_confidence' => 0.85,
                'quality_threshold' => 0.7,
                'liveness_check' => true
            ],
            'security' => [
                'max_attempts' => 3,
                'lockout_duration' => 300,
                'ip_whitelist' => ['192.168.1.0/24']
            ],
            'performance' => [
                'timeout' => 30,
                'cache_ttl' => 3600
            ]
        ];

        // Act
        $strategy = new VerificationStrategy('High Security Payment Strategy', 'high_value_payment', $complexConfig);
        $strategy->setDescription('Strategy for high-value payments with enhanced security')
                 ->setPriority(100)
                 ->setConfigValue('notifications', ['email', 'sms'])
                 ->setConfigValue('audit_level', 'full');

        // Assert
        $this->assertSame('High Security Payment Strategy', $strategy->getName());
        $this->assertSame('high_value_payment', $strategy->getBusinessType());
        $this->assertSame('Strategy for high-value payments with enhanced security', $strategy->getDescription());
        $this->assertSame(100, $strategy->getPriority());
        $this->assertTrue($strategy->isEnabled());
        $this->assertTrue($strategy->isUsable());
        
        // 测试嵌套配置访问
        $this->assertSame(0.85, $strategy->getConfigValue('face_detection')['min_confidence']);
        $this->assertSame(3, $strategy->getConfigValue('security')['max_attempts']);
        $this->assertSame(['email', 'sms'], $strategy->getConfigValue('notifications'));
        $this->assertSame('full', $strategy->getConfigValue('audit_level'));
    }

    /**
     * 测试边界条件和特殊字符处理
     */
    public function testBoundaryConditionsAndSpecialCharacters(): void
    {
        // Arrange
        $specialName = 'Strategy with 特殊字符 & symbols @#$%';
        $specialBusinessType = 'business/type-with_underscores';
        $specialConfig = [
            'unicode_text' => '这是中文 🎉 émojis',
            'empty_string' => '',
            'null_value' => null,
            'zero_value' => 0,
            'false_value' => false,
            'nested_special' => [
                'key with spaces' => 'value',
                'symbols@#$' => 'test'
            ]
        ];

        // Act
        $strategy = new VerificationStrategy($specialName, $specialBusinessType, $specialConfig);
        $strategy->setDescription('Description with 特殊字符 and "quotes"')
                 ->setConfigValue('special_key@domain.com', 'email_like_key');

        // Assert
        $this->assertSame($specialName, $strategy->getName());
        $this->assertSame($specialBusinessType, $strategy->getBusinessType());
        $this->assertStringContainsString('特殊字符', $strategy->getDescription());
        $this->assertSame('这是中文 🎉 émojis', $strategy->getConfigValue('unicode_text'));
        $this->assertSame('', $strategy->getConfigValue('empty_string'));
        $this->assertNull($strategy->getConfigValue('null_value'));
        $this->assertSame(0, $strategy->getConfigValue('zero_value'));
        $this->assertFalse($strategy->getConfigValue('false_value'));
        $this->assertSame('email_like_key', $strategy->getConfigValue('special_key@domain.com'));
    }

    /**
     * 测试时间戳的初始值
     */
    public function testTimestampInitialValues(): void
    {
        // Arrange
        $strategy = new VerificationStrategy('Test Strategy', 'login');

        // Act & Assert - 新创建的实体时间戳应该为null，直到被持久化
        $this->assertNull($strategy->getCreateTime());
        $this->assertNull($strategy->getUpdateTime());
    }

    /**
     * 测试规则集合和验证记录集合的独立性
     */
    public function testCollectionIndependence(): void
    {
        // Arrange
        $strategy1 = new VerificationStrategy('Strategy 1', 'login');
        $strategy2 = new VerificationStrategy('Strategy 2', 'payment');

        // Act & Assert
        $this->assertNotSame($strategy1->getRules(), $strategy2->getRules());
        $this->assertNotSame($strategy1->getVerificationRecords(), $strategy2->getVerificationRecords());
        
        // 修改一个策略的规则不应影响另一个
        /** @var StrategyRule&\PHPUnit\Framework\MockObject\MockObject $rule */
        $rule = $this->createMock(StrategyRule::class);
        $rule->method('setStrategy');
        
        $strategy1->addRule($rule);
        $this->assertCount(1, $strategy1->getRules());
        $this->assertCount(0, $strategy2->getRules());
    }
} 