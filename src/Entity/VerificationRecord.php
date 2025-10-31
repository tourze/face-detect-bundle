<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Enum\VerificationType;
use Tourze\FaceDetectBundle\Repository\VerificationRecordRepository;

/**
 * 验证记录实体
 * 记录每次人脸验证的详细信息
 */
#[ORM\Entity(repositoryClass: VerificationRecordRepository::class)]
#[ORM\Table(name: 'verification_records', options: ['comment' => '验证记录表'])]
#[ORM\Index(name: 'verification_records_idx_user_verification_history', columns: ['user_id', 'create_time'])]
#[ORM\Index(name: 'verification_records_idx_business_verification_stats', columns: ['business_type', 'result', 'create_time'])]
class VerificationRecord implements \Stringable
{
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId = '';

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: VerificationStrategy::class, inversedBy: 'verificationRecords')]
    #[ORM\JoinColumn(name: 'strategy_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?VerificationStrategy $strategy = null;

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '业务类型'])]
    private string $businessType = '';

    #[IndexColumn]
    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '关联的业务操作ID'])]
    private ?string $operationId = null;

    #[Assert\Choice(callback: [VerificationType::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 16, enumType: VerificationType::class, options: ['default' => 'required', 'comment' => '验证类型'])]
    private VerificationType $verificationType = VerificationType::REQUIRED;

    #[IndexColumn]
    #[Assert\Choice(callback: [VerificationResult::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 16, enumType: VerificationResult::class, nullable: false, options: ['comment' => '验证结果'])]
    private ?VerificationResult $result = null;

    #[Assert\Range(min: 0, max: 1)]
    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true, options: ['comment' => '置信度评分(0-1)'])]
    private ?float $confidenceScore = null;

    #[Assert\GreaterThan(value: 0)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 3, nullable: true, options: ['comment' => '验证耗时(秒)'])]
    private ?float $verificationTime = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '客户端信息'])]
    private ?array $clientInfo = null;

    #[Assert\Length(max: 32)]
    #[ORM\Column(type: Types::STRING, length: 32, nullable: true, options: ['comment' => '错误码'])]
    private ?string $errorCode = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    private ?string $errorMessage = null;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return sprintf('VerificationRecord[%d]: %s - %s', $this->id ?? 0, $this->userId, $this->result->value ?? 'unknown');
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

    public function getStrategy(): ?VerificationStrategy
    {
        return $this->strategy;
    }

    public function setStrategy(?VerificationStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getBusinessType(): string
    {
        return $this->businessType;
    }

    public function setBusinessType(string $businessType): void
    {
        $this->businessType = $businessType;
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function setOperationId(?string $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function getVerificationType(): VerificationType
    {
        return $this->verificationType;
    }

    public function setVerificationType(VerificationType $verificationType): void
    {
        $this->verificationType = $verificationType;
    }

    public function getResult(): ?VerificationResult
    {
        return $this->result;
    }

    public function setResult(?VerificationResult $result): void
    {
        $this->result = $result;
    }

    public function getConfidenceScore(): ?float
    {
        return $this->confidenceScore;
    }

    public function setConfidenceScore(?float $confidenceScore): void
    {
        $this->confidenceScore = $confidenceScore;
    }

    public function getVerificationTime(): ?float
    {
        return $this->verificationTime;
    }

    public function setVerificationTime(?float $verificationTime): void
    {
        $this->verificationTime = $verificationTime;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getClientInfo(): ?array
    {
        return $this->clientInfo;
    }

    /**
     * @param array<string, mixed>|null $clientInfo
     */
    public function setClientInfo(?array $clientInfo): void
    {
        $this->clientInfo = $clientInfo;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setErrorCode(?string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * 检查验证是否成功
     */
    public function isSuccessful(): bool
    {
        return VerificationResult::SUCCESS === $this->result;
    }

    /**
     * 检查验证是否失败
     */
    public function isFailed(): bool
    {
        return VerificationResult::FAILED === $this->result;
    }

    /**
     * 设置错误信息
     */
    public function setError(string $errorCode, string $errorMessage): void
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
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
    public function setClientInfoValue(string $key, mixed $value): void
    {
        if (null === $this->clientInfo) {
            $this->clientInfo = [];
        }
        $this->clientInfo[$key] = $value;
    }
}
