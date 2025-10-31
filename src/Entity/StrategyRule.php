<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\FaceDetectBundle\Repository\StrategyRuleRepository;

/**
 * 策略规则实体
 * 存储验证策略的具体规则配置
 */
#[ORM\Entity(repositoryClass: StrategyRuleRepository::class)]
#[ORM\Table(name: 'strategy_rules', options: ['comment' => '策略规则表'])]
#[ORM\Index(name: 'strategy_rules_idx_rule_enabled_priority', columns: ['is_enabled', 'priority'])]
class StrategyRule implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: VerificationStrategy::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(name: 'strategy_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?VerificationStrategy $strategy = null;

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Assert\Choice(choices: ['time', 'frequency', 'risk', 'amount'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '规则类型: time, frequency, risk, amount'])]
    private string $ruleType = 'time';

    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '规则名称'])]
    private string $ruleName = '';

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '规则条件'])]
    private array $conditions = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '规则动作'])]
    private array $actions = [];

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $isEnabled = true;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '规则优先级'])]
    private int $priority = 0;

    public function __construct()
    {
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

    public function setStrategy(?VerificationStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getRuleType(): ?string
    {
        return $this->ruleType;
    }

    public function setRuleType(?string $ruleType): void
    {
        $this->ruleType = $ruleType ?? 'time';
    }

    public function getRuleName(): ?string
    {
        return $this->ruleName;
    }

    public function setRuleName(?string $ruleName): void
    {
        $this->ruleName = $ruleName ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array<string, mixed>|string $conditions
     */
    public function setConditions(array|string $conditions): void
    {
        if (is_string($conditions)) {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($conditions, true);
            $this->conditions = is_array($decoded) ? $decoded : [];
        } else {
            $this->conditions = $conditions;
        }
    }

    /**
     * 获取条件的 JSON 字符串表示（用于表单显示）
     */
    public function getConditionsJson(): string
    {
        $encoded = json_encode($this->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false === $encoded ? '{}' : $encoded;
    }

    /**
     * Set conditions from JSON string (for form submission)
     */
    public function setConditionsJson(string $conditionsJson): void
    {
        $this->setConditions($conditionsJson);
    }

    /**
     * @return array<string, mixed>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array<string, mixed>|string $actions
     */
    public function setActions(array|string $actions): void
    {
        if (is_string($actions)) {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($actions, true);
            $this->actions = is_array($decoded) ? $decoded : [];
        } else {
            $this->actions = $actions;
        }
    }

    /**
     * 获取动作的JSON字符串格式用于表单显示
     */
    public function getActionsJson(): string
    {
        $encoded = json_encode($this->actions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false === $encoded ? '{}' : $encoded;
    }

    /**
     * 从JSON字符串设置动作（用于表单提交）
     */
    public function setActionsJson(string $actionsJson): void
    {
        $this->setActions($actionsJson);
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * 获取条件值
     */
    public function getConditionValue(string $key, mixed $default = null): mixed
    {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * 设置条件值
     */
    public function setConditionValue(string $key, mixed $value): void
    {
        $this->conditions[$key] = $value;
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
    public function setActionValue(string $key, mixed $value): void
    {
        $this->actions[$key] = $value;
    }

    /**
     * 检查规则是否可用
     */
    public function isUsable(): bool
    {
        return $this->isEnabled && ($this->strategy?->isEnabled() ?? false);
    }
}
