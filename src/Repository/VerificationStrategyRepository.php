<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * 验证策略仓储类
 * 负责验证策略数据的查询和管理操作
 *
 * @method VerificationStrategy|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerificationStrategy|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerificationStrategy[]    findAll()
 * @method VerificationStrategy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerificationStrategyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerificationStrategy::class);
    }

    /**
     * 根据业务类型查找启用的策略
     */
    public function findEnabledByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.businessType = :businessType')
            ->andWhere('vs.isEnabled = :enabled')
            ->setParameter('businessType', $businessType)
            ->setParameter('enabled', true)
            ->orderBy('vs.priority', 'DESC')
            ->addOrderBy('vs.createTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据名称查找策略
     */
    public function findByName(string $name): ?VerificationStrategy
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * 查找最高优先级的启用策略
     */
    public function findHighestPriorityByBusinessType(string $businessType): ?VerificationStrategy
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.businessType = :businessType')
            ->andWhere('vs.isEnabled = :enabled')
            ->setParameter('businessType', $businessType)
            ->setParameter('enabled', true)
            ->orderBy('vs.priority', 'DESC')
            ->addOrderBy('vs.createTime', 'ASC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * 查找所有启用的策略
     */
    public function findAllEnabled(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('vs.businessType', 'ASC')
            ->addOrderBy('vs.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据优先级范围查找策略
     */
    public function findByPriorityRange(int $minPriority, int $maxPriority): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.priority >= :minPriority')
            ->andWhere('vs.priority <= :maxPriority')
            ->setParameter('minPriority', $minPriority)
            ->setParameter('maxPriority', $maxPriority)
            ->orderBy('vs.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 统计各业务类型的策略数量
     */
    public function countByBusinessType(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select('vs.businessType, COUNT(vs.id) as count')
            ->groupBy('vs.businessType')
            ->orderBy('count', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找包含特定配置键的策略
     */
    public function findByConfigKey(string $configKey): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('JSON_EXTRACT(vs.config, :configPath) IS NOT NULL')
            ->setParameter('configPath', '$.' . $configKey);

        return $qb->getQuery()->getResult();
    }

    /**
     * 批量启用/禁用策略
     */
    public function updateEnabledStatus(array $strategyIds, bool $enabled): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->update(VerificationStrategy::class, 'vs')
            ->set('vs.isEnabled', ':enabled')
            ->set('vs.updateTime', ':now')
            ->where('vs.id IN (:ids)')
            ->setParameter('enabled', $enabled)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('ids', $strategyIds);

        return $qb->getQuery()->execute();
    }

    /**
     * 获取策略统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select([
                'COUNT(vs.id) as total',
                'COUNT(CASE WHEN vs.isEnabled = true THEN 1 END) as enabled',
                'COUNT(CASE WHEN vs.isEnabled = false THEN 1 END) as disabled',
                'COUNT(DISTINCT vs.businessType) as businessTypes',
                'AVG(vs.priority) as avgPriority',
                'MAX(vs.priority) as maxPriority',
                'MIN(vs.priority) as minPriority'
            ]);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 查找需要更新的策略（基于某些条件）
     */
    public function findForUpdate(?\DateTimeInterface $since = null): array
    {
        $since = $since ?? new \DateTimeImmutable('-1 day');

        $qb = $this->createQueryBuilder('vs')
            ->where('vs.updateTime >= :since')
            ->setParameter('since', $since)
            ->orderBy('vs.updateTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找默认策略（优先级最高且启用的策略）
     */
    public function findDefaultStrategies(): array
    {
        $subquery = $this->getEntityManager()->createQueryBuilder()
            ->select('MAX(vs2.priority)')
            ->from(VerificationStrategy::class, 'vs2')
            ->where('vs2.businessType = vs.businessType')
            ->andWhere('vs2.isEnabled = true')
            ->getDQL();

        $qb = $this->createQueryBuilder('vs')
            ->where('vs.isEnabled = true')
            ->andWhere('vs.priority = (' . $subquery . ')')
            ->orderBy('vs.businessType', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据配置参数值查找策略
     */
    public function findByConfigValue(string $configKey, mixed $value): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('JSON_EXTRACT(vs.config, :configPath) = :value')
            ->setParameter('configPath', '$.' . $configKey)
            ->setParameter('value', $value);

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取业务类型分组的策略统计
     */
    public function getBusinessTypeStatistics(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select([
                'vs.businessType',
                'COUNT(vs.id) as total',
                'COUNT(CASE WHEN vs.isEnabled = true THEN 1 END) as enabled',
                'AVG(vs.priority) as avgPriority',
                'MAX(vs.priority) as maxPriority'
            ])
            ->groupBy('vs.businessType')
            ->orderBy('vs.businessType', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
