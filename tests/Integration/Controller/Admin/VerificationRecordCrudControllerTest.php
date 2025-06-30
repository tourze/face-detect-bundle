<?php

namespace Tourze\FaceDetectBundle\Tests\Integration\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Controller\Admin\VerificationRecordCrudController;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;

class VerificationRecordCrudControllerTest extends TestCase
{
    private VerificationRecordCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new VerificationRecordCrudController();
    }

    public function test_controller_can_be_instantiated(): void
    {
        // Assert
        $this->assertInstanceOf(VerificationRecordCrudController::class, $this->controller);
    }

    public function test_get_entity_fqcn_returns_correct_class(): void
    {
        // Act
        $entityFqcn = VerificationRecordCrudController::getEntityFqcn();

        // Assert
        $this->assertSame(VerificationRecord::class, $entityFqcn);
    }

    public function test_configure_crud_sets_correct_labels(): void
    {
        // Arrange
        $crud = $this->createMock(Crud::class);
        
        // Expect
        $crud->expects($this->once())
            ->method('setEntityLabelInSingular')
            ->with('验证记录')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setEntityLabelInPlural')
            ->with('验证记录')
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
            ->with(['id' => 'DESC'])
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setHelp')
            ->with('index', '查看和管理用户的人脸验证记录')
            ->willReturnSelf();
            
        $crud->expects($this->once())
            ->method('setSearchFields')
            ->with(['userId', 'businessType', 'operationId'])
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
        $this->assertCount(13, $fieldsArray);

        // Verify field types
        $this->assertInstanceOf(IdField::class, $fieldsArray[0]);
        $this->assertInstanceOf(TextField::class, $fieldsArray[1]);
        $this->assertInstanceOf(AssociationField::class, $fieldsArray[2]); 
        $this->assertInstanceOf(TextField::class, $fieldsArray[3]);
        $this->assertInstanceOf(TextField::class, $fieldsArray[4]);
        $this->assertInstanceOf(ChoiceField::class, $fieldsArray[5]);
        $this->assertInstanceOf(ChoiceField::class, $fieldsArray[6]);
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
            '用户ID',
            '验证策略',
            '业务类型',
            '操作ID',
            '验证类型',
            '验证结果',
            '置信度分数',
            '验证耗时',
            '客户端信息',
            '错误码',
            '错误信息',
            '创建时间'
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
        $this->assertTrue($reflectionClass->hasMethod('configureActions'));
        $this->assertTrue($reflectionClass->hasMethod('configureFields'));
    }
}