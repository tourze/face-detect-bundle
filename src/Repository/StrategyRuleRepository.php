<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * 策略规则仓储类
 * 负责验证策略规则数据的查询和管理操作
 *
 * @method StrategyRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method StrategyRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method StrategyRule[]    findAll()
 * @method StrategyRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StrategyRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrategyRule::class);
    }

    /**
     * 根据策略查找启用的规则
     */
    public function findEnabledByStrategy(VerificationStrategy $strategy): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.strategy = :strategy')
            ->andWhere('sr.isEnabled = :enabled')
            ->setParameter('strategy', $strategy)
            ->setParameter('enabled', true)
            ->orderBy('sr.priority', 'DESC')
            ->addOrderBy('sr.createTime', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据规则类型查找规则
     */
    public function findByRuleType(string $ruleType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.ruleType = :ruleType')
            ->setParameter('ruleType', $ruleType)
            ->orderBy('sr.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据策略和规则类型查找规则
     */
    public function findByStrategyAndType(VerificationStrategy $strategy, string $ruleType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.strategy = :strategy')
            ->andWhere('sr.ruleType = :ruleType')
            ->setParameter('strategy', $strategy)
            ->setParameter('ruleType', $ruleType)
            ->orderBy('sr.priority', 'DESC');

        return $qb->getQuery()->getResult();
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
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * 获取规则统计信息
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->select([
                'COUNT(sr.id) as total',
                'COUNT(CASE WHEN sr.isEnabled = true THEN 1 END) as enabled',
                'COUNT(CASE WHEN sr.isEnabled = false THEN 1 END) as disabled',
                'COUNT(DISTINCT sr.ruleType) as ruleTypes'
            ]);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * 根据条件参数值查找规则
     */
    public function findByConditionValue(string $conditionKey, mixed $value): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('JSON_EXTRACT(sr.conditions, :conditionPath) = :value')
            ->setParameter('conditionPath', '$.' . $conditionKey)
            ->setParameter('value', $value)
            ->orderBy('sr.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据动作类型查找规则
     */
    public function findByActionType(string $actionType): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('JSON_CONTAINS(sr.actions, :actionType, \'$[*].type\')')
            ->setParameter('actionType', json_encode($actionType))
            ->orderBy('sr.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找优先级范围内的规则
     */
    public function findByPriorityRange(int $minPriority, int $maxPriority): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.priority >= :minPriority')
            ->andWhere('sr.priority <= :maxPriority')
            ->setParameter('minPriority', $minPriority)
            ->setParameter('maxPriority', $maxPriority)
            ->orderBy('sr.priority', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取规则类型统计
     */
    public function getRuleTypeStatistics(): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->select([
                'sr.ruleType',
                'COUNT(sr.id) as total',
                'COUNT(CASE WHEN sr.isEnabled = true THEN 1 END) as enabled',
                'AVG(sr.priority) as avgPriority'
            ])
            ->groupBy('sr.ruleType')
            ->orderBy('sr.ruleType', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 批量更新规则启用状态
     */
    public function getUpdatedEnabledStatus(array $ruleIds, bool $enabled): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->update(StrategyRule::class, 'sr')
            ->set('sr.isEnabled', ':enabled')
            ->set('sr.updateTime', ':now')
            ->where('sr.id IN (:ids)')
            ->setParameter('enabled', $enabled)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('ids', $ruleIds);

        return $qb->getQuery()->execute();
    }
}
