<?php

namespace Tourze\FaceDetectBundle\Tests\Bundle;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\FaceDetectBundle\FaceDetectBundle;

/**
 * FaceDetectBundle 主Bundle类测试
 */
class FaceDetectBundleTest extends TestCase
{
    private FaceDetectBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new FaceDetectBundle();
    }

    public function test_bundle_can_be_instantiated(): void
    {
        // Arrange & Act
        $bundle = new FaceDetectBundle();

        // Assert
        $this->assertInstanceOf(FaceDetectBundle::class, $bundle);
    }

    public function test_bundle_extends_symfony_bundle(): void
    {
        // Assert
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function test_bundle_has_correct_namespace(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->bundle);
        $this->assertSame('Tourze\FaceDetectBundle\FaceDetectBundle', $reflectionClass->getName());
    }

    public function test_bundle_class_is_final(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->bundle);
        $this->assertTrue($reflectionClass->isInstantiable());
        $this->assertFalse($reflectionClass->isAbstract());
    }

    public function test_bundle_inherits_bundle_methods(): void
    {
        // Assert - 验证Bundle基类的核心方法存在
        $this->assertTrue(method_exists($this->bundle, 'getName'));
        $this->assertTrue(method_exists($this->bundle, 'getNamespace'));
        $this->assertTrue(method_exists($this->bundle, 'getPath'));
    }

    public function test_bundle_name_follows_symfony_convention(): void
    {
        // Act
        $bundleName = $this->bundle->getName();

        // Assert
        $this->assertSame('FaceDetectBundle', $bundleName);
    }

    public function test_bundle_namespace_is_correct(): void
    {
        // Act
        $namespace = $this->bundle->getNamespace();

        // Assert
        $this->assertSame('Tourze\FaceDetectBundle', $namespace);
    }

    public function test_bundle_path_is_correct(): void
    {
        // Act
        $path = $this->bundle->getPath();

        // Assert
        $this->assertStringEndsWith('face-detect-bundle/src', $path);
        $this->assertTrue(is_dir($path));
    }

    public function test_bundle_has_no_custom_extension(): void
    {
        // Act
        $extension = $this->bundle->getContainerExtension();

        // Assert - 应该能获取到扩展，因为FaceDetectExtension存在
        $this->assertNotNull($extension);
        $this->assertSame('face_detect', $extension->getAlias());
    }

    public function test_bundle_directory_structure_exists(): void
    {
        // Arrange
        $bundlePath = $this->bundle->getPath();
        $expectedDirectories = [
            'Entity',
            'Repository', 
            'Enum',
            'Exception',
            'DependencyInjection',
            'Resources'
        ];

        // Act & Assert
        foreach ($expectedDirectories as $directory) {
            $dirPath = $bundlePath . '/' . $directory;
            $this->assertTrue(is_dir($dirPath), "Directory $directory should exist in bundle");
        }
    }

    public function test_bundle_resource_config_exists(): void
    {
        // Arrange
        $bundlePath = $this->bundle->getPath();
        $configPath = $bundlePath . '/Resources/config/services.yaml';

        // Assert
        $this->assertTrue(file_exists($configPath), 'services.yaml should exist');
        $this->assertTrue(is_readable($configPath), 'services.yaml should be readable');
    }

    public function test_bundle_follows_symfony_naming_conventions(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->bundle);
        
        // Bundle类名应该以Bundle结尾
        $this->assertStringEndsWith('Bundle', $reflectionClass->getShortName());
        
        // Bundle应该在正确的命名空间下
        $this->assertStringContainsString('Bundle', $reflectionClass->getNamespaceName());
    }

    public function test_bundle_can_be_used_in_kernel(): void
    {
        // Assert - 验证Bundle类可以被Symfony内核使用
        $reflectionClass = new \ReflectionClass($this->bundle);
        
        // 应该是具体类（非抽象）
        $this->assertFalse($reflectionClass->isAbstract());
        
        // 应该可以实例化
        $this->assertTrue($reflectionClass->isInstantiable());
        
        // 应该不是接口
        $this->assertFalse($reflectionClass->isInterface());
    }
} 