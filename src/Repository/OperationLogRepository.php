<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 操作日志仓储类
 * 负责操作日志数据的查询和统计操作
 *
 * @extends ServiceEntityRepository<OperationLog>
 */
#[AsRepository(entityClass: OperationLog::class)]
class OperationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OperationLog::class);
    }

    /**
     * 根据操作ID查找操作日志
     */
    public function findByOperationId(string $operationId): ?OperationLog
    {
        return $this->findOneBy(['operationId' => $operationId]);
    }

    /**
     * 根据用户ID查找操作日志
     *
     * @return array<int, OperationLog>
     */
    public function findByUserId(string $userId, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('ol.startedTime', 'DESC')
            ->setMaxResults($limit)
        ;

        /** @var array<int, OperationLog> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找需要验证但未完成的操作
     *
     * @return array<int, OperationLog>
     */
    public function findPendingVerification(?string $userId = null): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.verificationRequired = :required')
            ->andWhere('ol.verificationCompleted = :completed')
            ->andWhere('ol.status IN (:statuses)')
            ->setParameter('required', true)
            ->setParameter('completed', false)
            ->setParameter('statuses', [OperationStatus::PENDING, OperationStatus::PROCESSING])
        ;

        if (null !== $userId) {
            $qb->andWhere('ol.userId = :userId')
                ->setParameter('userId', $userId)
            ;
        }

        $qb->orderBy('ol.startedTime', 'ASC');

        /** @var array<int, OperationLog> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 获取操作统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :pending THEN 1 ELSE 0 END) as pending',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 ELSE 0 END) as completed',
                'COUNT(CASE WHEN ol.verificationRequired = true THEN 1 ELSE 0 END) as requireVerification',
            ])
            ->setParameter('pending', OperationStatus::PENDING)
            ->setParameter('completed', OperationStatus::COMPLETED)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找指定时间范围内的操作日志
     *
     * @return array<int, OperationLog>
     */
    public function findByTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.startedTime >= :start')
            ->andWhere('ol.startedTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('ol.startedTime', 'DESC')
        ;

        /** @var array<int, OperationLog> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找超时的操作
     *
     * @return array<int, OperationLog>
     */
    public function findTimeoutOperations(int $timeoutMinutes = 30): array
    {
        $timeoutTime = new \DateTimeImmutable("-{$timeoutMinutes} minutes");

        $qb = $this->createQueryBuilder('ol')
            ->where('ol.status IN (:processingStatuses)')
            ->andWhere('ol.startedTime < :timeoutTime')
            ->setParameter('processingStatuses', [OperationStatus::PENDING, OperationStatus::PROCESSING])
            ->setParameter('timeoutTime', $timeoutTime)
            ->orderBy('ol.startedTime', 'ASC')
        ;

        /** @var array<int, OperationLog> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据业务类型统计操作
     *
     * @return array<string, mixed>
     */
    public function getCountByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :pending THEN 1 ELSE 0 END) as pending',
                'COUNT(CASE WHEN ol.status = :processing THEN 1 ELSE 0 END) as processing',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 ELSE 0 END) as completed',
                'COUNT(CASE WHEN ol.status = :failed THEN 1 ELSE 0 END) as failed',
            ])
            ->where('ol.businessType = :businessType')
            ->setParameter('businessType', $businessType)
            ->setParameter('pending', OperationStatus::PENDING)
            ->setParameter('processing', OperationStatus::PROCESSING)
            ->setParameter('completed', OperationStatus::COMPLETED)
            ->setParameter('failed', OperationStatus::FAILED)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 获取删除指定时间之前的操作日志结果
     */
    public function getDeletedOldLogs(\DateTimeInterface $before): int
    {
        $qb = $this->createQueryBuilder('ol')
            ->delete()
            ->where('ol.startedTime < :before')
            ->setParameter('before', $before)
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    /**
     * 获取每日操作统计
     *
     * @return array<string, mixed>
     */
    public function getDailyStatistics(\DateTimeInterface $date): array
    {
        $startOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 ELSE 0 END) as completed',
                'COUNT(CASE WHEN ol.verificationRequired = true THEN 1 ELSE 0 END) as withVerification',
                'COUNT(DISTINCT ol.userId) as uniqueUsers',
            ])
            ->where('ol.startedTime >= :start')
            ->andWhere('ol.startedTime <= :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('completed', OperationStatus::COMPLETED)
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    public function save(OperationLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OperationLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
