<?php

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Enum\VerificationResult;

/**
 * 验证记录仓储类
 */
class VerificationRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerificationRecord::class);
    }

    /**
     * 根据用户ID查找验证记录
     */
    public function findByUserId(string $userId, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('vr.createTime', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据操作ID查找验证记录
     */
    public function findByOperationId(string $operationId): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.operationId = :operationId')
            ->setParameter('operationId', $operationId)
            ->orderBy('vr.createTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找用户最近的成功验证记录
     */
    public function findLastSuccessfulByUserId(string $userId): ?VerificationRecord
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.userId = :userId')
            ->andWhere('vr.result = :result')
            ->setParameter('userId', $userId)
            ->setParameter('result', VerificationResult::SUCCESS)
            ->orderBy('vr.createTime', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * 统计用户在指定时间范围内的验证次数
     */
    public function countByUserIdAndTimeRange(
        string $userId,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): int {
        $qb = $this->createQueryBuilder('vr')
            ->select('COUNT(vr.id)')
            ->where('vr.userId = :userId')
            ->andWhere('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 统计用户成功验证次数
     */
    public function countSuccessfulByUserId(string $userId, ?\DateTimeInterface $since = null): int
    {
        $qb = $this->createQueryBuilder('vr')
            ->select('COUNT(vr.id)')
            ->where('vr.userId = :userId')
            ->andWhere('vr.result = :result')
            ->setParameter('userId', $userId)
            ->setParameter('result', VerificationResult::SUCCESS);

        if ($since !== null) {
            $qb->andWhere('vr.createTime >= :since')
                ->setParameter('since', $since);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 根据业务类型统计验证记录
     */
    public function countByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->select([
                'COUNT(vr.id) as total',
                'COUNT(CASE WHEN vr.result = :success THEN 1 END) as successful',
                'COUNT(CASE WHEN vr.result = :failed THEN 1 END) as failed',
                'COUNT(CASE WHEN vr.result = :timeout THEN 1 END) as timeout',
                'AVG(vr.confidenceScore) as avgConfidence',
                'AVG(vr.verificationTime) as avgTime'
            ])
            ->where('vr.businessType = :businessType')
            ->setParameter('businessType', $businessType)
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('timeout', VerificationResult::TIMEOUT);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 查找指定时间范围内的验证记录
     */
    public function findByTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('vr.createTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找低置信度的验证记录
     */
    public function findLowConfidenceRecords(float $threshold = 0.7): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.confidenceScore IS NOT NULL')
            ->andWhere('vr.confidenceScore < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('vr.confidenceScore', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取验证记录统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->select([
                'COUNT(vr.id) as total',
                'COUNT(CASE WHEN vr.result = :success THEN 1 END) as successful',
                'COUNT(CASE WHEN vr.result = :failed THEN 1 END) as failed',
                'COUNT(CASE WHEN vr.result = :skipped THEN 1 END) as skipped',
                'COUNT(CASE WHEN vr.result = :timeout THEN 1 END) as timeout',
                'AVG(vr.confidenceScore) as avgConfidence',
                'AVG(vr.verificationTime) as avgTime',
                'COUNT(DISTINCT vr.userId) as uniqueUsers'
            ])
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('skipped', VerificationResult::SKIPPED)
            ->setParameter('timeout', VerificationResult::TIMEOUT);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 删除指定时间之前的验证记录
     */
    public function deleteOldRecords(\DateTimeInterface $before): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->delete(VerificationRecord::class, 'vr')
            ->where('vr.createTime < :before')
            ->setParameter('before', $before);

        return $qb->getQuery()->execute();
    }
}
