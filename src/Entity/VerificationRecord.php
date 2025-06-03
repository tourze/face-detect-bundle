<?php

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Enum\VerificationType;

/**
 * 验证记录实体
 * 记录每次人脸验证的详细信息
 */
#[ORM\Entity(repositoryClass: \Tourze\FaceDetectBundle\Repository\VerificationRecordRepository::class)]
#[ORM\Table(name: 'verification_records', options: ['comment' => '验证记录表'])]
#[ORM\Index(name: 'idx_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_strategy_id', columns: ['strategy_id'])]
#[ORM\Index(name: 'idx_business_type', columns: ['business_type'])]
#[ORM\Index(name: 'idx_operation_id', columns: ['operation_id'])]
#[ORM\Index(name: 'idx_result', columns: ['result'])]
#[ORM\Index(name: 'idx_create_time', columns: ['create_time'])]
#[ORM\Index(name: 'idx_user_verification_history', columns: ['user_id', 'create_time'])]
#[ORM\Index(name: 'idx_business_verification_stats', columns: ['business_type', 'result', 'create_time'])]
class VerificationRecord implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private readonly int $id;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId;

    #[ORM\ManyToOne(targetEntity: VerificationStrategy::class, inversedBy: 'verificationRecords')]
    #[ORM\JoinColumn(name: 'strategy_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private VerificationStrategy $strategy;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '业务类型'])]
    private string $businessType;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '关联的业务操作ID'])]
    private ?string $operationId = null;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: VerificationType::class, options: ['default' => 'required', 'comment' => '验证类型'])]
    private VerificationType $verificationType = VerificationType::REQUIRED;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: VerificationResult::class, nullable: false, options: ['comment' => '验证结果'])]
    private VerificationResult $result;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true, options: ['comment' => '置信度评分(0-1)'])]
    private ?float $confidenceScore = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 3, nullable: true, options: ['comment' => '验证耗时(秒)'])]
    private ?float $verificationTime = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '客户端信息'])]
    private ?array $clientInfo = null;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '错误码'])]
    private ?string $errorCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    private ?string $errorMessage = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeInterface $createTime;

    public function __construct(
        string $userId,
        VerificationStrategy $strategy,
        string $businessType,
        VerificationResult $result
    ) {
        $this->userId = $userId;
        $this->strategy = $strategy;
        $this->businessType = $businessType;
        $this->result = $result;
        $this->createTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('VerificationRecord[%d]: %s - %s', $this->id ?? 0, $this->userId, $this->result->value);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getStrategy(): VerificationStrategy
    {
        return $this->strategy;
    }

    public function setStrategy(VerificationStrategy $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getBusinessType(): string
    {
        return $this->businessType;
    }

    public function setBusinessType(string $businessType): self
    {
        $this->businessType = $businessType;
        return $this;
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function setOperationId(?string $operationId): self
    {
        $this->operationId = $operationId;
        return $this;
    }

    public function getVerificationType(): VerificationType
    {
        return $this->verificationType;
    }

    public function setVerificationType(VerificationType $verificationType): self
    {
        $this->verificationType = $verificationType;
        return $this;
    }

    public function getResult(): VerificationResult
    {
        return $this->result;
    }

    public function setResult(VerificationResult $result): self
    {
        $this->result = $result;
        return $this;
    }

    public function getConfidenceScore(): ?float
    {
        return $this->confidenceScore;
    }

    public function setConfidenceScore(?float $confidenceScore): self
    {
        $this->confidenceScore = $confidenceScore;
        return $this;
    }

    public function getVerificationTime(): ?float
    {
        return $this->verificationTime;
    }

    public function setVerificationTime(?float $verificationTime): self
    {
        $this->verificationTime = $verificationTime;
        return $this;
    }

    public function getClientInfo(): ?array
    {
        return $this->clientInfo;
    }

    public function setClientInfo(?array $clientInfo): self
    {
        $this->clientInfo = $clientInfo;
        return $this;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): self
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getCreateTime(): \DateTimeInterface
    {
        return $this->createTime;
    }

    /**
     * 检查验证是否成功
     */
    public function isSuccessful(): bool
    {
        return $this->result === VerificationResult::SUCCESS;
    }

    /**
     * 检查验证是否失败
     */
    public function isFailed(): bool
    {
        return $this->result === VerificationResult::FAILED;
    }

    /**
     * 设置错误信息
     */
    public function setError(string $errorCode, string $errorMessage): self
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * 获取客户端信息的特定值
     */
    public function getClientInfoValue(string $key, mixed $default = null): mixed
    {
        return $this->clientInfo[$key] ?? $default;
    }

    /**
     * 设置客户端信息的特定值
     */
    public function setClientInfoValue(string $key, mixed $value): self
    {
        if ($this->clientInfo === null) {
            $this->clientInfo = [];
        }
        $this->clientInfo[$key] = $value;
        return $this;
    }
}
