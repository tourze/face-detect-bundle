<?php

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

/**
 * 人脸档案仓储类
 */
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
            ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * 查找已过期的人脸档案
     */
    public function findExpiredProfiles(?\DateTimeInterface $before = null): array
    {
        $before = $before ?? new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('fp')
            ->where('fp.expiresTime IS NOT NULL')
            ->andWhere('fp.expiresTime <= :before')
            ->andWhere('fp.status != :expiredStatus')
            ->setParameter('before', $before)
            ->setParameter('expiredStatus', FaceProfileStatus::EXPIRED);

        return $qb->getQuery()->getResult();
    }

    /**
     * 统计用户人脸档案数量
     */
    public function countByUserId(string $userId): int
    {
        $qb = $this->createQueryBuilder('fp')
            ->select('COUNT(fp.id)')
            ->where('fp.userId = :userId')
            ->setParameter('userId', $userId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找指定时间范围内创建的人脸档案
     */
    public function findByCreateTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.createTime >= :start')
            ->andWhere('fp.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('fp.createTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找质量分数低于阈值的人脸档案
     */
    public function findByLowQuality(float $threshold = 0.6): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->where('fp.qualityScore < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('fp.qualityScore', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 批量更新过期状态
     */
    public function markExpiredProfiles(): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->update(FaceProfile::class, 'fp')
            ->set('fp.status', ':expiredStatus')
            ->set('fp.updateTime', ':now')
            ->where('fp.expiresTime IS NOT NULL')
            ->andWhere('fp.expiresTime <= :now')
            ->andWhere('fp.status = :activeStatus')
            ->setParameter('expiredStatus', FaceProfileStatus::EXPIRED)
            ->setParameter('activeStatus', FaceProfileStatus::ACTIVE)
            ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->execute();
    }

    /**
     * 删除指定状态的人脸档案
     */
    public function deleteByStatus(FaceProfileStatus $status): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->delete(FaceProfile::class, 'fp')
            ->where('fp.status = :status')
            ->setParameter('status', $status);

        return $qb->getQuery()->execute();
    }

    /**
     * 获取人脸档案统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('fp')
            ->select([
                'COUNT(fp.id) as total',
                'COUNT(CASE WHEN fp.status = :active THEN 1 END) as active',
                'COUNT(CASE WHEN fp.status = :expired THEN 1 END) as expired',
                'COUNT(CASE WHEN fp.status = :disabled THEN 1 END) as disabled',
                'AVG(fp.qualityScore) as avgQuality',
                'MIN(fp.qualityScore) as minQuality',
                'MAX(fp.qualityScore) as maxQuality'
            ])
            ->setParameter('active', FaceProfileStatus::ACTIVE)
            ->setParameter('expired', FaceProfileStatus::EXPIRED)
            ->setParameter('disabled', FaceProfileStatus::DISABLED);

        return $qb->getQuery()->getSingleResult();
    }
}
