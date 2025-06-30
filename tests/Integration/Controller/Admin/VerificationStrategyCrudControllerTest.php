<?php

namespace Tourze\FaceDetectBundle\Tests\Integration\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Controller\Admin\VerificationStrategyCrudController;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

class VerificationStrategyCrudControllerTest extends TestCase
{
    private VerificationStrategyCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new VerificationStrategyCrudController();
    }

    public function test_controller_can_be_instantiated(): void
    {
        // Assert
        $this->assertInstanceOf(VerificationStrategyCrudController::class, $this->controller);
    }

    public function test_get_entity_fqcn_returns_correct_class(): void
    {
        // Act
        $entityFqcn = VerificationStrategyCrudController::getEntityFqcn();

        // Assert
        $this->assertSame(VerificationStrategy::class, $entityFqcn);
    }

    public function test_configure_crud_sets_correct_labels(): void
    {
        // Arrange
        $crud = $this->createMock(Crud::class);
        
        // Expect
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('验证策略')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('验证策略')
            ->willReturnSelf();
            
        $crud->expects($this->exactly(4))
            ->method('setPageTitle')
            ->willReturnCallback(function ($page, $title) use ($crud) {
                $this->assertContains($page, ['index', 'detail', 'new', 'edit']);
                $this->assertNotEmpty($title);
                return $crud;
            });
            
        $crud->expects($this->once())
            ->method('setDefaultSort')
            ->with(['priority' => 'DESC', 'id' => 'DESC'])
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setHelp')
            ->with('index', '管理不同业务场景的人脸验证策略配置')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setSearchFields')
            ->with(['name', 'businessType', 'description'])
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setPaginatorPageSize')
            ->with(20)
            ->willReturnSelf();

        // Act
        $result = $this->controller->configureCrud($crud);

        // Assert
        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Config\Crud::class, $result);
    }

    public function test_configure_fields_returns_correct_fields(): void
    {
        // Arrange
        $pageName = Crud::PAGE_INDEX;

        // Act
        $fields = $this->controller->configureFields($pageName);

        // Assert - configureFields returns a Generator, convert to array
        $fieldsArray = iterator_to_array($fields);
        $this->assertCount(10, $fieldsArray);

        // Verify field types
        $this->assertInstanceOf(IdField::class, $fieldsArray[0]);
        $this->assertInstanceOf(TextField::class, $fieldsArray[1]);
        $this->assertInstanceOf(TextField::class, $fieldsArray[2]);
        $this->assertInstanceOf(TextareaField::class, $fieldsArray[3]);
        $this->assertInstanceOf(BooleanField::class, $fieldsArray[4]);
        $this->assertInstanceOf(IntegerField::class, $fieldsArray[5]);
        $this->assertInstanceOf(CodeEditorField::class, $fieldsArray[6]);
    }

    public function test_configure_fields_sets_correct_labels(): void
    {
        // Arrange
        $pageName = Crud::PAGE_INDEX;

        // Act
        $fields = $this->controller->configureFields($pageName);

        // Assert - convert Generator to array first
        $fieldsArray = iterator_to_array($fields);
        $fieldLabels = array_map(function ($field) {
            return $field->getAsDto()->getLabel();
        }, $fieldsArray);

        $expectedLabels = [
            'ID',
            '策略名称',
            '业务类型',
            '策略描述',
            '启用状态',
            '优先级',
            '策略配置',
            '关联规则',
            '创建时间',
            '更新时间'
        ];

        $this->assertEquals($expectedLabels, $fieldLabels);
    }

    public function test_controller_extends_abstract_crud_controller(): void
    {
        // Assert
        $this->assertInstanceOf(
            \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController::class,
            $this->controller
        );
    }

    public function test_controller_implements_crud_interface(): void
    {
        // Assert
        $reflectionClass = new \ReflectionClass($this->controller);
        
        // Verify required methods exist
        $this->assertTrue($reflectionClass->hasMethod('getEntityFqcn'));
        $this->assertTrue($reflectionClass->hasMethod('configureCrud'));
        $this->assertTrue($reflectionClass->hasMethod('configureFields'));
    }

    public function test_name_field_configuration(): void
    {
        // Arrange
        $pageName = Crud::PAGE_INDEX;

        // Act
        $fields = $this->controller->configureFields($pageName);
        $fieldsArray = iterator_to_array($fields);
        $nameField = $fieldsArray[1]; // name field is at index 1

        // Assert
        $fieldDto = $nameField->getAsDto();
        $this->assertTrue($fieldDto->getFormTypeOption('required'));
    }

    public function test_priority_field_configuration(): void
    {
        // Arrange
        $pageName = Crud::PAGE_INDEX;

        // Act
        $fields = $this->controller->configureFields($pageName);
        $fieldsArray = iterator_to_array($fields);
        $priorityField = $fieldsArray[3]; // priority field is at index 3

        // Assert
        $fieldDto = $priorityField->getAsDto();
        $this->assertEquals(0, $fieldDto->getFormTypeOption('attr')['min']);
    }
}