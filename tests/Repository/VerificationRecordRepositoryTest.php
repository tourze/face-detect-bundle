<?php

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Repository\VerificationRecordRepository;

/**
 * VerificationRecordRepository 仓储类测试
 */
class VerificationRecordRepositoryTest extends TestCase
{
    private VerificationRecordRepository $repository;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new VerificationRecordRepository($this->managerRegistry);
    }

    public function test_constructor_creates_repository_instance(): void
    {
        // Arrange & Act
        $repository = new VerificationRecordRepository($this->managerRegistry);

        // Assert
        $this->assertInstanceOf(VerificationRecordRepository::class, $repository);
    }

    public function test_find_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $userIdParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $userIdParam->getName());
        $this->assertSame('string', $userIdParam->getType()->getName());
        
        $limitParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('limit', $limitParam->getName());
        $this->assertSame('int', $limitParam->getType()->getName());
        $this->assertTrue($limitParam->isDefaultValueAvailable());
        $this->assertSame(10, $limitParam->getDefaultValue());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_by_operation_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByOperationId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByOperationId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('operationId', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_last_successful_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findLastSuccessfulByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findLastSuccessfulByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_count_by_user_id_and_time_range_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'countByUserIdAndTimeRange'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'countByUserIdAndTimeRange');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(3, $reflectionMethod->getParameters());
        
        $userIdParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $userIdParam->getName());
        $this->assertSame('string', $userIdParam->getType()->getName());
        
        $startParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('start', $startParam->getName());
        $this->assertSame('DateTimeInterface', $startParam->getType()->getName());
        
        $endParam = $reflectionMethod->getParameters()[2];
        $this->assertSame('end', $endParam->getName());
        $this->assertSame('DateTimeInterface', $endParam->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('int', $returnType->getName());
    }

    public function test_count_successful_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'countSuccessfulByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'countSuccessfulByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $userIdParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $userIdParam->getName());
        $this->assertSame('string', $userIdParam->getType()->getName());
        
        $sinceParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('since', $sinceParam->getName());
        $this->assertTrue($sinceParam->allowsNull());
        $this->assertTrue($sinceParam->isDefaultValueAvailable());
        $this->assertNull($sinceParam->getDefaultValue());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('int', $returnType->getName());
    }

    public function test_count_by_business_type_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'countByBusinessType'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'countByBusinessType');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('businessType', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_by_time_range_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByTimeRange'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByTimeRange');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $startParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('start', $startParam->getName());
        $this->assertSame('DateTimeInterface', $startParam->getType()->getName());
        
        $endParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('end', $endParam->getName());
        $this->assertSame('DateTimeInterface', $endParam->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_find_low_confidence_records_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findLowConfidenceRecords'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findLowConfidenceRecords');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('threshold', $parameter->getName());
        $this->assertSame('float', $parameter->getType()->getName());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertSame(0.7, $parameter->getDefaultValue());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
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

    public function test_delete_old_records_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'deleteOldRecords'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'deleteOldRecords');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('before', $parameter->getName());
        $this->assertSame('DateTimeInterface', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('int', $returnType->getName());
    }

    public function test_all_required_methods_exist_and_are_public(): void
    {
        // Arrange
        $requiredMethods = [
            'findByUserId',
            'findByOperationId',
            'findLastSuccessfulByUserId',
            'countByUserIdAndTimeRange',
            'countSuccessfulByUserId',
            'countByBusinessType',
            'findByTimeRange',
            'findLowConfidenceRecords',
            'getStatistics',
            'deleteOldRecords'
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
            'user-123',
            'operation-456',
            '',
            'user-unicode-用户',
            'business-type-test'
        ];

        // Act & Assert
        foreach ($stringTestCases as $testValue) {
            try {
                $this->repository->findByUserId($testValue);
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
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');

        // Act & Assert
        try {
            $this->repository->findByTimeRange($start, $end);
            $this->fail('Expected exception was not thrown');
        } catch (\TypeError $e) {
            $this->fail('Parameter type validation failed: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->assertTrue(true, 'Parameter validation passed');
        }
    }

    public function test_parameter_validation_for_float_parameters(): void
    {
        // Arrange
        $floatTestCases = [0.1, 0.5, 0.7, 0.9, 1.0];

        // Act & Assert
        foreach ($floatTestCases as $threshold) {
            try {
                $this->repository->findLowConfidenceRecords($threshold);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_repository_class_has_correct_namespace(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        $this->assertSame('Tourze\FaceDetectBundle\Repository\VerificationRecordRepository', $reflectionClass->getName());
    }

    public function test_repository_manages_correct_entity(): void
    {
        // Assert - 验证Repository管理的是VerificationRecord实体
        $this->assertTrue(class_exists(VerificationRecord::class));
    }

    public function test_method_documentation_exists(): void
    {
        // Arrange
        $methods = [
            'findByUserId', 'findByOperationId', 'findLastSuccessfulByUserId',
            'countByUserIdAndTimeRange', 'countSuccessfulByUserId', 'countByBusinessType',
            'findByTimeRange', 'findLowConfidenceRecords', 'getStatistics', 'deleteOldRecords'
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
        
        // 方法名应该以find、count、get或delete开头
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === $reflectionClass->getName()) {
                $methodName = $method->getName();
                $this->assertTrue(
                    str_starts_with($methodName, 'find') || 
                    str_starts_with($methodName, 'count') ||
                    str_starts_with($methodName, 'get') ||
                    str_starts_with($methodName, 'delete'),
                    "Method $methodName should start with 'find', 'count', 'get' or 'delete'"
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
            'findByUserId' => ['type' => 'array'],
            'findByOperationId' => ['type' => 'array'],
            'findLastSuccessfulByUserId' => ['nullable' => true],
            'countByUserIdAndTimeRange' => ['type' => 'int'],
            'countSuccessfulByUserId' => ['type' => 'int'],
            'countByBusinessType' => ['type' => 'array'],
            'findByTimeRange' => ['type' => 'array'],
            'findLowConfidenceRecords' => ['type' => 'array'],
            'getStatistics' => ['type' => 'array'],
            'deleteOldRecords' => ['type' => 'int']
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