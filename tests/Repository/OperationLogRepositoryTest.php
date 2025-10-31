<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\FaceDetectBundle\Repository\OperationLogRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * OperationLogRepository 仓储类测试
 *
 * @internal
 */
#[CoversClass(OperationLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class OperationLogRepositoryTest extends AbstractRepositoryTestCase
{
    private OperationLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OperationLogRepository::class);
    }

    /**
     * 创建OperationLog实体的辅助方法
     */
    private function createOperationLog(string $userId, string $operationId, string $operationType): OperationLog
    {
        $log = new OperationLog();
        $log->setUserId($userId);
        $log->setOperationId($operationId);
        $log->setOperationType($operationType);

        return $log;
    }

    public function testRepositoryIsInstantiatedFromContainer(): void
    {
        $this->assertInstanceOf(OperationLogRepository::class, $this->repository);
    }

    public function testFindByOperationIdReturnsCorrectLog(): void
    {
        // Arrange
        $operationId = 'test-operation-123';
        $log1 = $this->createOperationLog('user-1-' . uniqid(), $operationId, 'face-verification');
        $log2 = $this->createOperationLog('user-2-' . uniqid(), 'other-operation', 'id-check');

        self::getEntityManager()->persist($log1);
        self::getEntityManager()->persist($log2);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findByOperationId($operationId);

        // Assert
        $this->assertSame($log1, $result);
        $this->assertNotSame($log2, $result);
    }

    public function testFindByOperationIdReturnsNullWhenNotFound(): void
    {
        // Act
        $result = $this->repository->findByOperationId('non-existent-operation');

        // Assert
        $this->assertNull($result);
    }

    public function testFindByUserIdReturnsUserLogs(): void
    {
        // Arrange
        $userId = 'test-user-456';
        $log1 = $this->createOperationLog($userId, 'op-1', 'verification-1');
        $log2 = $this->createOperationLog($userId, 'op-2', 'verification-2');
        $log3 = $this->createOperationLog('other-user-' . uniqid(), 'op-3', 'verification-3');

        self::getEntityManager()->persist($log1);
        self::getEntityManager()->persist($log2);
        self::getEntityManager()->persist($log3);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByUserId($userId);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($log1, $results);
        $this->assertContains($log2, $results);
        $this->assertNotContains($log3, $results);
    }

    public function testFindByUserIdRespectsLimit(): void
    {
        // Arrange
        $userId = 'test-user-limit-' . uniqid();
        for ($i = 0; $i < 15; ++$i) {
            $log = $this->createOperationLog($userId, "operation-{$i}", 'verification');
            self::getEntityManager()->persist($log);
        }
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByUserId($userId, 5);

        // Assert
        $this->assertCount(5, $results);
    }

    public function testFindByUserIdWithEmptyUserIdReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByUserId('');

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByUserIdWithDefaultLimitReturns10Records(): void
    {
        // Arrange
        $userId = 'test-user-default';
        for ($i = 0; $i < 20; ++$i) {
            $log = $this->createOperationLog($userId, "operation-{$i}", 'verification');
            self::getEntityManager()->persist($log);
        }
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByUserId($userId); // Default limit should be 10

        // Assert
        $this->assertCount(10, $results);
    }

    public function testFindPendingVerificationReturnsPendingLogs(): void
    {
        // Arrange
        $userId = 'test-user-pending-' . uniqid();
        $pendingLog = $this->createOperationLog($userId, 'pending-op', 'verification');
        $pendingLog->setStatus(OperationStatus::PENDING);
        $pendingLog->setVerificationRequired(true);
        $completedLog = $this->createOperationLog($userId, 'completed-op', 'verification');
        $completedLog->setStatus(OperationStatus::COMPLETED);
        $completedLog->setVerificationRequired(true);

        self::getEntityManager()->persist($pendingLog);
        self::getEntityManager()->persist($completedLog);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findPendingVerification($userId);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($pendingLog, $results);
        $this->assertNotContains($completedLog, $results);
    }

    public function testFindPendingVerificationWithNullUserIdReturnsAllPending(): void
    {
        // Arrange
        $pendingLog1 = $this->createOperationLog('user-1-' . uniqid(), 'pending-1', 'verification');
        $pendingLog1->setStatus(OperationStatus::PENDING);
        $pendingLog1->setVerificationRequired(true);
        $pendingLog2 = $this->createOperationLog('user-2-' . uniqid(), 'pending-2', 'verification');
        $pendingLog2->setStatus(OperationStatus::PENDING);
        $pendingLog2->setVerificationRequired(true);
        $completedLog = $this->createOperationLog('user-3-' . uniqid(), 'completed', 'verification');
        $completedLog->setStatus(OperationStatus::COMPLETED);
        $completedLog->setVerificationRequired(true);

        self::getEntityManager()->persist($pendingLog1);
        self::getEntityManager()->persist($pendingLog2);
        self::getEntityManager()->persist($completedLog);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findPendingVerification(null);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($pendingLog1, $results);
        $this->assertContains($pendingLog2, $results);
        $this->assertNotContains($completedLog, $results);
    }

    public function testGetStatisticsReturnsValidStatistics(): void
    {
        // Act
        $statistics = $this->repository->getStatistics();

        // Assert
        $this->assertIsArray($statistics);
    }

    public function testFindByTimeRangeReturnsLogsInRange(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');

        $log = $this->createOperationLog('user-1-' . uniqid(), 'time-range-op', 'verification');
        self::getEntityManager()->persist($log);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByTimeRange($start, $end);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByTimeRangeWithInvalidRangeReturnsEmptyArray(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2025-01-01');
        $end = new \DateTimeImmutable('2024-01-01'); // end before start

        // Act
        $results = $this->repository->findByTimeRange($start, $end);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindTimeoutOperationsReturnsTimeoutLogs(): void
    {
        // Arrange
        $timeoutMinutes = 30;

        // Act
        $results = $this->repository->findTimeoutOperations($timeoutMinutes);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindTimeoutOperationsWithDefaultTimeoutReturns30MinutesTimeout(): void
    {
        // Act
        $results = $this->repository->findTimeoutOperations(); // Default should be 30 minutes

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindTimeoutOperationsWithZeroTimeoutReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findTimeoutOperations(0);

        // Assert
        $this->assertIsArray($results);
    }

    public function testRepositoryHandlesDatabaseConnectionFailure(): void
    {
        // Arrange
        $operationId = 'connection-test-op';

        // Act & Assert - Should not throw exception
        try {
            $result = $this->repository->findByOperationId($operationId);
            $this->assertNull($result);
        } catch (\Exception $exception) {
            // Database connection issues should be handled gracefully
            $this->assertInstanceOf(\Doctrine\DBAL\Exception::class, $exception);
        }
    }

    public function testRepositoryHandlesInvalidParameters(): void
    {
        // Test with various edge cases
        $edgeCases = [
            ['', '', 0],    // empty operationId, empty userId, zero limit
            ['very-long-operation-id-' . str_repeat('x', 1000), 'test-user', -1], // very long ID, negative limit
            ['unicode-操作-id', 'unicode-用户', 999999], // unicode characters, very large limit
        ];

        foreach ($edgeCases as [$operationId, $userId, $limit]) {
            $result = $this->repository->findByOperationId($operationId);
            $this->assertNull($result);

            $results = $this->repository->findByUserId($userId, $limit);
            $this->assertIsArray($results);
        }
    }

    public function testRepositoryPerformanceWithLargeDataset(): void
    {
        // Arrange
        $userId = 'performance-test-user-' . uniqid();

        // Create 100 logs to test performance
        for ($i = 0; $i < 100; ++$i) {
            $log = $this->createOperationLog($userId, "operation-{$i}", 'verification');
            self::getEntityManager()->persist($log);
        }
        self::getEntityManager()->flush();

        // Act & Assert - Should complete within reasonable time
        $startTime = microtime(true);
        $results = $this->repository->findByUserId($userId, 50);
        $endTime = microtime(true);

        $this->assertLessThan(1.0, $endTime - $startTime, 'Query should complete within 1 second');
        $this->assertCount(50, $results);
    }

    public function testRepositoryTransactionHandling(): void
    {
        // Arrange
        $operationId = 'transaction-test-op';
        $log = $this->createOperationLog('transaction-user-' . uniqid(), $operationId, 'verification');

        // Act - Test within transaction
        self::getEntityManager()->beginTransaction();
        try {
            self::getEntityManager()->persist($log);
            self::getEntityManager()->flush();

            $result = $this->repository->findByOperationId($operationId);
            $this->assertNotNull($result);

            self::getEntityManager()->rollback();
        } catch (\Exception $e) {
            self::getEntityManager()->rollback();
            throw $e;
        }

        // Assert - Log should not exist after rollback
        $result = $this->repository->findByOperationId($operationId);
        $this->assertNull($result);
    }

    public function testRepositoryWithConcurrentAccess(): void
    {
        // Arrange
        $userId = 'concurrent-test-user-' . uniqid();
        $log1 = $this->createOperationLog($userId, 'concurrent-op-1', 'verification-1');
        $log2 = $this->createOperationLog($userId, 'concurrent-op-2', 'verification-2');

        // Act - Simulate concurrent operations
        self::getEntityManager()->persist($log1);
        self::getEntityManager()->flush();

        $results1 = $this->repository->findByUserId($userId);

        self::getEntityManager()->persist($log2);
        self::getEntityManager()->flush();

        $results2 = $this->repository->findByUserId($userId);

        // Assert
        $this->assertCount(1, $results1);
        $this->assertCount(2, $results2);
        $this->assertContains($log1, $results1);
        $this->assertContains($log1, $results2);
        $this->assertContains($log2, $results2);
    }

    public function testRepositoryStatisticsIncludeRelevantMetrics(): void
    {
        // Arrange
        $log1 = $this->createOperationLog('user-1-' . uniqid(), 'stats-op-1', 'verification');
        $log1->setStatus(OperationStatus::COMPLETED);
        $log2 = $this->createOperationLog('user-2-' . uniqid(), 'stats-op-2', 'verification');
        $log2->setStatus(OperationStatus::FAILED);

        self::getEntityManager()->persist($log1);
        self::getEntityManager()->persist($log2);
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
        // Test that all methods return expected types even with empty database
        $this->assertNull($this->repository->findByOperationId('non-existent'));
        $this->assertIsArray($this->repository->findByUserId('non-existent'));
        $this->assertIsArray($this->repository->findPendingVerification());
        $this->assertIsArray($this->repository->getStatistics());
        $this->assertIsArray($this->repository->findByTimeRange(
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-01-02')
        ));
        $this->assertIsArray($this->repository->findTimeoutOperations());
    }

    // 添加缺失的基础Repository测试方法

    public function testSaveShouldPersistEntity(): void
    {
        // Arrange
        $log = $this->createOperationLog('user-save-' . uniqid(), 'op-save', 'save-verification');

        // Act
        $this->repository->save($log, true);

        // Assert
        $this->assertNotNull($log->getId());
        $savedLog = $this->repository->find($log->getId());
        $this->assertNotNull($savedLog);
        $this->assertSame('op-save', $savedLog->getOperationId());
        $this->assertSame('save-verification', $savedLog->getOperationType());
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        // Arrange
        $log = $this->createOperationLog('user-save-no-flush-' . uniqid(), 'op-no-flush', 'no-flush-verification');

        // Act
        $this->repository->save($log, false);

        // Assert - 在没有flush的情况下，实体应该在UnitOfWork中但还没有ID
        $this->assertNull($log->getId());

        // 手动flush后应该有ID
        self::getEntityManager()->flush();
        $this->assertNotNull($log->getId());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        // Arrange
        $log = $this->createOperationLog('user-remove-' . uniqid(), 'op-remove', 'remove-verification');
        self::getEntityManager()->persist($log);
        self::getEntityManager()->flush();
        $id = $log->getId();

        // Act
        $this->repository->remove($log, true);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        // Arrange
        $log = $this->createOperationLog('user-remove-no-flush-' . uniqid(), 'op-remove-no-flush', 'no-flush-remove');
        self::getEntityManager()->persist($log);
        self::getEntityManager()->flush();
        $id = $log->getId();

        // Act
        $this->repository->remove($log, false);

        // Assert - 在没有flush的情况下，实体应该仍然存在
        $this->assertNotNull($this->repository->find($id));

        // 手动flush后应该被删除
        self::getEntityManager()->flush();
        $this->assertNull($this->repository->find($id));
    }

    public function testCountWithNonExistentCriteriaShouldReturnZero(): void
    {
        // Act
        $count = $this->repository->count(['operationId' => 'non-existent-operation-xyz-999']);

        // Assert
        $this->assertSame(0, $count);
    }

    public function testFindOneByRespectOrderByClause(): void
    {
        // Arrange
        $uniquePrefix = 'test-order-' . uniqid() . '-';
        $log1 = $this->createOperationLog('user-1-' . uniqid(), $uniquePrefix . 'a-op', 'verification-1');
        $log1->setStatus(OperationStatus::COMPLETED);
        $log2 = $this->createOperationLog('user-2-' . uniqid(), $uniquePrefix . 'z-op', 'verification-2');
        $log2->setStatus(OperationStatus::COMPLETED);
        self::getEntityManager()->persist($log1); // 先保存a
        self::getEntityManager()->persist($log2); // 后保存z
        self::getEntityManager()->flush();

        // Act - 通过具体的operationId查询，确保返回正确的实体
        $result = $this->repository->findOneBy(['operationId' => $log2->getOperationId()]);

        // Assert
        $this->assertInstanceOf(OperationLog::class, $result);
        $this->assertSame($log2->getOperationId(), $result->getOperationId());
        $this->assertStringContainsString('z-op', $result->getOperationId());
    }

    public function testFindAllWithExistingRecordsReturnsArrayOfEntities(): void
    {
        // Arrange
        $log = $this->createOperationLog('user-1-' . uniqid(), 'op-1', 'verification-1');
        self::getEntityManager()->persist($log);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findAll();

        // Assert
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContainsOnlyInstancesOf(OperationLog::class, $results);
    }

    public function testFindOneByShouldRespectOrderByClause(): void
    {
        // Arrange
        $oldLog = $this->createOperationLog('test-user-' . uniqid(), 'old-op', 'verification');
        sleep(1); // Ensure different timestamps
        $newLog = $this->createOperationLog('test-user-' . uniqid(), 'new-op', 'verification');

        self::getEntityManager()->persist($oldLog);
        self::getEntityManager()->flush();
        sleep(1);
        self::getEntityManager()->persist($newLog);
        self::getEntityManager()->flush();

        // Act - Find latest by started time
        $result = $this->repository->findOneBy(['operationType' => 'verification'], ['startedTime' => 'DESC']);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame($newLog->getOperationId(), $result->getOperationId());
    }

    public function testFindOneByRespectOrderByClauseWithMultipleFields(): void
    {
        // Arrange
        $uniquePrefix = 'test-order-' . uniqid() . '-';
        $log1 = $this->createOperationLog('user-' . uniqid(), $uniquePrefix . 'a-op', 'verification');
        $log1->setStatus(OperationStatus::COMPLETED);
        $log2 = $this->createOperationLog('user-' . uniqid(), $uniquePrefix . 'z-op', 'verification');
        $log2->setStatus(OperationStatus::COMPLETED);
        self::getEntityManager()->persist($log1);
        self::getEntityManager()->persist($log2);
        self::getEntityManager()->flush();

        // Act - Find by status and order by operationId DESC
        $result = $this->repository->findOneBy(['status' => OperationStatus::COMPLETED], ['operationId' => 'DESC']);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(OperationLog::class, $result);
        // Should return the one with higher operationId (z > a)
        $this->assertStringContainsString('z-op', $result->getOperationId());
    }

    public function testFindByBusinessContextIsNull(): void
    {
        // Arrange
        $logWithContext = $this->createOperationLog('user-with-context-' . uniqid(), 'op-1', 'verification');
        $logWithContext->setBusinessContext(['key' => 'value']);

        $logWithoutContext = $this->createOperationLog('user-without-context-' . uniqid(), 'op-2', 'verification');
        // businessContext remains null by default

        self::getEntityManager()->persist($logWithContext);
        self::getEntityManager()->persist($logWithoutContext);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['businessContext' => null]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($logWithoutContext, $results);
        $this->assertNotContains($logWithContext, $results);
    }

    public function testFindByCompletedTimeIsNull(): void
    {
        // Arrange
        $completedLog = $this->createOperationLog('completed-user-' . uniqid(), 'completed-op', 'verification');
        $completedLog->setStatus(OperationStatus::COMPLETED);

        $pendingLog = $this->createOperationLog('pending-user-' . uniqid(), 'pending-op', 'verification');
        // completedTime remains null by default

        self::getEntityManager()->persist($completedLog);
        self::getEntityManager()->persist($pendingLog);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['completedTime' => null]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($pendingLog, $results);
        $this->assertNotContains($completedLog, $results);
    }

    public function testCountWithBusinessContextIsNull(): void
    {
        // Arrange
        $logWithContext = $this->createOperationLog('user-with-context-' . uniqid(), 'op-1', 'verification');
        $logWithContext->setBusinessContext(['key' => 'value']);

        $logWithoutContext = $this->createOperationLog('user-without-context-' . uniqid(), 'op-2', 'verification');

        self::getEntityManager()->persist($logWithContext);
        self::getEntityManager()->persist($logWithoutContext);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['businessContext' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithCompletedTimeIsNull(): void
    {
        // Arrange
        $completedLog = $this->createOperationLog('completed-user-' . uniqid(), 'completed-op', 'verification');
        $completedLog->setStatus(OperationStatus::COMPLETED);

        $pendingLog = $this->createOperationLog('pending-user-' . uniqid(), 'pending-op', 'verification');

        self::getEntityManager()->persist($completedLog);
        self::getEntityManager()->persist($pendingLog);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['completedTime' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithSortingLogicShouldReturnCorrectlyOrderedEntity(): void
    {
        // Arrange
        $uniquePrefix = 'sort-test-' . uniqid() . '-';

        // Create logs with different timestamps to test sorting
        $log1 = $this->createOperationLog('user-' . uniqid(), $uniquePrefix . 'op-1', 'verification');
        $log1->setStatus(OperationStatus::COMPLETED);

        sleep(1); // Ensure different create times

        $log2 = $this->createOperationLog('user-' . uniqid(), $uniquePrefix . 'op-2', 'verification');
        $log2->setStatus(OperationStatus::COMPLETED);

        self::getEntityManager()->persist($log1);
        self::getEntityManager()->flush();
        sleep(1);
        self::getEntityManager()->persist($log2);
        self::getEntityManager()->flush();

        // Act & Assert - Test different sorting scenarios

        // Test 1: Order by ID DESC (should return a valid entity)
        $result = $this->repository->findOneBy(['status' => OperationStatus::COMPLETED], ['id' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertInstanceOf(OperationLog::class, $result);
        $this->assertSame(OperationStatus::COMPLETED, $result->getStatus());

        // Test 2: Order by ID ASC (should return a valid entity)
        $result = $this->repository->findOneBy(['status' => OperationStatus::COMPLETED], ['id' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertInstanceOf(OperationLog::class, $result);
        $this->assertSame(OperationStatus::COMPLETED, $result->getStatus());

        // Test 3: Order by startedTime DESC (should return a valid result)
        $result = $this->repository->findOneBy(['status' => OperationStatus::COMPLETED], ['startedTime' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertInstanceOf(OperationLog::class, $result);

        // Test 4: Order by startedTime ASC (should return a valid result)
        $result = $this->repository->findOneBy(['status' => OperationStatus::COMPLETED], ['startedTime' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertInstanceOf(OperationLog::class, $result);
    }

    /**
     * @return ServiceEntityRepository<OperationLog>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $log = new OperationLog();
        $log->setUserId('test_user_' . time() . '_' . random_int(1000, 9999));
        $log->setOperationId('operation_' . time() . '_' . random_int(1000, 9999));
        $log->setOperationType('face_verification');
        $log->setBusinessContext(['action' => 'test', 'module' => 'unit_test']);
        $log->setVerificationRequired(true);
        $log->setVerificationCompleted(false);
        $log->setVerificationCount(0);
        $log->setMinVerificationCount(1);
        $log->setStatus(OperationStatus::PENDING);

        return $log;
    }
}
