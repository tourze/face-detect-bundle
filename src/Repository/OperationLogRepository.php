<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

/**
 * 操作日志仓储类
 * 负责操作日志数据的查询和统计操作
 *
 * @method OperationLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationLog[]    findAll()
 * @method OperationLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
     */
    public function findByUserId(string $userId, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('ol.startedTime', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找需要验证但未完成的操作
     */
    public function findPendingVerification(?string $userId = null): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.verificationRequired = :required')
            ->andWhere('ol.verificationCompleted = :completed')
            ->andWhere('ol.status IN (:statuses)')
            ->setParameter('required', true)
            ->setParameter('completed', false)
            ->setParameter('statuses', [OperationStatus::PENDING, OperationStatus::PROCESSING]);

        if ($userId !== null) {
            $qb->andWhere('ol.userId = :userId')
                ->setParameter('userId', $userId);
        }

        $qb->orderBy('ol.startedTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取操作统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :pending THEN 1 END) as pending',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 END) as completed',
                'COUNT(CASE WHEN ol.verificationRequired = true THEN 1 END) as requireVerification'
            ])
            ->setParameter('pending', OperationStatus::PENDING)
            ->setParameter('completed', OperationStatus::COMPLETED);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 查找指定时间范围内的操作日志
     */
    public function findByTimeRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->where('ol.startedTime >= :start')
            ->andWhere('ol.startedTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('ol.startedTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找超时的操作
     */
    public function findTimeoutOperations(int $timeoutMinutes = 30): array
    {
        $timeoutTime = new \DateTimeImmutable("-{$timeoutMinutes} minutes");

        $qb = $this->createQueryBuilder('ol')
            ->where('ol.status IN (:processingStatuses)')
            ->andWhere('ol.startedTime < :timeoutTime')
            ->setParameter('processingStatuses', [OperationStatus::PENDING, OperationStatus::PROCESSING])
            ->setParameter('timeoutTime', $timeoutTime)
            ->orderBy('ol.startedTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据业务类型统计操作
     */
    public function getCountByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :pending THEN 1 END) as pending',
                'COUNT(CASE WHEN ol.status = :processing THEN 1 END) as processing',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 END) as completed',
                'COUNT(CASE WHEN ol.status = :failed THEN 1 END) as failed'
            ])
            ->where('ol.businessType = :businessType')
            ->setParameter('businessType', $businessType)
            ->setParameter('pending', OperationStatus::PENDING)
            ->setParameter('processing', OperationStatus::PROCESSING)
            ->setParameter('completed', OperationStatus::COMPLETED)
            ->setParameter('failed', OperationStatus::FAILED);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 获取删除指定时间之前的操作日志结果
     */
    public function getDeletedOldLogs(\DateTimeInterface $before): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->delete(OperationLog::class, 'ol')
            ->where('ol.startedTime < :before')
            ->setParameter('before', $before);

        return $qb->getQuery()->execute();
    }

    /**
     * 获取每日操作统计
     */
    public function getDailyStatistics(\DateTimeInterface $date): array
    {
        $startOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('ol')
            ->select([
                'COUNT(ol.id) as total',
                'COUNT(CASE WHEN ol.status = :completed THEN 1 END) as completed',
                'COUNT(CASE WHEN ol.verificationRequired = true THEN 1 END) as withVerification',
                'COUNT(DISTINCT ol.userId) as uniqueUsers'
            ])
            ->where('ol.startedTime >= :start')
            ->andWhere('ol.startedTime <= :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('completed', OperationStatus::COMPLETED);

        return $qb->getQuery()->getSingleResult();
    }
}
