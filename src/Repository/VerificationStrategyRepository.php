<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 验证策略仓储类
 * 负责验证策略数据的查询和管理操作
 *
 * @extends ServiceEntityRepository<VerificationStrategy>
 */
#[AsRepository(entityClass: VerificationStrategy::class)]
class VerificationStrategyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerificationStrategy::class);
    }

    /**
     * 根据业务类型查找启用的策略
     *
     * @return array<int, VerificationStrategy>
     */
    public function findEnabledByBusinessType(string $businessType): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.businessType = :businessType')
            ->andWhere('vs.isEnabled = :enabled')
            ->setParameter('businessType', $businessType)
            ->setParameter('enabled', true)
            ->orderBy('vs.priority', 'DESC')
            ->addOrderBy('vs.createTime', 'ASC')
        ;

        /** @var array<int, VerificationStrategy> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
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
            ->setMaxResults(1)
        ;

        /** @var VerificationStrategy|null */
        $result = $qb->getQuery()->getOneOrNullResult();

        assert(null === $result || is_object($result));

        return $result;
    }

    /**
     * 查找所有启用的策略
     *
     * @return array<int, VerificationStrategy>
     */
    public function findAllEnabled(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('vs.businessType', 'ASC')
            ->addOrderBy('vs.priority', 'DESC')
        ;

        /** @var array<int, VerificationStrategy> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据优先级范围查找策略
     *
     * @return array<int, VerificationStrategy>
     */
    public function findByPriorityRange(int $minPriority, int $maxPriority): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->where('vs.priority >= :minPriority')
            ->andWhere('vs.priority <= :maxPriority')
            ->setParameter('minPriority', $minPriority)
            ->setParameter('maxPriority', $maxPriority)
            ->orderBy('vs.priority', 'DESC')
        ;

        /** @var array<int, VerificationStrategy> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 统计各业务类型的策略数量
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByBusinessType(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select('vs.businessType, COUNT(vs.id) as count')
            ->groupBy('vs.businessType')
            ->orderBy('count', 'DESC')
        ;

        /** @var array<int, array<string, mixed>> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找包含特定配置键的策略
     *
     * @return array<int, VerificationStrategy>
     */
    public function findByConfigKey(string $configKey): array
    {
        $allStrategies = $this->findAll();
        $result = [];

        foreach ($allStrategies as $strategy) {
            $config = $strategy->getConfig();
            if (is_array($config) && array_key_exists($configKey, $config)) {
                $result[] = $strategy;
            }
        }

        return $result;
    }

    /**
     * 批量启用/禁用策略
     *
     * @param array<int, int> $strategyIds
     */
    public function updateEnabledStatus(array $strategyIds, bool $enabled): int
    {
        $qb = $this->createQueryBuilder('vs')
            ->update()
            ->set('vs.isEnabled', ':enabled')
            ->set('vs.updateTime', ':now')
            ->where('vs.id IN (:ids)')
            ->setParameter('enabled', $enabled)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('ids', $strategyIds)
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    /**
     * 获取策略统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select([
                'COUNT(vs.id) as total',
                'COUNT(CASE WHEN vs.isEnabled = true THEN 1 ELSE 0 END) as enabled',
                'COUNT(CASE WHEN vs.isEnabled = false THEN 1 ELSE 0 END) as disabled',
                'COUNT(DISTINCT vs.businessType) as businessTypes',
                'AVG(vs.priority) as avgPriority',
                'MAX(vs.priority) as maxPriority',
                'MIN(vs.priority) as minPriority',
            ])
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找需要更新的策略（基于某些条件）
     *
     * @return array<int, VerificationStrategy>
     */
    public function findForUpdate(?\DateTimeInterface $since = null): array
    {
        $since ??= new \DateTimeImmutable('-1 day');

        $qb = $this->createQueryBuilder('vs')
            ->where('vs.updateTime >= :since')
            ->setParameter('since', $since)
            ->orderBy('vs.updateTime', 'DESC')
        ;

        /** @var array<int, VerificationStrategy> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找默认策略（优先级最高且启用的策略）
     *
     * @return array<int, VerificationStrategy>
     */
    public function findDefaultStrategies(): array
    {
        $subQuery = $this->createQueryBuilder('vs_sub')
            ->select('MAX(vs_sub.priority)')
            ->where('vs_sub.businessType = vs.businessType')
            ->andWhere('vs_sub.isEnabled = true')
            ->getDQL()
        ;

        $qb = $this->createQueryBuilder('vs')
            ->where('vs.isEnabled = true')
            ->andWhere('vs.priority = (' . $subQuery . ')')
            ->orderBy('vs.businessType', 'ASC')
        ;

        /** @var array<int, VerificationStrategy> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据配置参数值查找策略
     *
     * @return array<int, VerificationStrategy>
     */
    public function findByConfigValue(string $configKey, mixed $value): array
    {
        $allStrategies = $this->findAll();
        $result = [];

        foreach ($allStrategies as $strategy) {
            $config = $strategy->getConfig();
            if (is_array($config) && array_key_exists($configKey, $config) && $config[$configKey] === $value) {
                $result[] = $strategy;
            }
        }

        return $result;
    }

    /**
     * 获取业务类型分组的策略统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBusinessTypeStatistics(): array
    {
        $qb = $this->createQueryBuilder('vs')
            ->select([
                'vs.businessType',
                'COUNT(vs.id) as total',
                'COUNT(CASE WHEN vs.isEnabled = true THEN 1 ELSE 0 END) as enabled',
                'AVG(vs.priority) as avgPriority',
                'MAX(vs.priority) as maxPriority',
            ])
            ->groupBy('vs.businessType')
            ->orderBy('vs.businessType', 'ASC')
        ;

        /** @var array<int, array<string, mixed>> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    public function save(VerificationStrategy $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VerificationStrategy $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
