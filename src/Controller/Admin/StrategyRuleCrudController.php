<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\FaceDetectBundle\Entity\StrategyRule;

/**
 * 策略规则管理控制器
 */
#[AdminCrud(routePath: '/face-detect/strategy-rule', routeName: 'face_detect_strategy_rule')]
class StrategyRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StrategyRule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('策略规则')
            ->setEntityLabelInPlural('策略规则')
            ->setPageTitle('index', '策略规则列表')
            ->setPageTitle('detail', '策略规则详情')
            ->setPageTitle('new', '新建策略规则')
            ->setPageTitle('edit', '编辑策略规则')
            ->setHelp('index', '管理验证策略的具体规则配置')
            ->setDefaultSort(['priority' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['ruleName', 'ruleType'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield AssociationField::new('strategy', '关联策略')
            ->setRequired(true)
            ->setHelp('选择此规则所属的验证策略')
            ->formatValue(function ($value) {
                return $value ? sprintf('%s (%s)', $value->getName(), $value->getBusinessType()) : '';
            });

        yield ChoiceField::new('ruleType', '规则类型')
            ->setChoices([
                '时间规则' => 'time',
                '频率规则' => 'frequency',
                '风险规则' => 'risk',
                '金额规则' => 'amount',
            ])
            ->setRequired(true)
            ->setHelp('规则的分类类型');

        yield TextField::new('ruleName', '规则名称')
            ->setRequired(true)
            ->setHelp('规则的描述性名称');

        yield CodeEditorField::new('conditions', '规则条件')
            ->setLanguage('json')
            ->hideOnIndex()
            ->setHelp('JSON格式的规则条件配置')
            ->formatValue(function ($value) {
                return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value;
            });

        yield CodeEditorField::new('actions', '规则动作')
            ->setLanguage('json')
            ->hideOnIndex()
            ->setHelp('JSON格式的规则动作配置')
            ->formatValue(function ($value) {
                return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value;
            });

        yield BooleanField::new('isEnabled', '启用状态')
            ->setHelp('是否启用此规则');

        yield IntegerField::new('priority', '规则优先级')
            ->setHelp('数值越大优先级越高')
            ->formatValue(function ($value) {
                return (int)$value;
            });

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('strategy', '关联策略'))
            ->add(ChoiceFilter::new('ruleType', '规则类型')
                ->setChoices([
                    '时间规则' => 'time',
                    '频率规则' => 'frequency',
                    '风险规则' => 'risk',
                    '金额规则' => 'amount',
                ]))
            ->add(TextFilter::new('ruleName', '规则名称'))
            ->add(BooleanFilter::new('isEnabled', '启用状态'))
            ->add(NumericFilter::new('priority', '规则优先级'))
            ->add(DateTimeFilter::new('createTime', '创建时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }
}
