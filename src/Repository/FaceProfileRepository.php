<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 人脸档案仓储类
 * 负责人脸档案数据的查询和统计操作
 *
 * @extends ServiceEntityRepository<FaceProfile>
 */
#[AsRepository(entityClass: FaceProfile::class)]
class FaceProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaceProfile::class);
    }

    /**
     * 根据用户ID查找人脸档案
     */
    public function findByUserId(string $userId): ?FaceProfile
    {
        return $this->findOneBy(['userId' => $userId]);
    }

    /**
     * 查找用户的可用人脸档案
     */
    public function findAvailableByUserId(string $userId): ?FaceProfile
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.userId = :userId')
            ->andWhere('fp.status = :status')
            ->andWhere('(fp.expiresTime IS NULL OR fp.expiresTime > :now)')
            ->setParameter('userId', $userId)
            ->setParameter('status', FaceProfileStatus::ACTIVE)
            ->setParameter('now', new \DateTimeImmutable())
        ;

        $result = $qb->getQuery()->getOneOrNullResult();
        assert($result instanceof FaceProfile || null === $result);

        return $result;
    }

    /**
     * 查找已过期的人脸档案
     *
     * @return array<int, FaceProfile>
     * @phpstan-return array<int, FaceProfile>
     */
    public function findExpiredProfiles(?\DateTimeInterface $before = null): array
    {
        $before ??= new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('fp')
            ->where('fp.expiresTime IS NOT NULL')
            ->andWhere('fp.expiresTime <= :before')
            ->andWhere('fp.status != :expiredStatus')
            ->setParameter('before', $before)
            ->setParameter('expiredStatus', FaceProfileStatus::EXPIRED)
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 统计用户人脸档案数量
     */
    public function countByUserId(string $userId): int
    {
        $qb = $this->createQueryBuilder('fp')
            ->select('COUNT(fp.id)')
            ->where('fp.userId = :userId')
            ->setParameter('userId', $userId)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找指定时间范围内创建的人脸档案
     *
     * @return array<int, FaceProfile>
     */
    public function findByCreateTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.createTime >= :start')
            ->andWhere('fp.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('fp.createTime', 'DESC')
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找质量分数低于阈值的人脸档案
     *
     * @return array<int, FaceProfile>
     */
    public function findByLowQuality(float $threshold = 0.6): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.qualityScore < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('fp.qualityScore', 'ASC')
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 批量更新过期状态
     */
    public function markExpiredProfiles(): int
    {
        $qb = $this->createQueryBuilder('fp')
            ->update()
            ->set('fp.status', ':expiredStatus')
            ->set('fp.updateTime', ':now')
            ->where('fp.expiresTime IS NOT NULL')
            ->andWhere('fp.expiresTime <= :now')
            ->andWhere('fp.status = :activeStatus')
            ->setParameter('expiredStatus', FaceProfileStatus::EXPIRED)
            ->setParameter('activeStatus', FaceProfileStatus::ACTIVE)
            ->setParameter('now', new \DateTimeImmutable())
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    /**
     * 删除指定状态的人脸档案
     */
    public function deleteByStatus(FaceProfileStatus $status): int
    {
        $qb = $this->createQueryBuilder('fp')
            ->delete()
            ->where('fp.status = :status')
            ->setParameter('status', $status)
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    /**
     * 获取人脸档案统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->select([
                'COUNT(fp.id) as total',
                'COUNT(CASE WHEN fp.status = :active THEN 1 ELSE 0 END) as active',
                'COUNT(CASE WHEN fp.status = :expired THEN 1 ELSE 0 END) as expired',
                'COUNT(CASE WHEN fp.status = :disabled THEN 1 ELSE 0 END) as disabled',
                'AVG(fp.qualityScore) as avgQuality',
                'MIN(fp.qualityScore) as minQuality',
                'MAX(fp.qualityScore) as maxQuality',
            ])
            ->setParameter('active', FaceProfileStatus::ACTIVE)
            ->setParameter('expired', FaceProfileStatus::EXPIRED)
            ->setParameter('disabled', FaceProfileStatus::DISABLED)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找即将过期的人脸档案（指定天数内）
     *
     * @return array<int, FaceProfile>
     */
    public function findExpiringProfiles(int $days = 7): array
    {
        $futureDate = new \DateTimeImmutable("+{$days} days");

        $qb = $this->createQueryBuilder('fp')
            ->where('fp.expiresTime IS NOT NULL')
            ->andWhere('fp.expiresTime <= :futureDate')
            ->andWhere('fp.expiresTime > :now')
            ->andWhere('fp.status = :status')
            ->setParameter('futureDate', $futureDate)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('status', FaceProfileStatus::ACTIVE)
            ->orderBy('fp.expiresTime', 'ASC')
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据采集方式查找人脸档案
     *
     * @return array<int, FaceProfile>
     */
    public function findByCollectionMethod(string $collectionMethod): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.collectionMethod = :method')
            ->setParameter('method', $collectionMethod)
            ->orderBy('fp.createTime', 'DESC')
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找高质量人脸档案
     *
     * @return array<int, FaceProfile>
     */
    public function findHighQualityProfiles(float $threshold = 0.8): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.qualityScore >= :threshold')
            ->andWhere('fp.status = :status')
            ->setParameter('threshold', $threshold)
            ->setParameter('status', FaceProfileStatus::ACTIVE)
            ->orderBy('fp.qualityScore', 'DESC')
        ;

        /** @var array<int, FaceProfile> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    public function save(FaceProfile $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FaceProfile $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
