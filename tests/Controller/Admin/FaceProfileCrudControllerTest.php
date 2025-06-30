<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Controller\Admin\FaceProfileCrudController;
use Tourze\FaceDetectBundle\Entity\FaceProfile;

class FaceProfileCrudControllerTest extends TestCase
{
    private FaceProfileCrudController $controller;

    public function test_controller_extends_abstract_crud_controller(): void
    {
        // Assert
        $this->assertInstanceOf(AbstractCrudController::class, $this->controller);
    }

    public function test_get_entity_fqcn_returns_correct_class(): void
    {
        // Act
        $result = $this->controller::getEntityFqcn();

        // Assert
        $this->assertSame(FaceProfile::class, $result);
    }

    public function test_configure_crud_method_exists(): void
    {
        // Arrange
        $crud = $this->createMock(Crud::class);
        $crud->expects($this->atLeastOnce())
            ->method('setEntityLabelInSingular')
            ->with('人脸档案')
            ->willReturnSelf();

        // Act & Assert
        $result = $this->controller->configureCrud($crud);
        $this->assertInstanceOf(Crud::class, $result);
    }

    public function test_configure_fields_method_exists(): void
    {
        // Act
        $fields = $this->controller->configureFields('index');

        // Assert
        $this->assertInstanceOf(\Generator::class, $fields);

        // 验证字段配置是可遍历的
        $fieldArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldArray);
    }



    public function test_controller_class_is_properly_structured(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->controller);

        // 验证类不是抽象的
        $this->assertFalse($reflectionClass->isAbstract());

        // 验证类可以实例化
        $this->assertTrue($reflectionClass->isInstantiable());

        // 验证有正确的命名空间
        $this->assertSame('Tourze\FaceDetectBundle\Controller\Admin\FaceProfileCrudController', $reflectionClass->getName());
    }

    public function test_controller_has_proper_methods(): void
    {
        // Assert - These methods are guaranteed to exist by the parent class
        $reflection = new \ReflectionClass($this->controller);
        $this->assertTrue($reflection->hasMethod('getEntityFqcn'));
        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
        $this->assertTrue($reflection->hasMethod('configureActions'));
    }

    protected function setUp(): void
    {
        $this->controller = new FaceProfileCrudController();
    }
}
