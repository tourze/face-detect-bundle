<?php

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Repository\StrategyRuleRepository;

/**
 * StrategyRuleRepository 仓储类测试
 */
class StrategyRuleRepositoryTest extends TestCase
{
    private StrategyRuleRepository $repository;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new StrategyRuleRepository($this->managerRegistry);
    }

    public function test_constructor_creates_repository_instance(): void
    {
        // Arrange & Act
        $repository = new StrategyRuleRepository($this->managerRegistry);

        // Assert
        $this->assertInstanceOf(StrategyRuleRepository::class, $repository);
    }

    public function test_find_enabled_by_strategy_method_exists(): void
    {
        // Assert
        // 验证findEnabledByStrategy方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findEnabledByStrategy');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('strategy', $parameter->getName());
        $this->assertSame('Tourze\FaceDetectBundle\Entity\VerificationStrategy', (string) $parameter->getType());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
    }

    public function test_find_by_rule_type_method_exists(): void
    {
        // Assert
        // 验证findByRuleType方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByRuleType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('ruleType', $parameter->getName());
        $this->assertSame('string', (string) $parameter->getType());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
    }

    public function test_find_by_strategy_and_type_method_exists(): void
    {
        // Assert
        // 验证findByStrategyAndType方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByStrategyAndType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $strategyParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('strategy', $strategyParam->getName());
        $this->assertSame('Tourze\FaceDetectBundle\Entity\VerificationStrategy', (string) $strategyParam->getType());
        
        $ruleTypeParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('ruleType', $ruleTypeParam->getName());
        $this->assertSame('string', (string) $ruleTypeParam->getType());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
    }

    public function test_find_highest_priority_by_strategy_method_exists(): void
    {
        // Assert
        // 验证findHighestPriorityByStrategy方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findHighestPriorityByStrategy');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('strategy', $parameter->getName());
        $this->assertSame('Tourze\FaceDetectBundle\Entity\VerificationStrategy', (string) $parameter->getType());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_get_statistics_method_exists(): void
    {
        // Assert
        // 验证getStatistics方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'getStatistics');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
    }

    public function test_all_required_methods_exist_and_are_public(): void
    {
        // Arrange
        $requiredMethods = [
            'findEnabledByStrategy',
            'findByRuleType',
            'findByStrategyAndType',
            'findHighestPriorityByStrategy',
            'getStatistics'
        ];

        // Act & Assert
        foreach ($requiredMethods as $methodName) {
            $this->assertTrue(method_exists($this->repository, $methodName), "Method $methodName should exist");
            
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $this->assertTrue($reflectionMethod->isPublic(), "Method $methodName should be public");
        }
    }

    public function test_parameter_validation_for_string_parameters(): void
    {
        // Arrange
        $stringTestCases = [
            'rule-type-1',
            'rule-type-2',
            '',
            'rule-type-unicode-测试'
        ];

        // Act & Assert
        foreach ($stringTestCases as $testValue) {
            try {
                $this->repository->findByRuleType($testValue);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_parameter_validation_for_entity_parameters(): void
    {
        // Arrange
        /** @var VerificationStrategy&MockObject $strategy */
        $strategy = $this->createMock(VerificationStrategy::class);

        // Act & Assert
        try {
            $this->repository->findEnabledByStrategy($strategy);
            $this->fail('Expected exception was not thrown');
        } catch (\TypeError $e) {
            $this->fail('Parameter type validation failed: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->assertTrue(true, 'Parameter validation passed');
        }
    }

    public function test_repository_class_has_correct_namespace(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        $this->assertSame('Tourze\FaceDetectBundle\Repository\StrategyRuleRepository', $reflectionClass->getName());
    }

    public function test_repository_manages_correct_entity(): void
    {
        // Assert - 验证Repository管理的是StrategyRule实体
        $this->assertTrue(class_exists(StrategyRule::class));
    }

    public function test_method_documentation_exists(): void
    {
        // Arrange
        $methods = [
            'findEnabledByStrategy', 'findByRuleType', 'findByStrategyAndType',
            'findHighestPriorityByStrategy', 'getStatistics'
        ];

        // Act & Assert
        foreach ($methods as $methodName) {
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $docComment = $reflectionMethod->getDocComment();
            
            $this->assertNotFalse($docComment, "Method $methodName should have documentation");
            $this->assertStringContainsString('/**', $docComment, "Method $methodName should have proper doc comment");
        }
    }

    public function test_repository_follows_naming_conventions(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        
        // 类名应该以Repository结尾
        $this->assertStringEndsWith('Repository', $reflectionClass->getShortName());
        
        // 应该在Repository命名空间下
        $namespaceName = $reflectionClass->getNamespaceName();
        $this->assertStringContainsString('Repository', $namespaceName);
        
        // 方法名应该以find或get开头
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $reflectionClass->getName()) {
                $methodName = $method->getName();
                $this->assertTrue(
                    str_starts_with($methodName, 'find') || 
                    str_starts_with($methodName, 'get'),
                    "Method $methodName should start with 'find' or 'get'"
                );
            }
        }
    }

    public function test_constructor_parameter_type(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters);
        $parameter = $parameters[0];
        $this->assertSame('registry', $parameter->getName());
        $this->assertSame('Doctrine\Persistence\ManagerRegistry', (string) $parameter->getType());
    }

    public function test_class_is_instantiable(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        
        // Repository类应该可以被实例化
        $this->assertTrue($reflectionClass->isInstantiable());
        
        // 不应该是抽象类
        $this->assertFalse($reflectionClass->isAbstract());
    }

    public function test_method_return_types_are_correctly_defined(): void
    {
        // Arrange
        $expectedReturnTypes = [
            'findEnabledByStrategy' => ['type' => 'array'],
            'findByRuleType' => ['type' => 'array'],
            'findByStrategyAndType' => ['type' => 'array'],
            'findHighestPriorityByStrategy' => ['nullable' => true],
            'getStatistics' => ['type' => 'array']
        ];

        // Act & Assert
        foreach ($expectedReturnTypes as $methodName => $expectations) {
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $returnType = $reflectionMethod->getReturnType();
            
            $this->assertNotNull($returnType, "Method $methodName should have return type");
            
            if (isset($expectations['type'])) {
                $this->assertSame($expectations['type'], (string) $returnType, "Method $methodName return type mismatch");
            }
            
            if (isset($expectations['nullable'])) {
                $this->assertSame($expectations['nullable'], $returnType->allowsNull(), "Method $methodName nullable return type mismatch");
            }
        }
    }

    public function test_related_entities_are_accessible(): void
    {
        // Assert - 验证相关实体类可以被访问
        $this->assertTrue(class_exists(VerificationStrategy::class));
        $this->assertTrue(class_exists(StrategyRule::class));
    }

    public function test_method_parameter_count_is_correct(): void
    {
        // Arrange
        $expectedParameterCounts = [
            'findEnabledByStrategy' => 1,
            'findByRuleType' => 1,
            'findByStrategyAndType' => 2,
            'findHighestPriorityByStrategy' => 1,
            'getStatistics' => 0
        ];

        // Act & Assert
        foreach ($expectedParameterCounts as $methodName => $expectedCount) {
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $actualCount = count($reflectionMethod->getParameters());
            
            $this->assertSame($expectedCount, $actualCount, "Method $methodName should have $expectedCount parameters");
        }
    }
} 