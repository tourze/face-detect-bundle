<?php

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;

/**
 * 人脸档案实体
 * 存储用户的人脸特征数据和相关信息
 */
#[ORM\Entity(repositoryClass: \Tourze\FaceDetectBundle\Repository\FaceProfileRepository::class)]
#[ORM\Table(name: 'face_profiles', options: ['comment' => '人脸档案表'])]
#[ORM\Index(name: 'idx_status', columns: ['status'])]
#[ORM\Index(name: 'idx_expires_time', columns: ['expires_time'])]
#[ORM\UniqueConstraint(name: 'uk_user_id', columns: ['user_id'])]
class FaceProfile implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private readonly int $id;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['comment' => '加密的人脸特征数据'])]
    private string $faceFeatures;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, options: ['default' => '0.00', 'comment' => '人脸质量评分(0-1)'])]
    private float $qualityScore = 0.00;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['default' => 'manual', 'comment' => '采集方式: manual, auto, import'])]
    private string $collectionMethod = 'manual';

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '采集设备信息'])]
    private ?array $deviceInfo = null;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: FaceProfileStatus::class, options: ['default' => 'active', 'comment' => '状态'])]
    private FaceProfileStatus $status = FaceProfileStatus::ACTIVE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $expiresTime = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '创建时间'])]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '更新时间'])]
    private \DateTimeInterface $updateTime;

    public function __construct(string $userId, string $faceFeatures)
    {
        $this->userId = $userId;
        $this->faceFeatures = $faceFeatures;
        $this->createTime = new \DateTimeImmutable();
        $this->updateTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('FaceProfile[%d]: %s', $this->id ?? 0, $this->userId);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getFaceFeatures(): string
    {
        return $this->faceFeatures;
    }

    public function setFaceFeatures(string $faceFeatures): self
    {
        $this->faceFeatures = $faceFeatures;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getQualityScore(): float
    {
        return $this->qualityScore;
    }

    public function setQualityScore(float $qualityScore): self
    {
        $this->qualityScore = $qualityScore;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getCollectionMethod(): string
    {
        return $this->collectionMethod;
    }

    public function setCollectionMethod(string $collectionMethod): self
    {
        $this->collectionMethod = $collectionMethod;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getDeviceInfo(): ?array
    {
        return $this->deviceInfo;
    }

    public function setDeviceInfo(?array $deviceInfo): self
    {
        $this->deviceInfo = $deviceInfo;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getStatus(): FaceProfileStatus
    {
        return $this->status;
    }

    public function setStatus(FaceProfileStatus $status): self
    {
        $this->status = $status;
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }

    public function getExpiresTime(): ?\DateTimeInterface
    {
        return $this->expiresTime;
    }

    public function setExpiresTime(?\DateTimeInterface $expiresTime): self
    {
        $this->expiresTime = $expiresTime;
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

    /**
     * 检查人脸档案是否已过期
     */
    public function isExpired(): bool
    {
        if ($this->expiresTime === null) {
            return false;
        }

        return $this->expiresTime < new \DateTimeImmutable();
    }

    /**
     * 检查人脸档案是否可用
     */
    public function isAvailable(): bool
    {
        return $this->status === FaceProfileStatus::ACTIVE && !$this->isExpired();
    }

    /**
     * 设置过期时间（从现在开始计算）
     */
    public function setExpiresAfter(\DateInterval $interval): self
    {
        $this->expiresTime = (new \DateTimeImmutable())->add($interval);
        $this->updateTime = new \DateTimeImmutable();
        return $this;
    }
} 