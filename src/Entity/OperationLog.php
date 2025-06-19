<?php

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\FaceDetectBundle\Repository\OperationLogRepository;

/**
 * 操作日志实体
 * 记录用户的业务操作和验证关联信息
 */
#[ORM\Entity(repositoryClass: OperationLogRepository::class)]
#[ORM\Table(name: 'operation_logs', options: ['comment' => '操作日志表'])]
#[ORM\Index(name: 'idx_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_operation_type', columns: ['operation_type'])]
#[ORM\Index(name: 'idx_verification_status', columns: ['verification_required', 'verification_completed'])]
#[ORM\Index(name: 'idx_status', columns: ['status'])]
#[ORM\Index(name: 'idx_started_time', columns: ['started_time'])]
#[ORM\Index(name: 'idx_operation_verification_status', columns: ['user_id', 'verification_required', 'verification_completed', 'status'])]
#[ORM\UniqueConstraint(name: 'uk_operation_id', columns: ['operation_id'])]
class OperationLog implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '操作ID'])]
    private string $operationId;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '操作类型'])]
    private string $operationType;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '业务上下文'])]
    private ?array $businessContext = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否需要验证'])]
    private bool $verificationRequired = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否完成验证'])]
    private bool $verificationCompleted = false;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '验证次数'])]
    private int $verificationCount = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1, 'comment' => '最少验证次数'])]
    private int $minVerificationCount = 1;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: OperationStatus::class, options: ['default' => 'pending', 'comment' => '状态'])]
    private OperationStatus $status = OperationStatus::PENDING;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private \DateTimeImmutable $startedTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeImmutable $completedTime = null;

    public function __construct(string $userId, string $operationId, string $operationType)
    {
        $this->userId = $userId;
        $this->operationId = $operationId;
        $this->operationType = $operationType;
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

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): self
    {
        $this->operationType = $operationType;
        return $this;
    }

    public function getBusinessContext(): ?array
    {
        return $this->businessContext;
    }

    public function setBusinessContext(?array $businessContext): self
    {
        $this->businessContext = $businessContext;
        return $this;
    }

    public function isVerificationRequired(): bool
    {
        return $this->verificationRequired;
    }

    public function setVerificationRequired(bool $verificationRequired): self
    {
        $this->verificationRequired = $verificationRequired;
        return $this;
    }

    public function isVerificationCompleted(): bool
    {
        return $this->verificationCompleted;
    }

    public function setVerificationCompleted(bool $verificationCompleted): self
    {
        $this->verificationCompleted = $verificationCompleted;
        return $this;
    }

    public function getVerificationCount(): int
    {
        return $this->verificationCount;
    }

    public function setVerificationCount(int $verificationCount): self
    {
        $this->verificationCount = $verificationCount;
        return $this;
    }

    public function getMinVerificationCount(): int
    {
        return $this->minVerificationCount;
    }

    public function setMinVerificationCount(int $minVerificationCount): self
    {
        $this->minVerificationCount = $minVerificationCount;
        return $this;
    }

    public function getStatus(): OperationStatus
    {
        return $this->status;
    }

    public function setStatus(OperationStatus $status): self
    {
        $this->status = $status;
        if ($status === OperationStatus::COMPLETED || $status === OperationStatus::FAILED || $status === OperationStatus::CANCELLED) {
            $this->completedTime = new \DateTimeImmutable();
        }
        return $this;
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
    public function incrementVerificationCount(): self
    {
        $this->verificationCount++;
        return $this;
    }

    /**
     * 检查验证是否满足要求
     */
    public function isVerificationSatisfied(): bool
    {
        return !$this->verificationRequired || 
               ($this->verificationCompleted && $this->verificationCount >= $this->minVerificationCount);
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
    public function setBusinessContextValue(string $key, mixed $value): self
    {
        if ($this->businessContext === null) {
            $this->businessContext = [];
        }
        $this->businessContext[$key] = $value;
        return $this;
    }

    /**
     * 检查操作是否已完成
     */
    public function isCompleted(): bool
    {
        return $this->status === OperationStatus::COMPLETED;
    }

    /**
     * 检查操作是否失败
     */
    public function isFailed(): bool
    {
        return $this->status === OperationStatus::FAILED;
    }

    /**
     * 检查操作是否被取消
     */
    public function isCancelled(): bool
    {
        return $this->status === OperationStatus::CANCELLED;
    }

    /**
     * 获取操作持续时间（秒）
     */
    public function getDuration(): ?float
    {
        if ($this->completedTime === null) {
            return null;
        }

        return $this->completedTime->getTimestamp() - $this->startedTime->getTimestamp();
    }
}
