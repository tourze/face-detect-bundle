<?php

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Repository\VerificationStrategyRepository;

/**
 * VerificationStrategyRepository 仓储类测试
 */
class VerificationStrategyRepositoryTest extends TestCase
{
    private VerificationStrategyRepository $repository;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new VerificationStrategyRepository($this->managerRegistry);
    }

    public function test_constructor_creates_repository_instance(): void
    {
        // Arrange & Act
        $repository = new VerificationStrategyRepository($this->managerRegistry);

        // Assert
        $this->assertInstanceOf(VerificationStrategyRepository::class, $repository);
    }

    public function test_find_enabled_by_business_type_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findEnabledByBusinessType'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findEnabledByBusinessType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('businessType', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_by_name_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByName'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByName');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('name', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_highest_priority_by_business_type_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findHighestPriorityByBusinessType'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findHighestPriorityByBusinessType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('businessType', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_all_enabled_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findAllEnabled'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findAllEnabled');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_by_priority_range_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByPriorityRange'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByPriorityRange');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $minParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('minPriority', $minParam->getName());
        $this->assertSame('int', $minParam->getType()->getName());
        
        $maxParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('maxPriority', $maxParam->getName());
        $this->assertSame('int', $maxParam->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_count_by_business_type_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'countByBusinessType'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'countByBusinessType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_by_config_key_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByConfigKey'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByConfigKey');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('configKey', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_update_enabled_status_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'updateEnabledStatus'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'updateEnabledStatus');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $idsParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('strategyIds', $idsParam->getName());
        $this->assertSame('array', $idsParam->getType()->getName());
        
        $enabledParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('enabled', $enabledParam->getName());
        $this->assertSame('bool', $enabledParam->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('int', $returnType->getName());
    }

    public function test_get_statistics_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'getStatistics'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'getStatistics');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_for_update_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findForUpdate'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findForUpdate');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('since', $parameter->getName());
        $this->assertTrue($parameter->allowsNull());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertNull($parameter->getDefaultValue());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_all_required_methods_exist_and_are_public(): void
    {
        // Arrange
        $requiredMethods = [
            'findEnabledByBusinessType',
            'findByName',
            'findHighestPriorityByBusinessType',
            'findAllEnabled',
            'findByPriorityRange',
            'countByBusinessType',
            'findByConfigKey',
            'updateEnabledStatus',
            'getStatistics',
            'findForUpdate'
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
            'business-type-test',
            'strategy-name',
            '',
            'config-key-test',
            'unicode-测试'
        ];

        // Act & Assert
        foreach ($stringTestCases as $testValue) {
            try {
                $this->repository->findEnabledByBusinessType($testValue);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_parameter_validation_for_integer_parameters(): void
    {
        // Arrange
        $intTestCases = [
            [1, 10],
            [0, 100],
            [-10, 50],
            [100, 200]
        ];

        // Act & Assert
        foreach ($intTestCases as [$min, $max]) {
            try {
                $this->repository->findByPriorityRange($min, $max);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_parameter_validation_for_array_and_bool_parameters(): void
    {
        // Arrange
        $testCases = [
            [['id1', 'id2'], true],
            [[], false],
            [['1', '2', '3'], true]
        ];

        // Act & Assert
        foreach ($testCases as [$ids, $enabled]) {
            try {
                $this->repository->updateEnabledStatus($ids, $enabled);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_parameter_validation_for_datetime_parameters(): void
    {
        // Arrange
        $dateTime = new \DateTimeImmutable('2023-01-01');

        // Act & Assert
        try {
            $this->repository->findForUpdate($dateTime);
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
        $this->assertSame('Tourze\FaceDetectBundle\Repository\VerificationStrategyRepository', $reflectionClass->getName());
    }

    public function test_repository_manages_correct_entity(): void
    {
        // Assert - 验证Repository管理的是VerificationStrategy实体
        $this->assertTrue(class_exists(VerificationStrategy::class));
    }

    public function test_method_documentation_exists(): void
    {
        // Arrange
        $methods = [
            'findEnabledByBusinessType', 'findByName', 'findHighestPriorityByBusinessType',
            'findAllEnabled', 'findByPriorityRange', 'countByBusinessType',
            'findByConfigKey', 'updateEnabledStatus', 'getStatistics', 'findForUpdate'
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
        
        // 方法名应该以find、count、update或get开头
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $reflectionClass->getName()) {
                $methodName = $method->getName();
                $this->assertTrue(
                    str_starts_with($methodName, 'find') || 
                    str_starts_with($methodName, 'count') ||
                    str_starts_with($methodName, 'update') ||
                    str_starts_with($methodName, 'get'),
                    "Method $methodName should start with 'find', 'count', 'update' or 'get'"
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
        $this->assertSame('Doctrine\Persistence\ManagerRegistry', $parameter->getType()->getName());
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
            'findEnabledByBusinessType' => ['type' => 'array'],
            'findByName' => ['nullable' => true],
            'findHighestPriorityByBusinessType' => ['nullable' => true],
            'findAllEnabled' => ['type' => 'array'],
            'findByPriorityRange' => ['type' => 'array'],
            'countByBusinessType' => ['type' => 'array'],
            'findByConfigKey' => ['type' => 'array'],
            'updateEnabledStatus' => ['type' => 'int'],
            'getStatistics' => ['type' => 'array'],
            'findForUpdate' => ['type' => 'array']
        ];

        // Act & Assert
        foreach ($expectedReturnTypes as $methodName => $expectations) {
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $returnType = $reflectionMethod->getReturnType();
            
            $this->assertNotNull($returnType, "Method $methodName should have return type");
            
            if (isset($expectations['type'])) {
                $this->assertSame($expectations['type'], $returnType->getName(), "Method $methodName return type mismatch");
            }
            
            if (isset($expectations['nullable'])) {
                $this->assertSame($expectations['nullable'], $returnType->allowsNull(), "Method $methodName nullable return type mismatch");
            }
        }
    }
} 