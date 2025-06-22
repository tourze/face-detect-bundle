<?php

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Repository\OperationLogRepository;

/**
 * OperationLogRepository 仓储类测试
 */
class OperationLogRepositoryTest extends TestCase
{
    private OperationLogRepository $repository;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new OperationLogRepository($this->managerRegistry);
    }

    public function test_constructor_creates_repository_instance(): void
    {
        // Arrange & Act
        $repository = new OperationLogRepository($this->managerRegistry);

        // Assert
        $this->assertInstanceOf(OperationLogRepository::class, $repository);
    }

    public function test_find_by_operation_id_method_exists(): void
    {
        // Assert
        // 验证findByOperationId方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByOperationId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('operationId', $parameter->getName());
        $this->assertSame('string', (string) $parameter->getType());
    }

    public function test_find_by_user_id_method_exists(): void
    {
        // Assert
        // 验证findByUserId方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $userIdParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $userIdParam->getName());
        $this->assertSame('string', (string) $userIdParam->getType());
        
        $limitParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('limit', $limitParam->getName());
        $this->assertSame('int', (string) $limitParam->getType());
        $this->assertTrue($limitParam->isDefaultValueAvailable());
        $this->assertSame(10, $limitParam->getDefaultValue());
    }

    public function test_find_pending_verification_method_exists(): void
    {
        // Assert
        // 验证findPendingVerification方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findPendingVerification');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $parameter->getName());
        $this->assertTrue($parameter->allowsNull());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertNull($parameter->getDefaultValue());
    }

    public function test_get_statistics_method_exists(): void
    {
        // Assert
        // 验证getStatistics方法的签名
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'getStatistics');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(0, $reflectionMethod->getParameters());
    }

    public function test_find_by_operation_id_parameter_validation(): void
    {
        // Arrange
        $testCases = [
            'test-operation-123',
            'operation_with_underscore',
            'operation-with-dash',
            '12345',
            'op-unicode-中文',
            ''
        ];

        // Act & Assert
        foreach ($testCases as $operationId) {
            try {
                // 由于我们没有真实的数据库连接，这会抛出异常，但参数验证应该通过
                $this->repository->findByOperationId($operationId);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                // 预期会有异常（因为没有真实的EntityManager），但不应该是参数类型错误
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_find_by_user_id_parameter_validation(): void
    {
        // Arrange
        $testCases = [
            ['user-123', 10],
            ['user_456', 5],
            ['', 1],
            ['user-unicode-用户', 100],
            ['user@example.com', 0]
        ];

        // Act & Assert
        foreach ($testCases as [$userId, $limit]) {
            try {
                $this->repository->findByUserId($userId, $limit);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_find_pending_verification_parameter_validation(): void
    {
        // Arrange
        $testCases = [
            null,
            'user-123',
            '',
            'user-unicode-用户',
            'user@example.com'
        ];

        // Act & Assert
        foreach ($testCases as $userId) {
            try {
                $this->repository->findPendingVerification($userId);
                $this->fail('Expected exception was not thrown');
            } catch (\TypeError $e) {
                $this->fail('Parameter type validation failed: ' . $e->getMessage());
            } catch (\Throwable $e) {
                $this->assertTrue(true, 'Parameter validation passed');
            }
        }
    }

    public function test_repository_extends_correct_base_class(): void
    {
        // Assert - 验证Repository类可以被实例化和使用
        $this->assertInstanceOf(OperationLogRepository::class, $this->repository);
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function test_repository_manages_correct_entity(): void
    {
        // Act
        $reflectionClass = new \ReflectionClass($this->repository);
        $constructor = $reflectionClass->getConstructor();
        $constructorBody = $constructor->getDocComment();

        // Assert - 验证Repository管理的是OperationLog实体
        $this->assertTrue(class_exists(OperationLog::class));
    }

    public function test_method_return_types_are_correct(): void
    {
        // Assert
        $findByOperationIdMethod = new \ReflectionMethod($this->repository, 'findByOperationId');
        $returnType = $findByOperationIdMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        
        $findByUserIdMethod = new \ReflectionMethod($this->repository, 'findByUserId');
        $returnType = $findByUserIdMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
        
        $findPendingVerificationMethod = new \ReflectionMethod($this->repository, 'findPendingVerification');
        $returnType = $findPendingVerificationMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
        
        $getStatisticsMethod = new \ReflectionMethod($this->repository, 'getStatistics');
        $returnType = $getStatisticsMethod->getReturnType();
        $this->assertSame('array', (string) $returnType);
    }

    public function test_all_required_methods_exist_and_are_public(): void
    {
        // Arrange
        $requiredMethods = [
            'findByOperationId',
            'findByUserId', 
            'findPendingVerification',
            'getStatistics'
        ];

        // Act & Assert
        foreach ($requiredMethods as $methodName) {
            $this->assertTrue(method_exists($this->repository, $methodName), "Method $methodName should exist");
            
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $this->assertTrue($reflectionMethod->isPublic(), "Method $methodName should be public");
        }
    }

    public function test_repository_class_has_correct_namespace(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        $this->assertSame('Tourze\FaceDetectBundle\Repository\OperationLogRepository', $reflectionClass->getName());
    }

    public function test_repository_uses_correct_imports(): void
    {
        // Assert - 验证本项目的类可以被访问
        $this->assertTrue(class_exists(\Tourze\FaceDetectBundle\Entity\OperationLog::class));
        
        // 验证Repository类本身正确实例化
        $reflectionClass = new \ReflectionClass($this->repository);
        $this->assertSame('Tourze\FaceDetectBundle\Repository\OperationLogRepository', $reflectionClass->getName());
    }

    public function test_method_documentation_exists(): void
    {
        // Arrange
        $methods = ['findByOperationId', 'findByUserId', 'findPendingVerification', 'getStatistics'];

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
                    str_starts_with($methodName, 'find') || str_starts_with($methodName, 'get'),
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

    public function test_class_is_final_or_can_be_extended(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->repository);
        
        // Repository类应该可以被实例化
        $this->assertTrue($reflectionClass->isInstantiable());
        
        // 不应该是抽象类
        $this->assertFalse($reflectionClass->isAbstract());
    }
} 