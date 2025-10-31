<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\FaceDetectBundle\Repository\FaceProfileRepository;

#[ORM\Entity(repositoryClass: FaceProfileRepository::class)]
#[ORM\Table(name: 'face_profiles', options: ['comment' => '人脸档案表'])]
#[ORM\UniqueConstraint(name: 'uk_user_id', columns: ['user_id'])]
class FaceProfile implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '用户ID'])]
    private string $userId = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['comment' => '加密的人脸特征数据'])]
    private string $faceFeatures = '';

    #[Assert\Range(min: 0, max: 1)]
    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, options: ['default' => '0.00', 'comment' => '人脸质量评分(0-1)'])]
    private float $qualityScore = 0.00;

    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[Assert\Choice(choices: ['manual', 'auto', 'import'])]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['default' => 'manual', 'comment' => '采集方式: manual, auto, import'])]
    private string $collectionMethod = 'manual';

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '采集设备信息'])]
    private ?array $deviceInfo = null;

    #[IndexColumn]
    #[Assert\Choice(callback: [FaceProfileStatus::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 16, enumType: FaceProfileStatus::class, options: ['default' => 'active', 'comment' => '状态'])]
    private FaceProfileStatus $status = FaceProfileStatus::ACTIVE;

    #[IndexColumn]
    #[Assert\Type(type: '\DateTimeImmutable')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expiresTime = null;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return sprintf('FaceProfile[%d]: %s', $this->id ?? 0, $this->userId);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId ?? '';
    }

    public function getFaceFeatures(): string
    {
        return $this->faceFeatures;
    }

    public function setFaceFeatures(?string $faceFeatures): void
    {
        $this->faceFeatures = $faceFeatures ?? '';
    }

    public function getQualityScore(): float
    {
        return $this->qualityScore;
    }

    public function setQualityScore(?float $qualityScore): void
    {
        $this->qualityScore = $qualityScore ?? 0.00;
    }

    public function getCollectionMethod(): string
    {
        return $this->collectionMethod;
    }

    public function setCollectionMethod(?string $collectionMethod): void
    {
        $this->collectionMethod = $collectionMethod ?? 'manual';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDeviceInfo(): ?array
    {
        return $this->deviceInfo;
    }

    /**
     * @param array<string, mixed>|null $deviceInfo
     */
    public function setDeviceInfo(?array $deviceInfo): void
    {
        $this->deviceInfo = $deviceInfo;
    }

    public function getStatus(): FaceProfileStatus
    {
        return $this->status;
    }

    public function setStatus(FaceProfileStatus $status): void
    {
        $this->status = $status;
    }

    public function getExpiresTime(): ?\DateTimeImmutable
    {
        return $this->expiresTime;
    }

    public function setExpiresTime(?\DateTimeImmutable $expiresTime): void
    {
        $this->expiresTime = $expiresTime;
    }

    public function isExpired(): bool
    {
        if (null === $this->expiresTime) {
            return false;
        }

        return $this->expiresTime < new \DateTimeImmutable();
    }

    public function isAvailable(): bool
    {
        return FaceProfileStatus::ACTIVE === $this->status && !$this->isExpired();
    }

    public function setExpiresAfter(\DateInterval $interval): void
    {
        $this->expiresTime = (new \DateTimeImmutable())->add($interval);
    }
}
