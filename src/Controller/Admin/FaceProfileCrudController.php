<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

/**
 * 人脸档案管理控制器
 */
#[AdminCrud(routePath: '/face-detect/face-profile', routeName: 'face_detect_face_profile')]
class FaceProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FaceProfile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('人脸档案')
            ->setEntityLabelInPlural('人脸档案')
            ->setPageTitle('index', '人脸档案列表')
            ->setPageTitle('detail', '人脸档案详情')
            ->setPageTitle('new', '新建人脸档案')
            ->setPageTitle('edit', '编辑人脸档案')
            ->setHelp('index', '管理用户的人脸特征数据和相关信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['userId', 'collectionMethod'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield TextField::new('userId', '用户ID')
            ->setRequired(true)
            ->setHelp('用户的唯一标识符');

        yield TextareaField::new('faceFeatures', '人脸特征')
            ->setRequired(true)
            ->setHelp('加密的人脸特征数据')
            ->hideOnIndex()
            ->setMaxLength(1000);

        yield NumberField::new('qualityScore', '质量评分')
            ->setNumDecimals(2)
            ->setHelp('人脸质量评分(0-1)')
            ->formatValue(function ($value) {
                return number_format((float)$value, 2);
            });

        yield ChoiceField::new('collectionMethod', '采集方式')
            ->setChoices([
                '手动采集' => 'manual',
                '自动采集' => 'auto',
                '导入数据' => 'import',
            ])
            ->renderExpanded(false);

        yield CodeEditorField::new('deviceInfo', '设备信息')
            ->setLanguage('json')
            ->hideOnIndex()
            ->setHelp('采集设备的详细信息');

        yield ChoiceField::new('status', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => FaceProfileStatus::class])
            ->formatValue(function ($value) {
                return $value instanceof FaceProfileStatus ? $value->getDescription() : '';
            });

        yield DateTimeField::new('expiresTime', '过期时间')
            ->setRequired(false)
            ->setHelp('档案过期时间，为空表示永不过期');

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        $statusChoices = [];
        foreach (FaceProfileStatus::cases() as $case) {
            $statusChoices[$case->getDescription()] = $case->value;
        }

        return $filters
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(ChoiceFilter::new('collectionMethod', '采集方式')
                ->setChoices([
                    '手动采集' => 'manual',
                    '自动采集' => 'auto',
                    '导入数据' => 'import',
                ]))
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices($statusChoices))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('expiresTime', '过期时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }
}
