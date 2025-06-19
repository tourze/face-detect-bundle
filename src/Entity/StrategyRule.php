<?php

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\FaceDetectBundle\Repository\StrategyRuleRepository;

/**
 * 策略规则实体
 * 存储验证策略的具体规则配置
 */
#[ORM\Entity(repositoryClass: StrategyRuleRepository::class)]
#[ORM\Table(name: 'strategy_rules', options: ['comment' => '策略规则表'])]
#[ORM\Index(name: 'idx_strategy_id', columns: ['strategy_id'])]
#[ORM\Index(name: 'idx_rule_type', columns: ['rule_type'])]
#[ORM\Index(name: 'idx_enabled_priority', columns: ['is_enabled', 'priority'])]
class StrategyRule implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VerificationStrategy::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(name: 'strategy_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?VerificationStrategy $strategy = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '规则类型: time, frequency, risk, amount'])]
    private string $ruleType;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '规则名称'])]
    private string $ruleName;

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '规则条件'])]
    private array $conditions = [];

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '规则动作'])]
    private array $actions = [];

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $isEnabled = true;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '规则优先级'])]
    private int $priority = 0;


    public function __construct(string $ruleType, string $ruleName, array $conditions = [], array $actions = [])
    {
        $this->ruleType = $ruleType;
        $this->ruleName = $ruleName;
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function __toString(): string
    {
        return sprintf('StrategyRule[%d]: %s (%s)', $this->id ?? 0, $this->ruleName, $this->ruleType);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrategy(): ?VerificationStrategy
    {
        return $this->strategy;
    }

    public function setStrategy(?VerificationStrategy $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getRuleType(): string
    {
        return $this->ruleType;
    }

    public function setRuleType(string $ruleType): self
    {
        $this->ruleType = $ruleType;
        return $this;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
    }

    public function setRuleName(string $ruleName): self
    {
        $this->ruleName = $ruleName;
        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setConditions(array $conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): self
    {
        $this->actions = $actions;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }/**
     * 获取条件值
     */
    public function getConditionValue(string $key, mixed $default = null): mixed
    {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * 设置条件值
     */
    public function setConditionValue(string $key, mixed $value): self
    {
        $this->conditions[$key] = $value;
        return $this;
    }

    /**
     * 获取动作值
     */
    public function getActionValue(string $key, mixed $default = null): mixed
    {
        return $this->actions[$key] ?? $default;
    }

    /**
     * 设置动作值
     */
    public function setActionValue(string $key, mixed $value): self
    {
        $this->actions[$key] = $value;
        return $this;
    }

    /**
     * 检查规则是否可用
     */
    public function isUsable(): bool
    {
        return $this->isEnabled && $this->strategy?->isEnabled();
    }
}
