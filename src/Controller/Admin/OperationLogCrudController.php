<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

/**
 * 操作日志管理控制器
 */
#[AdminCrud(routePath: '/face-detect/operation-log', routeName: 'face_detect_operation_log')]
class OperationLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OperationLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('操作日志')
            ->setEntityLabelInPlural('操作日志')
            ->setPageTitle('index', '操作日志列表')
            ->setPageTitle('detail', '操作日志详情')
            ->setPageTitle('new', '新建操作日志')
            ->setPageTitle('edit', '编辑操作日志')
            ->setHelp('index', '查看和管理用户的业务操作和验证关联信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['userId', 'operationId', 'operationType'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield TextField::new('userId', '用户ID')
            ->setRequired(true)
            ->setHelp('操作用户的唯一标识符');

        yield TextField::new('operationId', '操作ID')
            ->setRequired(true)
            ->setHelp('业务操作的唯一标识符');

        yield TextField::new('operationType', '操作类型')
            ->setRequired(true)
            ->setHelp('业务操作的分类类型');

        yield CodeEditorField::new('businessContext', '业务上下文')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setRequired(false)
            ->setHelp('操作相关的业务上下文信息')
            ->formatValue(function ($value) {
                return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value;
            });

        yield BooleanField::new('verificationRequired', '需要验证')
            ->setHelp('此操作是否需要人脸验证');

        yield BooleanField::new('verificationCompleted', '验证完成')
            ->setHelp('人脸验证是否已完成');

        yield IntegerField::new('verificationCount', '验证次数')
            ->setHelp('已进行的验证次数')
            ->formatValue(function ($value) {
                return (int)$value;
            });

        yield IntegerField::new('minVerificationCount', '最少验证次数')
            ->setHelp('操作要求的最少验证次数')
            ->formatValue(function ($value) {
                return (int)$value;
            });

        yield ChoiceField::new('status', '操作状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => OperationStatus::class])
            ->formatValue(function ($value) {
                return $value instanceof OperationStatus ? $value->getDescription() : '';
            });

        yield NumberField::new('duration', '操作持续时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setHelp('操作总持续时间(秒)')
            ->formatValue(function ($value, $entity) {
                if ($entity && method_exists($entity, 'getDuration')) {
                    $duration = $entity->getDuration();
                    return $duration !== null ? number_format($duration, 1) . 's' : '进行中';
                }
                return '';
            });

        yield DateTimeField::new('startedTime', '开始时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield DateTimeField::new('completedTime', '完成时间')
            ->setRequired(false)
            ->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        $statusChoices = [];
        foreach (OperationStatus::cases() as $case) {
            $statusChoices[$case->getDescription()] = $case->value;
        }

        return $filters
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(TextFilter::new('operationId', '操作ID'))
            ->add(TextFilter::new('operationType', '操作类型'))
            ->add(BooleanFilter::new('verificationRequired', '需要验证'))
            ->add(BooleanFilter::new('verificationCompleted', '验证完成'))
            ->add(ChoiceFilter::new('status', '操作状态')
                ->setChoices($statusChoices))
            ->add(NumericFilter::new('verificationCount', '验证次数'))
            ->add(DateTimeFilter::new('startedTime', '开始时间'))
            ->add(DateTimeFilter::new('completedTime', '完成时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL]);
    }
}
