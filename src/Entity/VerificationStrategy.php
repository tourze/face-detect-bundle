<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\FaceDetectBundle\Repository\VerificationStrategyRepository;

/**
 * 验证策略实体
 * 定义不同业务场景的验证策略
 */
#[ORM\Entity(repositoryClass: VerificationStrategyRepository::class)]
#[ORM\Table(name: 'verification_strategies', options: ['comment' => '验证策略表'])]
#[ORM\Index(name: 'verification_strategies_idx_enabled_priority', columns: ['is_enabled', 'priority'])]
#[ORM\UniqueConstraint(name: 'uk_name', columns: ['name'])]
class VerificationStrategy implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '策略名称'])]
    private string $name = '';

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '业务类型'])]
    private string $businessType = '';

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '策略描述'])]
    private ?string $description = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $isEnabled = true;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '优先级，数值越大优先级越高'])]
    private int $priority = 0;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '策略配置参数'])]
    private array $config = [];

    /**
     * @var Collection<int, StrategyRule>
     */
    #[ORM\OneToMany(mappedBy: 'strategy', targetEntity: StrategyRule::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $rules;

    /**
     * @var Collection<int, VerificationRecord>
     */
    #[ORM\OneToMany(mappedBy: 'strategy', targetEntity: VerificationRecord::class, fetch: 'EXTRA_LAZY')]
    private Collection $verificationRecords;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
        $this->verificationRecords = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('VerificationStrategy[%d]: %s (%s)', $this->id ?? 0, $this->name, $this->businessType);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name ?? '';
    }

    public function getBusinessType(): ?string
    {
        return $this->businessType;
    }

    public function setBusinessType(?string $businessType): void
    {
        $this->businessType = $businessType ?? '';
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed>|string $config
     */
    public function setConfig(array|string $config): void
    {
        if (is_string($config)) {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($config, true);
            $this->config = is_array($decoded) ? $decoded : [];
        } else {
            $this->config = $config;
        }
    }

    /**
     * 获取配置的JSON字符串格式用于表单显示
     */
    public function getConfigJson(): string
    {
        $encoded = json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false === $encoded ? '{}' : $encoded;
    }

    /**
     * 从JSON字符串设置配置（用于表单提交）
     */
    public function setConfigJson(string $configJson): void
    {
        $this->setConfig($configJson);
    }

    /**
     * @return Collection<int, StrategyRule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(StrategyRule $rule): void
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setStrategy($this);
        }
    }

    public function removeRule(StrategyRule $rule): void
    {
        if ($this->rules->removeElement($rule)) {
            if ($rule->getStrategy() === $this) {
                $rule->setStrategy(null);
            }
        }
    }

    /**
     * @return Collection<int, VerificationRecord>
     */
    public function getVerificationRecords(): Collection
    {
        return $this->verificationRecords;
    }

    /**
     * 获取配置项的值
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 设置配置项的值
     */
    public function setConfigValue(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * 检查策略是否可用
     */
    public function isUsable(): bool
    {
        return $this->isEnabled;
    }

    /**
     * 获取启用的规则
     *
     * @return Collection<int, StrategyRule>
     */
    public function getEnabledRules(): Collection
    {
        return $this->rules->filter(fn (StrategyRule $rule) => $rule->isEnabled());
    }
}
