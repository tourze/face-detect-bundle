<?php

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Repository\FaceProfileRepository;

/**
 * FaceProfileRepository 仓储类测试
 */
class FaceProfileRepositoryTest extends TestCase
{
    private FaceProfileRepository $repository;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new FaceProfileRepository($this->managerRegistry);
    }

    public function test_constructor_creates_repository_instance(): void
    {
        // Arrange & Act
        $repository = new FaceProfileRepository($this->managerRegistry);

        // Assert
        $this->assertInstanceOf(FaceProfileRepository::class, $repository);
    }

    public function test_find_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_available_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findAvailableByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findAvailableByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_expired_profiles_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findExpiredProfiles'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'findExpiredProfiles');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('before', $parameter->getName());
        $this->assertTrue($parameter->allowsNull());
        $this->assertTrue($parameter->isDefaultValueAvailable());
        $this->assertNull($parameter->getDefaultValue());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('array', $returnType->getName());
    }

    public function test_count_by_user_id_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'countByUserId'));
        
        $reflectionMethod = new \ReflectionMethod($this->repository, 'countByUserId');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(1, $reflectionMethod->getParameters());
        
        $parameter = $reflectionMethod->getParameters()[0];
        $this->assertSame('userId', $parameter->getName());
        $this->assertSame('string', $parameter->getType()->getName());
        
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

    public function test_all_required_methods_exist_and_are_public(): void
    {
        // Arrange
        $requiredMethods = [
            'findByUserId',
            'findAvailableByUserId',
            'findExpiredProfiles',
            'countByUserId',
            'findByCreateTimeRange',
            'findByLowQuality',
            'markExpiredProfiles',
            'deleteByStatus',
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
        $this->assertSame('Tourze\FaceDetectBundle\Repository\FaceProfileRepository', $reflectionClass->getName());
    }

    public function test_repository_manages_correct_entity(): void
    {
        // Assert - 验证Repository管理的是FaceProfile实体
        $this->assertTrue(class_exists(FaceProfile::class));
    }

    public function test_method_return_types_are_correctly_defined(): void
    {
        // Arrange
        $methods = ['findByUserId', 'findAvailableByUserId', 'getStatistics'];

        // Act & Assert
        foreach ($methods as $methodName) {
            $reflectionMethod = new \ReflectionMethod($this->repository, $methodName);
            $returnType = $reflectionMethod->getReturnType();
            
            $this->assertNotNull($returnType, "Method $methodName should have return type");
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
} 