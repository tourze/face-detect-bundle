<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 策略规则仓储类
 * 负责验证策略规则数据的查询和管理操作
 *
 * @extends ServiceEntityRepository<StrategyRule>
 */
#[AsRepository(entityClass: StrategyRule::class)]
class StrategyRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyRule::class);
    }

    /**
     * 根据策略查找启用的规则
     *
     * @return array<int, StrategyRule>
     */
    public function findEnabledByStrategy(VerificationStrategy $strategy): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.strategy = :strategy')
            ->andWhere('sr.isEnabled = :enabled')
            ->setParameter('strategy', $strategy)
            ->setParameter('enabled', true)
            ->orderBy('sr.priority', 'DESC')
            ->addOrderBy('sr.createTime', 'ASC')
        ;

        /** @var array<int, StrategyRule> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据规则类型查找规则
     *
     * @return array<int, StrategyRule>
     */
    public function findByRuleType(string $ruleType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.ruleType = :ruleType')
            ->setParameter('ruleType', $ruleType)
            ->orderBy('sr.priority', 'DESC')
        ;

        /** @var array<int, StrategyRule> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据策略和规则类型查找规则
     *
     * @return array<int, StrategyRule>
     */
    public function findByStrategyAndType(VerificationStrategy $strategy, string $ruleType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.strategy = :strategy')
            ->andWhere('sr.ruleType = :ruleType')
            ->setParameter('strategy', $strategy)
            ->setParameter('ruleType', $ruleType)
            ->orderBy('sr.priority', 'DESC')
        ;

        /** @var array<int, StrategyRule> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找最高优先级的启用规则
     */
    public function findHighestPriorityByStrategy(VerificationStrategy $strategy): ?StrategyRule
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.strategy = :strategy')
            ->andWhere('sr.isEnabled = :enabled')
            ->setParameter('strategy', $strategy)
            ->setParameter('enabled', true)
            ->orderBy('sr.priority', 'DESC')
            ->addOrderBy('sr.createTime', 'ASC')
            ->setMaxResults(1)
        ;

        /** @var StrategyRule|null */
        $result = $qb->getQuery()->getOneOrNullResult();

        assert(null === $result || is_object($result));

        return $result;
    }

    /**
     * 获取规则统计信息
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->select([
                'COUNT(sr.id) as total',
                'COUNT(CASE WHEN sr.isEnabled = true THEN 1 ELSE 0 END) as enabled',
                'COUNT(CASE WHEN sr.isEnabled = false THEN 1 ELSE 0 END) as disabled',
                'COUNT(DISTINCT sr.ruleType) as ruleTypes',
            ])
        ;

        /** @var array<string, mixed> */
        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 根据条件参数值查找规则
     *
     * @return array<int, StrategyRule>
     */
    public function findByConditionValue(string $conditionKey, mixed $value): array
    {
        $allRules = $this->findAll();
        $result = [];

        foreach ($allRules as $rule) {
            $conditions = $rule->getConditions();
            if (is_array($conditions) && array_key_exists($conditionKey, $conditions) && $conditions[$conditionKey] === $value) {
                $result[] = $rule;
            }
        }

        // 按优先级排序
        usort($result, function ($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $result;
    }

    /**
     * 根据动作类型查找规则
     *
     * @return array<int, StrategyRule>
     */
    public function findByActionType(string $actionType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.actions LIKE :actionType')
            ->setParameter('actionType', '%"' . $actionType . '"%')
            ->orderBy('sr.priority', 'DESC')
        ;

        /** @var array<int, StrategyRule> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 查找优先级范围内的规则
     *
     * @return array<int, StrategyRule>
     */
    public function findByPriorityRange(int $minPriority, int $maxPriority): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.priority >= :minPriority')
            ->andWhere('sr.priority <= :maxPriority')
            ->setParameter('minPriority', $minPriority)
            ->setParameter('maxPriority', $maxPriority)
            ->orderBy('sr.priority', 'DESC')
        ;

        /** @var array<int, StrategyRule> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 获取规则类型统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRuleTypeStatistics(): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->select([
                'sr.ruleType',
                'COUNT(sr.id) as total',
                'COUNT(CASE WHEN sr.isEnabled = true THEN 1 ELSE 0 END) as enabled',
                'AVG(sr.priority) as avgPriority',
            ])
            ->groupBy('sr.ruleType')
            ->orderBy('sr.ruleType', 'ASC')
        ;

        /** @var array<int, array<string, mixed>> */
        $result = $qb->getQuery()->getResult();

        assert(is_array($result));

        return $result;
    }

    /**
     * 批量更新规则启用状态
     *
     * @param array<int, int> $ruleIds
     */
    public function getUpdatedEnabledStatus(array $ruleIds, bool $enabled): int
    {
        $qb = $this->createQueryBuilder('sr')
            ->update()
            ->set('sr.isEnabled', ':enabled')
            ->set('sr.updateTime', ':now')
            ->where('sr.id IN (:ids)')
            ->setParameter('enabled', $enabled)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('ids', $ruleIds)
        ;

        $result = $qb->getQuery()->execute();

        assert(is_int($result));

        return $result;
    }

    public function save(StrategyRule $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StrategyRule $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
