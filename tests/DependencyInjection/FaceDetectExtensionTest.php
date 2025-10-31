<?php

namespace Tourze\FaceDetectBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\FaceDetectBundle\DependencyInjection\FaceDetectExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * FaceDetectExtension DI扩展测试
 *
 * @internal
 */
#[CoversClass(FaceDetectExtension::class)]
final class FaceDetectExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testExtensionExtendsSymfonyExtension(): void
    {
        // 验证Extension继承了Symfony的Extension基类 - 通过反射
        $reflectionClass = new \ReflectionClass(FaceDetectExtension::class);
        $this->assertTrue($reflectionClass->isSubclassOf(Extension::class));
    }

    public function testExtensionHasCorrectNamespace(): void
    {
        // 验证Extension命名空间
        $reflectionClass = new \ReflectionClass(FaceDetectExtension::class);
        $this->assertSame('Tourze\FaceDetectBundle\DependencyInjection\FaceDetectExtension', $reflectionClass->getName());
    }

    public function testServicesYamlFileExists(): void
    {
        // 验证services.yaml文件是否存在
        $reflectionClass = new \ReflectionClass(FaceDetectExtension::class);
        $fileName = $reflectionClass->getFileName();
        if (false === $fileName) {
            self::fail('Cannot get extension file name');
        }
        $extensionPath = dirname($fileName);
        $servicesPath = $extensionPath . '/../Resources/config/services.yaml';

        $this->assertFileExists($servicesPath, 'services.yaml file should exist');
        $this->assertIsReadable($servicesPath, 'services.yaml should be readable');
    }

    public function testExtensionAlias(): void
    {
        // 验证Extension别名是否正确
        $extension = new FaceDetectExtension();
        $this->assertSame('face_detect', $extension->getAlias());
    }
}
