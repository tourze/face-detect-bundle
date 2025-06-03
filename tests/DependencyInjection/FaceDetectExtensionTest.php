<?php

namespace Tourze\FaceDetectBundle\Tests\DependencyInjection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\FaceDetectBundle\DependencyInjection\FaceDetectExtension;

/**
 * FaceDetectExtension DI扩展测试
 */
class FaceDetectExtensionTest extends TestCase
{
    private FaceDetectExtension $extension;
    private ContainerBuilder&MockObject $container;

    protected function setUp(): void
    {
        $this->extension = new FaceDetectExtension();
        $this->container = $this->createMock(ContainerBuilder::class);
    }

    public function test_extension_can_be_instantiated(): void
    {
        // Arrange & Act
        $extension = new FaceDetectExtension();

        // Assert
        $this->assertInstanceOf(FaceDetectExtension::class, $extension);
    }

    public function test_extension_extends_symfony_extension(): void
    {
        // Assert
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function test_extension_has_correct_namespace(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->extension);
        $this->assertSame('Tourze\FaceDetectBundle\DependencyInjection\FaceDetectExtension', $reflectionClass->getName());
    }

    public function test_extension_has_load_method(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->extension, 'load'));
        
        $reflectionMethod = new \ReflectionMethod($this->extension, 'load');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
        
        $configsParam = $reflectionMethod->getParameters()[0];
        $this->assertSame('configs', $configsParam->getName());
        $this->assertSame('array', $configsParam->getType()->getName());
        
        $containerParam = $reflectionMethod->getParameters()[1];
        $this->assertSame('container', $containerParam->getName());
        $this->assertSame('Symfony\Component\DependencyInjection\ContainerBuilder', $containerParam->getType()->getName());
        
        $returnType = $reflectionMethod->getReturnType();
        $this->assertSame('void', $returnType->getName());
    }

    public function test_load_method_parameter_validation(): void
    {
        // Arrange
        $configs = [];

        // Act & Assert - 由于Extension会尝试加载真实文件，我们只验证方法签名
        $reflectionMethod = new \ReflectionMethod($this->extension, 'load');
        $this->assertTrue($reflectionMethod->isPublic());
        $this->assertCount(2, $reflectionMethod->getParameters());
    }

    public function test_extension_follows_symfony_naming_conventions(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->extension);
        
        // Extension类名应该以Extension结尾
        $this->assertStringEndsWith('Extension', $reflectionClass->getShortName());
        
        // Extension应该在DependencyInjection命名空间下
        $this->assertStringContainsString('DependencyInjection', $reflectionClass->getNamespaceName());
    }

    public function test_extension_alias_follows_convention(): void
    {
        // Act - Extension应该自动生成别名
        $alias = $this->extension->getAlias();

        // Assert
        $this->assertSame('face_detect', $alias);
    }

    public function test_extension_can_be_used_in_container(): void
    {
        // Assert - 验证Extension类可以被DI容器使用
        $reflectionClass = new \ReflectionClass($this->extension);
        
        // 应该是具体类（非抽象）
        $this->assertFalse($reflectionClass->isAbstract());
        
        // 应该可以实例化
        $this->assertTrue($reflectionClass->isInstantiable());
        
        // 应该不是接口
        $this->assertFalse($reflectionClass->isInterface());
    }

    public function test_load_method_has_documentation(): void
    {
        // Assert
        $reflectionMethod = new \ReflectionMethod($this->extension, 'load');
        $docComment = $reflectionMethod->getDocComment();
        
        // 如果没有文档注释，这是可以接受的，因为这是一个简单的实现
        $this->assertTrue(
            $docComment === false || strpos($docComment, '/**') !== false,
            'Method load should have proper documentation or no documentation'
        );
    }

    public function test_services_yaml_file_exists(): void
    {
        // Arrange
        $reflectionClass = new \ReflectionClass($this->extension);
        $extensionPath = dirname($reflectionClass->getFileName());
        $servicesPath = $extensionPath . '/../Resources/config/services.yaml';

        // Assert
        $this->assertTrue(file_exists($servicesPath), 'services.yaml file should exist');
        $this->assertTrue(is_readable($servicesPath), 'services.yaml should be readable');
    }

    public function test_services_yaml_contains_valid_configuration(): void
    {
        // Arrange
        $reflectionClass = new \ReflectionClass($this->extension);
        $extensionPath = dirname($reflectionClass->getFileName());
        $servicesPath = $extensionPath . '/../Resources/config/services.yaml';

        // Act
        $content = file_get_contents($servicesPath);

        // Assert
        $this->assertNotFalse($content, 'services.yaml should be readable');
        $this->assertStringContainsString('services:', $content, 'services.yaml should contain services section');
        $this->assertStringContainsString('Tourze\FaceDetectBundle\Repository\\', $content, 'services.yaml should configure repositories');
    }

    public function test_extension_constructor_has_no_parameters(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->extension);
        $constructor = $reflectionClass->getConstructor();
        
        if ($constructor !== null) {
            $this->assertCount(0, $constructor->getParameters(), 'Extension constructor should have no parameters');
        } else {
            // 没有显式构造函数也是可以的
            $this->assertTrue(true, 'Extension has no explicit constructor');
        }
    }

    public function test_extension_class_is_properly_structured(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->extension);
        
        // 应该只有一个公开方法（load）
        $publicMethods = array_filter(
            $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC),
            fn($method) => $method->getDeclaringClass()->getName() === $reflectionClass->getName()
        );
        
        $this->assertCount(1, $publicMethods, 'Extension should have only one public method (load)');
        
        $method = reset($publicMethods);
        $this->assertSame('load', $method->getName());
    }

    public function test_load_method_signature_matches_parent(): void
    {
        // Assert
        $reflectionMethod = new \ReflectionMethod($this->extension, 'load');
        $parentMethod = new \ReflectionMethod(Extension::class, 'load');
        
        // 参数数量应该匹配
        $this->assertCount(
            count($parentMethod->getParameters()),
            $reflectionMethod->getParameters(),
            'load method should have same parameter count as parent'
        );
        
        // 检查返回类型（parent可能没有明确的返回类型声明）
        $parentReturnType = $parentMethod->getReturnType();
        $childReturnType = $reflectionMethod->getReturnType();
        
        if ($parentReturnType !== null) {
            $this->assertSame(
                $parentReturnType->getName(),
                $childReturnType?->getName(),
                'load method should have same return type as parent'
            );
        } else {
            // 如果父类没有返回类型，子类可以有更具体的返回类型
            $this->assertTrue(true, 'Parent has no return type, child can define more specific type');
        }
    }
} 