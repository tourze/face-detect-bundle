<?php

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Entity\OperationStatus;

/**
 * 操作日志仓储类
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
    public function findPendingVerification(string $userId = null): array
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
}
