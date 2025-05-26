<?php

namespace Tourze\FaceDetectBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * 策略规则仓储类
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
} 