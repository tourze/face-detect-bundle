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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Enum\VerificationType;

/**
 * 验证记录管理控制器
 *
 * @extends AbstractCrudController<VerificationRecord>
 */
#[AdminCrud(routePath: '/face-detect/verification-record', routeName: 'face_detect_verification_record')]
final class VerificationRecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VerificationRecord::class;
    }

    private function formatStrategyValue(mixed $value): string
    {
        if (is_object($value) && method_exists($value, 'getName') && method_exists($value, 'getBusinessType')) {
            $name = $value->getName();
            $businessType = $value->getBusinessType();

            return sprintf('%s (%s)',
                is_string($name) ? $name : '',
                is_string($businessType) ? $businessType : ''
            );
        }

        return '';
    }

    private function formatNumericValue(mixed $value, int $decimals, string $suffix = ''): string
    {
        return null !== $value && is_numeric($value)
            ? number_format((float) $value, $decimals) . $suffix
            : '';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('验证记录')
            ->setEntityLabelInPlural('验证记录')
            ->setPageTitle('index', '验证记录列表')
            ->setPageTitle('detail', '验证记录详情')
            ->setPageTitle('new', '新建验证记录')
            ->setPageTitle('edit', '编辑验证记录')
            ->setHelp('index', '查看和管理用户的人脸验证记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['userId', 'businessType', 'operationId'])
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

        yield TextField::new('userId', '用户ID')
            ->setRequired(true)
            ->setHelp('验证用户的唯一标识符')
        ;

        yield AssociationField::new('strategy', '验证策略')
            ->setRequired(true)
            ->setHelp('使用的验证策略')
            ->formatValue(fn (mixed $value): string => $this->formatStrategyValue($value))
        ;

        yield TextField::new('businessType', '业务类型')
            ->setRequired(true)
            ->setHelp('验证相关的业务场景')
        ;

        yield TextField::new('operationId', '操作ID')
            ->setRequired(false)
            ->setHelp('关联的业务操作标识符')
        ;

        yield ChoiceField::new('verificationType', '验证类型')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => VerificationType::class])
            ->formatValue(function ($value) {
                return $value instanceof VerificationType ? $value->getDescription() : '';
            })
        ;

        yield ChoiceField::new('result', '验证结果')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => VerificationResult::class])
            ->formatValue(function ($value) {
                return $value instanceof VerificationResult ? $value->getDescription() : '';
            })
        ;

        yield NumberField::new('confidenceScore', '置信度分数')
            ->setNumDecimals(2)
            ->setRequired(false)
            ->setHelp('验证的置信度评分(0-1)')
            ->formatValue(fn (mixed $value): string => $this->formatNumericValue($value, 2))
        ;

        yield NumberField::new('verificationTime', '验证耗时')
            ->setNumDecimals(3)
            ->setRequired(false)
            ->setHelp('验证操作耗时(秒)')
            ->formatValue(fn (mixed $value): string => $this->formatNumericValue($value, 3, 's'))
        ;

        yield CodeEditorField::new('clientInfo', '客户端信息')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setRequired(false)
            ->setHelp('客户端设备和环境信息')
            ->formatValue(function ($value) {
                return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value;
            })
        ;

        yield TextField::new('errorCode', '错误码')
            ->setRequired(false)
            ->hideOnIndex()
            ->setHelp('验证失败时的错误码')
        ;

        yield TextField::new('errorMessage', '错误信息')
            ->setRequired(false)
            ->hideOnIndex()
            ->setHelp('验证失败时的错误详情')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $verificationTypeChoices = [];
        foreach (VerificationType::cases() as $case) {
            $verificationTypeChoices[$case->getDescription()] = $case->value;
        }

        $verificationResultChoices = [];
        foreach (VerificationResult::cases() as $case) {
            $verificationResultChoices[$case->getDescription()] = $case->value;
        }

        return $filters
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(EntityFilter::new('strategy', '验证策略'))
            ->add(TextFilter::new('businessType', '业务类型'))
            ->add(TextFilter::new('operationId', '操作ID'))
            ->add(ChoiceFilter::new('verificationType', '验证类型')
                ->setChoices($verificationTypeChoices))
            ->add(ChoiceFilter::new('result', '验证结果')
                ->setChoices($verificationResultChoices))
            ->add(NumericFilter::new('confidenceScore', '置信度分数'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL])
        ;
    }
}
