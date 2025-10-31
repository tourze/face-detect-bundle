<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 验证记录仓储类
 * 负责人脸验证记录数据的查询和统计操作
 *
 * @extends ServiceEntityRepository<VerificationRecord>
 */
#[AsRepository(entityClass: VerificationRecord::class)]
class VerificationRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerificationRecord::class);
    }

    /**
     * 根据用户ID查找验证记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findByUserId(string $userId, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('vr.createTime', 'DESC')
            ->setMaxResults($limit)
        ;

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据操作ID查找验证记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findByOperationId(string $operationId): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.operationId = :operationId')
            ->setParameter('operationId', $operationId)
            ->orderBy('vr.createTime', 'ASC')
        ;

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
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
            ->setMaxResults(1)
        ;

        /** @var VerificationRecord|null */
        $result = $qb->getQuery()->getOneOrNullResult();

        assert(null === $result || is_object($result));

        return $result;
    }

    /**
     * 统计用户在指定时间范围内的验证次数
     */
    public function countByUserIdAndTimeRange(
        string $userId,
        \DateTimeInterface $start,
        \DateTimeInterface $end,
    ): int {
        $qb = $this->createQueryBuilder('vr')
            ->select('COUNT(vr.id)')
            ->where('vr.userId = :userId')
            ->andWhere('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;

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
            ->setParameter('result', VerificationResult::SUCCESS)
        ;

        if (null !== $since) {
            $qb->andWhere('vr.createTime >= :since')
                ->setParameter('since', $since)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 根据业务类型统计验证记录
     *
     * @return array<string, mixed>
     */
    public function countByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->select([
                'COUNT(vr.id) as total',
                'COUNT(CASE WHEN vr.result = :success THEN 1 ELSE 0 END) as successful',
                'COUNT(CASE WHEN vr.result = :failed THEN 1 ELSE 0 END) as failed',
                'COUNT(CASE WHEN vr.result = :timeout THEN 1 ELSE 0 END) as timeout',
                'AVG(vr.confidenceScore) as avgConfidence',
                'AVG(vr.verificationTime) as avgTime',
            ])
            ->where('vr.businessType = :businessType')
            ->setParameter('businessType', $businessType)
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('timeout', VerificationResult::TIMEOUT)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找指定时间范围内的验证记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findByTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('vr.createTime', 'DESC')
        ;

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找低置信度的验证记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findLowConfidenceRecords(float $threshold = 0.7): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.confidenceScore IS NOT NULL')
            ->andWhere('vr.confidenceScore < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('vr.confidenceScore', 'ASC')
        ;

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 获取验证记录统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->select([
                'COUNT(vr.id) as total',
                'COUNT(CASE WHEN vr.result = :success THEN 1 ELSE 0 END) as successful',
                'COUNT(CASE WHEN vr.result = :failed THEN 1 ELSE 0 END) as failed',
                'COUNT(CASE WHEN vr.result = :skipped THEN 1 ELSE 0 END) as skipped',
                'COUNT(CASE WHEN vr.result = :timeout THEN 1 ELSE 0 END) as timeout',
                'AVG(vr.confidenceScore) as avgConfidence',
                'AVG(vr.verificationTime) as avgTime',
                'COUNT(DISTINCT vr.userId) as uniqueUsers',
            ])
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('skipped', VerificationResult::SKIPPED)
            ->setParameter('timeout', VerificationResult::TIMEOUT)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 删除指定时间之前的验证记录
     */
    public function deleteOldRecords(\DateTimeInterface $before): int
    {
        $qb = $this->createQueryBuilder('vr')
            ->delete()
            ->where('vr.createTime < :before')
            ->setParameter('before', $before)
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    /**
     * 查找指定时间段内失败的验证记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findFailedRecordsInTimeRange(
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        ?string $userId = null,
    ): array {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.result = :failed')
            ->andWhere('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('vr.createTime', 'DESC')
        ;

        if (null !== $userId) {
            $qb->andWhere('vr.userId = :userId')
                ->setParameter('userId', $userId)
            ;
        }

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找验证时间超过阈值的记录
     *
     * @return array<int, VerificationRecord>
     */
    public function findSlowVerifications(int $timeThreshold = 5000): array
    {
        $qb = $this->createQueryBuilder('vr')
            ->where('vr.verificationTime > :threshold')
            ->setParameter('threshold', $timeThreshold)
            ->orderBy('vr.verificationTime', 'DESC')
        ;

        /** @var array<int, VerificationRecord> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 获取每日验证统计
     *
     * @return array<string, mixed>
     */
    public function getDailyStatistics(\DateTimeInterface $date): array
    {
        $startOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('vr')
            ->select([
                'COUNT(vr.id) as total',
                'COUNT(CASE WHEN vr.result = :success THEN 1 ELSE 0 END) as successful',
                'COUNT(CASE WHEN vr.result = :failed THEN 1 ELSE 0 END) as failed',
                'COUNT(CASE WHEN vr.result = :timeout THEN 1 ELSE 0 END) as timeout',
                'AVG(vr.confidenceScore) as avgConfidence',
                'AVG(vr.verificationTime) as avgTime',
                'COUNT(DISTINCT vr.userId) as uniqueUsers',
            ])
            ->where('vr.createTime >= :start')
            ->andWhere('vr.createTime <= :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('failed', VerificationResult::FAILED)
            ->setParameter('timeout', VerificationResult::TIMEOUT)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    public function save(VerificationRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VerificationRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
