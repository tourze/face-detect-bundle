<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * 验证策略管理控制器
 *
 * @extends AbstractCrudController<VerificationStrategy>
 */
#[AdminCrud(routePath: '/face-detect/verification-strategy', routeName: 'face_detect_verification_strategy')]
final class VerificationStrategyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VerificationStrategy::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('验证策略')
            ->setEntityLabelInPlural('验证策略')
            ->setPageTitle('index', '验证策略列表')
            ->setPageTitle('detail', '验证策略详情')
            ->setPageTitle('new', '新建验证策略')
            ->setPageTitle('edit', '编辑验证策略')
            ->setHelp('index', '管理不同业务场景的人脸验证策略配置')
            ->setDefaultSort(['priority' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['name', 'businessType', 'description'])
            ->setPaginatorPageSize(20)
        ;
    }

    /**
     * @return iterable<FieldInterface|string>
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('name', '策略名称')
            ->setRequired(true)
            ->setHelp('策略的唯一名称标识')
        ;

        yield TextField::new('businessType', '业务类型')
            ->setRequired(true)
            ->setHelp('适用的业务场景类型')
        ;

        yield TextareaField::new('description', '策略描述')
            ->setRequired(false)
            ->setHelp('策略的详细说明')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isEnabled', '启用状态')
            ->setHelp('是否启用此策略')
        ;

        yield IntegerField::new('priority', '优先级')
            ->setHelp('数值越大优先级越高')
            ->setFormTypeOption('attr', ['min' => 0])
            ->formatValue(function (mixed $value): string {
                return (string) (is_numeric($value) ? (int) $value : 0);
            })
        ;

        yield CodeEditorField::new('configJson', '策略配置')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('JSON格式的策略配置参数')
            ->setFormTypeOption('empty_data', '{}')
        ;

        yield CollectionField::new('rules', '关联规则')
            ->hideOnForm()
            ->setTemplatePath('@EasyAdmin/crud/field/collection.html.twig')
            ->formatValue(function ($value) {
                if (!$value) {
                    return '暂无规则';
                }
                $count = is_countable($value) ? count($value) : 0;

                return "共 {$count} 条规则";
            })
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '策略名称'))
            ->add(TextFilter::new('businessType', '业务类型'))
            ->add(BooleanFilter::new('isEnabled', '启用状态'))
            ->add(NumericFilter::new('priority', '优先级'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function createEntity(string $entityFqcn): VerificationStrategy
    {
        return new VerificationStrategy();
    }
}
