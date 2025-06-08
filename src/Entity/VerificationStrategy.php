<?php

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\FaceDetectBundle\Repository\VerificationStrategyRepository;

/**
 * 验证策略实体
 * 定义不同业务场景的验证策略
 */
#[ORM\Entity(repositoryClass: VerificationStrategyRepository::class)]
#[ORM\Table(name: 'verification_strategies', options: ['comment' => '验证策略表'])]
#[ORM\Index(name: 'idx_business_type', columns: ['business_type'])]
#[ORM\Index(name: 'idx_enabled_priority', columns: ['is_enabled', 'priority'])]
#[ORM\UniqueConstraint(name: 'uk_name', columns: ['name'])]
class VerificationStrategy implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private readonly int $id;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '策略名称'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '业务类型'])]
    private string $businessType;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '策略描述'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $isEnabled = true;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '优先级，数值越大优先级越高'])]
    private int $priority = 0;

    #[ORM\Column(type: Types::JSON, nullable: false, options: ['comment' => '策略配置参数'])]
    private array $config = [];

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '更新时间'])]
    private \DateTimeInterface $updateTime;

    #[ORM\OneToMany(mappedBy: 'strategy', targetEntity: StrategyRule::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $rules;

    #[ORM\OneToMany(mappedBy: 'strategy', targetEntity: VerificationRecord::class, fetch: 'EXTRA_LAZY')]
    private Collection $verificationRecords;

    public function __construct(string $name, string $businessType, array $config = [])
    {
        $this->name = $name;
        $this->businessType = $businessType;
        $this->config = $config;
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
        $this->rules = new ArrayCollection();
        $this->verificationRecords = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('VerificationStrategy[%d]: %s (%s)', $this->id ?? 0, $this->name, $this->businessType);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getBusinessType(): string
    {
        return $this->businessType;
    }

    public function setBusinessType(string $businessType): self
    {
        $this->businessType = $businessType;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }

    public function getUpdateTime(): \DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(StrategyRule $rule): self
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setStrategy($this);
        }
        return $this;
    }

    public function removeRule(StrategyRule $rule): self
    {
        if ($this->rules->removeElement($rule)) {
            if ($rule->getStrategy() === $this) {
                $rule->setStrategy(null);
            }
        }
        return $this;
    }

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
    public function setConfigValue(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
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
     */
    public function getEnabledRules(): Collection
    {
        return $this->rules->filter(fn(StrategyRule $rule) => $rule->isEnabled());
    }
} 