<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\FaceDetectBundle\Repository\OperationLogRepository;

/**
 * 操作日志实体
 * 记录用户的业务操作和验证关联信息
 */
#[ORM\Entity(repositoryClass: OperationLogRepository::class)]
#[ORM\Table(name: 'operation_logs', options: ['comment' => '操作日志表'])]
#[ORM\Index(name: 'operation_logs_idx_verification_status', columns: ['verification_required', 'verification_completed'])]
#[ORM\Index(name: 'operation_logs_idx_operation_verification_status', columns: ['user_id', 'verification_required', 'verification_completed', 'status'])]
#[ORM\UniqueConstraint(name: 'uk_operation_id', columns: ['operation_id'])]
class OperationLog implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '操作ID'])]
    private string $operationId = '';

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '操作类型'])]
    private string $operationType = '';

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '业务上下文'])]
    private ?array $businessContext = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否需要验证'])]
    private bool $verificationRequired = false;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否完成验证'])]
    private bool $verificationCompleted = false;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '验证次数'])]
    private int $verificationCount = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1, 'comment' => '最少验证次数'])]
    private int $minVerificationCount = 1;

    #[IndexColumn]
    #[Assert\Choice(callback: [OperationStatus::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 16, enumType: OperationStatus::class, options: ['default' => 'pending', 'comment' => '状态'])]
    private OperationStatus $status = OperationStatus::PENDING;

    #[IndexColumn]
    #[Assert\Type(type: '\DateTimeImmutable')]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private \DateTimeImmutable $startedTime;

    #[Assert\Type(type: '\DateTimeImmutable')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeImmutable $completedTime = null;

    public function __construct()
    {
        $this->startedTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('OperationLog[%d]: %s - %s (%s)', $this->id ?? 0, $this->operationId, $this->operationType, $this->status->value);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function setOperationId(string $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): void
    {
        $this->operationType = $operationType;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBusinessContext(): ?array
    {
        return $this->businessContext;
    }

    /**
     * @param array<string, mixed>|null $businessContext
     */
    public function setBusinessContext(?array $businessContext): void
    {
        $this->businessContext = $businessContext;
    }

    public function isVerificationRequired(): bool
    {
        return $this->verificationRequired;
    }

    public function setVerificationRequired(bool $verificationRequired): void
    {
        $this->verificationRequired = $verificationRequired;
    }

    public function isVerificationCompleted(): bool
    {
        return $this->verificationCompleted;
    }

    public function setVerificationCompleted(bool $verificationCompleted): void
    {
        $this->verificationCompleted = $verificationCompleted;
    }

    public function getVerificationCount(): int
    {
        return $this->verificationCount;
    }

    public function setVerificationCount(int $verificationCount): void
    {
        $this->verificationCount = $verificationCount;
    }

    public function getMinVerificationCount(): int
    {
        return $this->minVerificationCount;
    }

    public function setMinVerificationCount(int $minVerificationCount): void
    {
        $this->minVerificationCount = $minVerificationCount;
    }

    public function getStatus(): OperationStatus
    {
        return $this->status;
    }

    public function setStatus(OperationStatus $status): void
    {
        $this->status = $status;
        if (OperationStatus::COMPLETED === $status || OperationStatus::FAILED === $status || OperationStatus::CANCELLED === $status) {
            $this->completedTime = new \DateTimeImmutable();
        }
    }

    public function getStartedTime(): \DateTimeImmutable
    {
        return $this->startedTime;
    }

    public function getCompletedTime(): ?\DateTimeImmutable
    {
        return $this->completedTime;
    }

    /**
     * 增加验证次数
     */
    public function incrementVerificationCount(): void
    {
        ++$this->verificationCount;
    }

    /**
     * 检查验证是否满足要求
     */
    public function isVerificationSatisfied(): bool
    {
        return !$this->verificationRequired
               || ($this->verificationCompleted && $this->verificationCount >= $this->minVerificationCount);
    }

    /**
     * 获取业务上下文的特定值
     */
    public function getBusinessContextValue(string $key, mixed $default = null): mixed
    {
        return $this->businessContext[$key] ?? $default;
    }

    /**
     * 设置业务上下文的特定值
     */
    public function setBusinessContextValue(string $key, mixed $value): void
    {
        if (null === $this->businessContext) {
            $this->businessContext = [];
        }
        $this->businessContext[$key] = $value;
    }

    /**
     * 检查操作是否已完成
     */
    public function isCompleted(): bool
    {
        return OperationStatus::COMPLETED === $this->status;
    }

    /**
     * 检查操作是否失败
     */
    public function isFailed(): bool
    {
        return OperationStatus::FAILED === $this->status;
    }

    /**
     * 检查操作是否被取消
     */
    public function isCancelled(): bool
    {
        return OperationStatus::CANCELLED === $this->status;
    }

    /**
     * 获取操作持续时间（秒）
     */
    public function getDuration(): ?float
    {
        if (null === $this->completedTime) {
            return null;
        }

        return $this->completedTime->getTimestamp() - $this->startedTime->getTimestamp();
    }
}
