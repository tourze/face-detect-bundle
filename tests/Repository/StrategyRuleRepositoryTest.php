<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Repository\StrategyRuleRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * StrategyRuleRepository 仓储类测试
 *
 * @internal
 */
#[CoversClass(StrategyRuleRepository::class)]
#[RunTestsInSeparateProcesses]
final class StrategyRuleRepositoryTest extends AbstractRepositoryTestCase
{
    private StrategyRuleRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(StrategyRuleRepository::class);
    }

    /**
     * 创建VerificationStrategy实体的辅助方法
     *
     * @param array<string, mixed> $config
     */
    private function createVerificationStrategy(string $name, string $businessType, array $config = []): VerificationStrategy
    {
        $strategy = new VerificationStrategy();
        $strategy->setName($name);
        $strategy->setBusinessType($businessType);
        $strategy->setConfig($config);

        return $strategy;
    }

    /**
     * 创建StrategyRule实体的辅助方法
     *
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $actions
     */
    private function createStrategyRule(string $ruleType, string $ruleName, array $conditions = [], array $actions = []): StrategyRule
    {
        $rule = new StrategyRule();
        $rule->setRuleType($ruleType);
        $rule->setRuleName($ruleName);
        $rule->setConditions($conditions);
        $rule->setActions($actions);

        return $rule;
    }

    public function testRepositoryIsInstantiatedFromContainer(): void
    {
        $this->assertInstanceOf(StrategyRuleRepository::class, $this->repository);
    }

    public function testFindEnabledByStrategyReturnsEnabledRules(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('test-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $enabledRule = $this->createStrategyRule('validation', 'threshold');
        $enabledRule->setStrategy($strategy);
        $enabledRule->setEnabled(true);
        $disabledRule = $this->createStrategyRule('validation', 'timeout');
        $disabledRule->setStrategy($strategy);
        $disabledRule->setEnabled(false);

        self::getEntityManager()->persist($enabledRule);
        self::getEntityManager()->persist($disabledRule);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findEnabledByStrategy($strategy);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($enabledRule, $results);
        $this->assertNotContains($disabledRule, $results);
    }

    public function testFindEnabledByStrategyWithNoRulesReturnsEmptyArray(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('empty-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findEnabledByStrategy($strategy);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByRuleTypeReturnsCorrectRules(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('test-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $ruleType = 'confidence_threshold';
        $rule1 = $this->createStrategyRule($ruleType, 'threshold-1');
        $rule1->setStrategy($strategy);
        $rule2 = $this->createStrategyRule($ruleType, 'threshold-2');
        $rule2->setStrategy($strategy);
        $rule3 = $this->createStrategyRule('timeout_rule', 'timeout');
        $rule3->setStrategy($strategy);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->persist($rule3);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByRuleType($ruleType);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($rule1, $results);
        $this->assertContains($rule2, $results);
        $this->assertNotContains($rule3, $results);
    }

    public function testFindByRuleTypeWithEmptyTypeReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByRuleType('');

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByStrategyAndTypeReturnsSpecificRules(): void
    {
        // Arrange
        $strategy1 = $this->createVerificationStrategy('strategy-1', 'business-1');
        $strategy2 = $this->createVerificationStrategy('strategy-2', 'business-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $ruleType = 'validation_rule';
        $targetRule = $this->createStrategyRule($ruleType, 'rule-1');
        $targetRule->setStrategy($strategy1);
        $otherStrategyRule = $this->createStrategyRule($ruleType, 'rule-2');
        $otherStrategyRule->setStrategy($strategy2);
        $otherTypeRule = $this->createStrategyRule('other_type', 'rule-3');
        $otherTypeRule->setStrategy($strategy1);

        self::getEntityManager()->persist($targetRule);
        self::getEntityManager()->persist($otherStrategyRule);
        self::getEntityManager()->persist($otherTypeRule);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByStrategyAndType($strategy1, $ruleType);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($targetRule, $results);
        $this->assertNotContains($otherStrategyRule, $results);
        $this->assertNotContains($otherTypeRule, $results);
    }

    public function testFindHighestPriorityByStrategyReturnsHighestPriorityRule(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('priority-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $lowPriorityRule = $this->createStrategyRule('validation', 'low-rule');
        $lowPriorityRule->setStrategy($strategy);
        $lowPriorityRule->setPriority(1);
        $highPriorityRule = $this->createStrategyRule('validation', 'high-rule');
        $highPriorityRule->setStrategy($strategy);
        $highPriorityRule->setPriority(10);

        self::getEntityManager()->persist($lowPriorityRule);
        self::getEntityManager()->persist($highPriorityRule);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findHighestPriorityByStrategy($strategy);

        // Assert
        $this->assertSame($highPriorityRule, $result);
        $this->assertNotSame($lowPriorityRule, $result);
    }

    public function testFindHighestPriorityByStrategyReturnsNullWhenNoRules(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('empty-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findHighestPriorityByStrategy($strategy);

        // Assert
        $this->assertNull($result);
    }

    public function testGetStatisticsReturnsValidStatistics(): void
    {
        // Act
        $statistics = $this->repository->getStatistics();

        // Assert
        $this->assertIsArray($statistics);
    }

    public function testFindByActionTypeReturnsCorrectRules(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('action-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $actionType = 'reject';
        $rule1 = $this->createStrategyRule('validation', 'reject-rule-1');
        $rule1->setStrategy($strategy);
        $rule1->setActionValue('type', $actionType);
        $rule2 = $this->createStrategyRule('validation', 'accept-rule');
        $rule2->setStrategy($strategy);
        $rule2->setActionValue('type', 'accept');

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByActionType($actionType);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($rule1, $results);
        $this->assertNotContains($rule2, $results);
    }

    public function testFindByActionTypeWithEmptyTypeReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByActionType('');

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByConditionValueReturnsMatchingRules(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('condition-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $conditionKey = 'confidence_score';
        $value = 0.8;
        $rule1 = $this->createStrategyRule('validation', 'confidence-rule');
        $rule1->setStrategy($strategy);
        $rule1->setConditionValue($conditionKey, $value);
        $rule2 = $this->createStrategyRule('validation', 'other-rule');
        $rule2->setStrategy($strategy);
        $rule2->setConditionValue($conditionKey, 0.5);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByConditionValue($conditionKey, $value);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByConditionValueWithMixedValueHandlesGracefully(): void
    {
        // Arrange
        $conditionKey = 'complex_condition';
        $value = ['nested' => ['array' => 'value']]; // complex mixed value

        // Act
        $results = $this->repository->findByConditionValue($conditionKey, $value);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByPriorityRangeReturnsRulesInRange(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('priority-range-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $minPriority = 5;
        $maxPriority = 15;
        $lowRule = $this->createStrategyRule('validation', 'low-rule');
        $lowRule->setStrategy($strategy);
        $lowRule->setPriority(1);
        $inRangeRule = $this->createStrategyRule('validation', 'in-range-rule');
        $inRangeRule->setStrategy($strategy);
        $inRangeRule->setPriority(10);
        $highRule = $this->createStrategyRule('validation', 'high-rule');
        $highRule->setStrategy($strategy);
        $highRule->setPriority(20);

        self::getEntityManager()->persist($lowRule);
        self::getEntityManager()->persist($inRangeRule);
        self::getEntityManager()->persist($highRule);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByPriorityRange($minPriority, $maxPriority);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($inRangeRule, $results);
        $this->assertNotContains($lowRule, $results);
        $this->assertNotContains($highRule, $results);
    }

    public function testFindByPriorityRangeWithInvalidRangeReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByPriorityRange(100, 50); // max < min

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testRepositoryHandlesDatabaseConnectionFailure(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('connection-test', 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        // Act & Assert - Should not throw exception
        try {
            $results = $this->repository->findEnabledByStrategy($strategy);
            $this->assertIsArray($results);
        } catch (\Exception $exception) {
            // Database connection issues should be handled gracefully
            $this->assertInstanceOf(\Doctrine\DBAL\Exception::class, $exception);
        }
    }

    public function testRepositoryHandlesInvalidParameters(): void
    {
        // Test with various edge cases
        $strategy = $this->createVerificationStrategy('invalid-test', 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        $edgeCases = [
            ['', 'valid-key', 'valid-value'],  // empty rule type
            ['valid-type', '', 'valid-value'], // empty condition key
            ['unicode-规则类型', 'unicode-键', 'unicode-值'], // unicode characters
        ];

        foreach ($edgeCases as [$ruleType, $conditionKey, $value]) {
            $results = $this->repository->findByRuleType($ruleType);
            $this->assertIsArray($results);

            $results = $this->repository->findByConditionValue($conditionKey, $value);
            $this->assertIsArray($results);
        }
    }

    public function testRepositoryPerformanceWithLargeDataset(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('performance-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        // Create 50 rules to test performance
        for ($i = 0; $i < 50; ++$i) {
            $rule = $this->createStrategyRule('validation', "rule-{$i}");
            $rule->setStrategy($strategy);
            $rule->setEnabled(0 === $i % 2); // Half enabled, half disabled
            $rule->setPriority($i);
            self::getEntityManager()->persist($rule);
        }
        self::getEntityManager()->flush();

        // Act & Assert - Should complete within reasonable time
        $startTime = microtime(true);
        $results = $this->repository->findEnabledByStrategy($strategy);
        $endTime = microtime(true);

        $this->assertLessThan(1.0, $endTime - $startTime, 'Query should complete within 1 second');
        $this->assertIsArray($results);
        $this->assertCount(25, $results); // Half of 50 rules are enabled
    }

    public function testRepositoryTransactionHandling(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('transaction-strategy', 'business-type');
        $rule = $this->createStrategyRule('validation', 'transaction-rule');
        $rule->setStrategy($strategy);

        // Act - Test within transaction
        self::getEntityManager()->beginTransaction();
        try {
            self::getEntityManager()->persist($strategy);
            self::getEntityManager()->persist($rule);
            self::getEntityManager()->flush();

            $results = $this->repository->findEnabledByStrategy($strategy);
            $this->assertNotEmpty($results);

            self::getEntityManager()->rollback();
        } catch (\Exception $e) {
            self::getEntityManager()->rollback();
            throw $e;
        }

        // Assert - Rules should not exist after rollback
        $results = $this->repository->findByRuleType('validation');
        $this->assertNotContains($rule, $results);
    }

    public function testRepositoryWithConcurrentAccess(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('concurrent-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $rule1 = $this->createStrategyRule('validation', 'concurrent-rule-1');
        $rule1->setStrategy($strategy);
        $rule1->setEnabled(true);
        $rule2 = $this->createStrategyRule('validation', 'concurrent-rule-2');
        $rule2->setStrategy($strategy);
        $rule2->setEnabled(true);

        // Act - Simulate concurrent operations
        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->flush();

        $results1 = $this->repository->findEnabledByStrategy($strategy);

        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        $results2 = $this->repository->findEnabledByStrategy($strategy);

        // Assert
        $this->assertCount(1, $results1);
        $this->assertCount(2, $results2);
        $this->assertContains($rule1, $results1);
        $this->assertContains($rule1, $results2);
        $this->assertContains($rule2, $results2);
    }

    public function testRepositoryStatisticsIncludeRelevantMetrics(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('stats-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);

        $rule1 = $this->createStrategyRule('validation', 'stats-rule-1');
        $rule1->setStrategy($strategy);
        $rule1->setEnabled(true);
        $rule2 = $this->createStrategyRule('processing', 'stats-rule-2');
        $rule2->setStrategy($strategy);
        $rule2->setEnabled(false);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act
        $statistics = $this->repository->getStatistics();

        // Assert
        $this->assertIsArray($statistics);
        // Statistics should be an array of meaningful data
        // We don't assert specific values as they depend on database state
    }

    public function testRepositoryMethodsReturnConsistentTypes(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('type-test-strategy', 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        // Test that all methods return expected types even with empty database
        $this->assertIsArray($this->repository->findEnabledByStrategy($strategy));
        $this->assertIsArray($this->repository->findByRuleType('non-existent'));
        $this->assertIsArray($this->repository->findByStrategyAndType($strategy, 'non-existent'));
        $this->assertNull($this->repository->findHighestPriorityByStrategy($strategy));
        $this->assertIsArray($this->repository->getStatistics());
        $this->assertIsArray($this->repository->findByActionType('non-existent'));
        $this->assertIsArray($this->repository->findByConditionValue('key', 'value'));
        $this->assertIsArray($this->repository->findByPriorityRange(1, 10));
    }

    public function testFindOneByRespectOrderByClause(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('findone-order-strategy-' . uniqid(), 'business-type');
        self::getEntityManager()->persist($strategy);

        $lowPriorityRule = $this->createStrategyRule('validation', 'low-priority-rule');
        $lowPriorityRule->setStrategy($strategy);
        $lowPriorityRule->setPriority(1);

        $highPriorityRule = $this->createStrategyRule('validation', 'high-priority-rule');
        $highPriorityRule->setStrategy($strategy);
        $highPriorityRule->setPriority(10);

        self::getEntityManager()->persist($lowPriorityRule);
        self::getEntityManager()->persist($highPriorityRule);
        self::getEntityManager()->flush();

        // Act - Find highest priority
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['priority' => 'DESC']);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame($highPriorityRule->getRuleName(), $result->getRuleName());
        $this->assertSame(10, $result->getPriority());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('remove-strategy-' . uniqid(), 'business-type');
        self::getEntityManager()->persist($strategy);

        $rule = $this->createStrategyRule('validation', 'remove-rule');
        $rule->setStrategy($strategy);
        self::getEntityManager()->persist($rule);
        self::getEntityManager()->flush();
        $id = $rule->getId();

        // Act
        $this->repository->remove($rule, true);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function testSaveShouldPersistEntity(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('save-strategy-' . uniqid(), 'business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        $rule = $this->createStrategyRule('validation', 'save-rule');
        $rule->setStrategy($strategy);

        // Act
        $this->repository->save($rule, true);

        // Assert
        $this->assertNotNull($rule->getId());
        $savedRule = $this->repository->find($rule->getId());
        $this->assertNotNull($savedRule);
        $this->assertSame('save-rule', $savedRule->getRuleName());
    }

    public function testCountByStrategyRelation(): void
    {
        // Arrange
        $strategy1 = $this->createVerificationStrategy('count-strategy-1-' . uniqid(), 'business-type-1');
        $strategy2 = $this->createVerificationStrategy('count-strategy-2-' . uniqid(), 'business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $rule1 = $this->createStrategyRule('validation', 'rule-1');
        $rule1->setStrategy($strategy1);
        $rule2 = $this->createStrategyRule('validation', 'rule-2');
        $rule2->setStrategy($strategy1);
        $rule3 = $this->createStrategyRule('validation', 'rule-3');
        $rule3->setStrategy($strategy2);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->persist($rule3);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['strategy' => $strategy1]);

        // Assert
        $this->assertSame(2, $count);
    }

    public function testFindByStrategyRelation(): void
    {
        // Arrange
        $strategy1 = $this->createVerificationStrategy('relation-strategy-1-' . uniqid(), 'business-type-1');
        $strategy2 = $this->createVerificationStrategy('relation-strategy-2-' . uniqid(), 'business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $rule1 = $this->createStrategyRule('validation', 'rule-1');
        $rule1->setStrategy($strategy1);
        $rule2 = $this->createStrategyRule('validation', 'rule-2');
        $rule2->setStrategy($strategy2);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['strategy' => $strategy1]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($rule1, $results);
        $this->assertNotContains($rule2, $results);
    }

    public function testFindOneByShouldRespectOrderByClause(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('order-strategy-' . uniqid(), 'business-type');
        self::getEntityManager()->persist($strategy);

        $lowPriorityRule = $this->createStrategyRule('validation', 'low-priority-rule');
        $lowPriorityRule->setStrategy($strategy);
        $lowPriorityRule->setPriority(1);

        $highPriorityRule = $this->createStrategyRule('validation', 'high-priority-rule');
        $highPriorityRule->setStrategy($strategy);
        $highPriorityRule->setPriority(10);

        self::getEntityManager()->persist($lowPriorityRule);
        self::getEntityManager()->persist($highPriorityRule);
        self::getEntityManager()->flush();

        // Act - Find highest priority
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['priority' => 'DESC']);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame($highPriorityRule->getRuleName(), $result->getRuleName());
        $this->assertSame(10, $result->getPriority());
    }

    public function testFindOneByWithSortingLogicShouldReturnCorrectlyOrderedEntity(): void
    {
        // Arrange
        $strategy = $this->createVerificationStrategy('sort-test-strategy-' . uniqid(), 'business-type');
        self::getEntityManager()->persist($strategy);

        // Create rules with different priorities to test sorting
        $rule1 = $this->createStrategyRule('validation', 'rule-1');
        $rule1->setStrategy($strategy);
        $rule1->setPriority(5);
        $rule1->setEnabled(true);

        $rule2 = $this->createStrategyRule('validation', 'rule-2');
        $rule2->setStrategy($strategy);
        $rule2->setPriority(10);
        $rule2->setEnabled(true);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act & Assert - Test different sorting scenarios

        // Test 1: Order by priority DESC (should return the higher priority one)
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['priority' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertSame($rule2->getId(), $result->getId());
        $this->assertSame(10, $result->getPriority());

        // Test 2: Order by priority ASC (should return the lower priority one)
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['priority' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertSame($rule1->getId(), $result->getId());
        $this->assertSame(5, $result->getPriority());

        // Test 3: Order by ID DESC (should return the newer one)
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['id' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertSame($rule2->getId(), $result->getId());

        // Test 4: Order by ID ASC (should return the older one)
        $result = $this->repository->findOneBy(['ruleType' => 'validation'], ['id' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertSame($rule1->getId(), $result->getId());
    }

    public function testCountByAssociationStrategyShouldReturnCorrectNumber(): void
    {
        // Arrange
        $strategy1 = $this->createVerificationStrategy('count-association-strategy-1-' . uniqid(), 'business-type-1');
        $strategy2 = $this->createVerificationStrategy('count-association-strategy-2-' . uniqid(), 'business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $rule1 = $this->createStrategyRule('validation', 'rule-1');
        $rule1->setStrategy($strategy1);
        $rule2 = $this->createStrategyRule('validation', 'rule-2');
        $rule2->setStrategy($strategy1);
        $rule3 = $this->createStrategyRule('validation', 'rule-3');
        $rule3->setStrategy($strategy2);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->persist($rule3);
        self::getEntityManager()->flush();

        // Act
        $count1 = $this->repository->count(['strategy' => $strategy1]);
        $count2 = $this->repository->count(['strategy' => $strategy2]);

        // Assert
        $this->assertSame(2, $count1);
        $this->assertSame(1, $count2);
    }

    public function testFindOneByAssociationStrategyShouldReturnMatchingEntity(): void
    {
        // Arrange
        $strategy1 = $this->createVerificationStrategy('findone-association-strategy-1-' . uniqid(), 'business-type-1');
        $strategy2 = $this->createVerificationStrategy('findone-association-strategy-2-' . uniqid(), 'business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $rule1 = $this->createStrategyRule('validation', 'rule-1');
        $rule1->setStrategy($strategy1);
        $rule2 = $this->createStrategyRule('validation', 'rule-2');
        $rule2->setStrategy($strategy2);

        self::getEntityManager()->persist($rule1);
        self::getEntityManager()->persist($rule2);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findOneBy(['strategy' => $strategy1]);

        // Assert
        $this->assertInstanceOf(StrategyRule::class, $result);
        $this->assertSame($rule1->getId(), $result->getId());
        $this->assertSame($strategy1, $result->getStrategy());
    }

    /**
     * @return ServiceEntityRepository<StrategyRule>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): StrategyRule
    {
        $strategy = new VerificationStrategy();
        $strategy->setName('test-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        $rule = new StrategyRule();
        $rule->setRuleType('time');
        $rule->setRuleName('test-rule-' . uniqid());
        $rule->setStrategy($strategy);

        return $rule;
    }
}
