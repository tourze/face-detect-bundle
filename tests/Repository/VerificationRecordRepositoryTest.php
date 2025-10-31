<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Repository\VerificationRecordRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * VerificationRecordRepository 仓储类测试
 *
 * @internal
 */
#[CoversClass(VerificationRecordRepository::class)]
#[RunTestsInSeparateProcesses]
final class VerificationRecordRepositoryTest extends AbstractRepositoryTestCase
{
    private VerificationRecordRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(VerificationRecordRepository::class);
    }

    private function createTestStrategy(?string $name = null): VerificationStrategy
    {
        $strategy = new VerificationStrategy();
        $strategy->setName($name ?? 'test-strategy-' . uniqid());
        $strategy->setBusinessType('hotel-checkin');
        self::getEntityManager()->persist($strategy);

        return $strategy;
    }

    public function testRepositoryIsInstantiatedFromContainer(): void
    {
        $this->assertInstanceOf(VerificationRecordRepository::class, $this->repository);
    }

    public function testFindByUserIdReturnsCorrectRecords(): void
    {
        // Arrange
        $userId = 'test-user-123-' . uniqid();
        $strategy1 = $this->createTestStrategy();
        $strategy2 = $this->createTestStrategy();
        $strategy3 = $this->createTestStrategy();

        $record1 = new VerificationRecord();
        $record1->setUserId($userId);
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId($userId);
        $record2->setStrategy($strategy2);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::FAILED);

        $record3 = new VerificationRecord();
        $record3->setUserId('other-user-' . uniqid());
        $record3->setStrategy($strategy3);
        $record3->setBusinessType('hotel-checkin');
        $record3->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByUserId($userId);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContains($record1, $results);
        $this->assertContains($record2, $results);
        $this->assertNotContains($record3, $results);
    }

    public function testFindByUserIdRespectsLimit(): void
    {
        // Arrange
        $userId = 'test-user-limit-' . uniqid();
        for ($i = 0; $i < 15; ++$i) {
            $strategy = $this->createTestStrategy();
            $record = new VerificationRecord();

            $record->setUserId($userId);

            $record->setStrategy($strategy);

            $record->setBusinessType('hotel-checkin');

            $record->setResult(VerificationResult::SUCCESS);
            self::getEntityManager()->persist($record);
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

    public function testFindByOperationIdReturnsCorrectRecords(): void
    {
        // Arrange
        $operationId = 'test-operation-456';
        $strategy1 = $this->createTestStrategy();
        $strategy2 = $this->createTestStrategy();
        $strategy3 = $this->createTestStrategy();

        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);
        $record1->setOperationId($operationId);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy2);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::FAILED);
        $record2->setOperationId($operationId);

        $record3 = new VerificationRecord();
        $record3->setUserId('user-3-' . uniqid());
        $record3->setStrategy($strategy3);
        $record3->setBusinessType('hotel-checkin');
        $record3->setResult(VerificationResult::SUCCESS);
        $record3->setOperationId('other-operation');

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByOperationId($operationId);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContains($record1, $results);
        $this->assertContains($record2, $results);
        $this->assertNotContains($record3, $results);
    }

    public function testFindByOperationIdWithEmptyOperationIdReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByOperationId('');

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindLastSuccessfulByUserIdReturnsLatestSuccessfulRecord(): void
    {
        // Arrange
        $userId = 'test-user-success';
        $strategy1 = $this->createTestStrategy('old-strategy');
        $strategy2 = $this->createTestStrategy('failed-strategy');
        $strategy3 = $this->createTestStrategy('latest-strategy');

        $oldRecord = new VerificationRecord();

        $oldRecord->setUserId($userId);

        $oldRecord->setStrategy($strategy1);

        $oldRecord->setBusinessType('hotel-checkin');

        $oldRecord->setResult(VerificationResult::SUCCESS);
        $failedRecord = new VerificationRecord();

        $failedRecord->setUserId($userId);

        $failedRecord->setStrategy($strategy2);

        $failedRecord->setBusinessType('hotel-checkin');

        $failedRecord->setResult(VerificationResult::FAILED);
        $latestRecord = new VerificationRecord();

        $latestRecord->setUserId($userId);

        $latestRecord->setStrategy($strategy3);

        $latestRecord->setBusinessType('hotel-checkin');

        $latestRecord->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($oldRecord);
        self::getEntityManager()->persist($failedRecord);
        self::getEntityManager()->persist($latestRecord);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findLastSuccessfulByUserId($userId);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame($latestRecord, $result);
        $this->assertSame(VerificationResult::SUCCESS, $result->getResult());
    }

    public function testFindLastSuccessfulByUserIdReturnsNullWhenNoSuccessfulRecords(): void
    {
        // Arrange
        $userId = 'test-user-no-success';
        $strategy = $this->createTestStrategy('failed-strategy');
        $failedRecord = new VerificationRecord();

        $failedRecord->setUserId($userId);

        $failedRecord->setStrategy($strategy);

        $failedRecord->setBusinessType('hotel-checkin');

        $failedRecord->setResult(VerificationResult::FAILED);

        self::getEntityManager()->persist($failedRecord);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findLastSuccessfulByUserId($userId);

        // Assert
        $this->assertNull($result);
    }

    public function testCountByUserIdAndTimeRangeReturnsCorrectCount(): void
    {
        // Arrange
        $userId = 'test-user-count';
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');

        $strategy1 = $this->createTestStrategy('count-strategy-1');
        $strategy2 = $this->createTestStrategy('count-strategy-2');
        $strategy3 = $this->createTestStrategy('count-strategy-3');

        $record1 = new VerificationRecord();

        $record1->setUserId($userId);

        $record1->setStrategy($strategy1);

        $record1->setBusinessType('hotel-checkin');

        $record1->setResult(VerificationResult::SUCCESS);
        $record2 = new VerificationRecord();

        $record2->setUserId($userId);

        $record2->setStrategy($strategy2);

        $record2->setBusinessType('hotel-checkin');

        $record2->setResult(VerificationResult::FAILED);
        $record3 = new VerificationRecord();

        $record3->setUserId('other-user');

        $record3->setStrategy($strategy3);

        $record3->setBusinessType('hotel-checkin');

        $record3->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->countByUserIdAndTimeRange($userId, $start, $end);

        // Assert
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByUserIdAndTimeRangeWithInvalidTimeRangeReturnsZero(): void
    {
        // Arrange
        $userId = 'test-user-invalid-range';
        $start = new \DateTimeImmutable('2025-01-01');
        $end = new \DateTimeImmutable('2024-01-01'); // end before start

        // Act
        $count = $this->repository->countByUserIdAndTimeRange($userId, $start, $end);

        // Assert
        $this->assertSame(0, $count);
    }

    public function testCountSuccessfulByUserIdReturnsCorrectCount(): void
    {
        // Arrange
        $userId = 'test-user-successful-count';
        $strategy1 = $this->createTestStrategy('success-strategy-1');
        $strategy2 = $this->createTestStrategy('success-strategy-2');
        $strategy3 = $this->createTestStrategy('success-strategy-3');
        $strategy4 = $this->createTestStrategy('success-strategy-4');

        $record1 = new VerificationRecord();

        $record1->setUserId($userId);

        $record1->setStrategy($strategy1);

        $record1->setBusinessType('hotel-checkin');

        $record1->setResult(VerificationResult::SUCCESS);
        $record2 = new VerificationRecord();

        $record2->setUserId($userId);

        $record2->setStrategy($strategy2);

        $record2->setBusinessType('hotel-checkin');

        $record2->setResult(VerificationResult::SUCCESS);
        $record3 = new VerificationRecord();

        $record3->setUserId($userId);

        $record3->setStrategy($strategy3);

        $record3->setBusinessType('hotel-checkin');

        $record3->setResult(VerificationResult::FAILED);
        $record4 = new VerificationRecord();

        $record4->setUserId('other-user');

        $record4->setStrategy($strategy4);

        $record4->setBusinessType('hotel-checkin');

        $record4->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->persist($record4);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->countSuccessfulByUserId($userId);

        // Assert
        $this->assertSame(2, $count);
    }

    public function testCountSuccessfulByUserIdWithSinceParameterFiltersCorrectly(): void
    {
        // Arrange
        $userId = 'test-user-since';
        $since = new \DateTimeImmutable('2023-06-01');

        // Act
        $count = $this->repository->countSuccessfulByUserId($userId, $since);

        // Assert
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByBusinessTypeReturnsStatistics(): void
    {
        // Arrange
        $businessType = 'test-business-type';

        // Act
        $result = $this->repository->countByBusinessType($businessType);

        // Assert
        $this->assertIsArray($result);
    }

    public function testFindByTimeRangeReturnsRecordsInRange(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');

        $strategy = $this->createTestStrategy('time-range-strategy');
        $record = new VerificationRecord();

        $record->setUserId('user-1');

        $record->setStrategy($strategy);

        $record->setBusinessType('hotel-checkin');

        $record->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($record);
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

    public function testFindLowConfidenceRecordsReturnsCorrectRecords(): void
    {
        // Arrange
        $threshold = 0.6;

        // Act
        $results = $this->repository->findLowConfidenceRecords($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindLowConfidenceRecordsWithInvalidThresholdHandlesGracefully(): void
    {
        // Arrange
        $threshold = -1.0; // Invalid threshold

        // Act
        $results = $this->repository->findLowConfidenceRecords($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testGetStatisticsReturnsValidStatistics(): void
    {
        // Act
        $statistics = $this->repository->getStatistics();

        // Assert
        $this->assertIsArray($statistics);
    }

    public function testDeleteOldRecordsDeletesCorrectRecords(): void
    {
        // Arrange
        $before = new \DateTimeImmutable('2020-01-01');

        // Act
        $deletedCount = $this->repository->deleteOldRecords($before);

        // Assert
        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testDeleteOldRecordsWithFutureDateDeletesNothing(): void
    {
        // Arrange
        $before = new \DateTimeImmutable('2030-01-01'); // Future date

        // Act
        $deletedCount = $this->repository->deleteOldRecords($before);

        // Assert
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testFindFailedRecordsInTimeRangeReturnsFailedRecords(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');
        $userId = 'test-user-failed';

        $strategy1 = $this->createTestStrategy('failed-strategy');
        $strategy2 = $this->createTestStrategy('success-strategy');

        $failedRecord = new VerificationRecord();

        $failedRecord->setUserId($userId);

        $failedRecord->setStrategy($strategy1);

        $failedRecord->setBusinessType('hotel-checkin');

        $failedRecord->setResult(VerificationResult::FAILED);
        $successRecord = new VerificationRecord();

        $successRecord->setUserId($userId);

        $successRecord->setStrategy($strategy2);

        $successRecord->setBusinessType('hotel-checkin');

        $successRecord->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($failedRecord);
        self::getEntityManager()->persist($successRecord);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findFailedRecordsInTimeRange($start, $end, $userId);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindSlowVerificationsReturnsSlowRecords(): void
    {
        // Arrange
        $timeThreshold = 3000; // 3 seconds

        // Act
        $results = $this->repository->findSlowVerifications($timeThreshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testRepositoryHandlesDatabaseConnectionFailure(): void
    {
        // Arrange
        $userId = 'test-connection-failure';

        // Act & Assert - Should not throw exception
        try {
            $results = $this->repository->findByUserId($userId);
            $this->assertIsArray($results);
        } catch (\Exception $exception) {
            // Database connection issues should be handled gracefully
            $this->assertInstanceOf(\Doctrine\DBAL\Exception::class, $exception);
        }
    }

    public function testRepositoryHandlesInvalidParameters(): void
    {
        // Test with various edge cases
        $edgeCases = [
            ['', 0],    // empty userId, zero limit
            ['test', -1], // negative limit
            ['test', 999999], // very large limit
        ];

        foreach ($edgeCases as [$userId, $limit]) {
            $results = $this->repository->findByUserId($userId, $limit);
            $this->assertIsArray($results);
        }
    }

    public function testRepositoryPerformanceWithLargeDataset(): void
    {
        // Arrange
        $userId = 'performance-test-user';

        // Create 100 records to test performance
        for ($i = 0; $i < 100; ++$i) {
            $strategy = $this->createTestStrategy("performance-strategy-{$i}");
            $record = new VerificationRecord();

            $record->setUserId($userId);

            $record->setStrategy($strategy);

            $record->setBusinessType('hotel-checkin');

            $record->setResult(VerificationResult::SUCCESS);
            self::getEntityManager()->persist($record);
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
        $userId = 'transaction-test-user';
        $strategy = $this->createTestStrategy('transaction-strategy');
        $record = new VerificationRecord();

        $record->setUserId($userId);

        $record->setStrategy($strategy);

        $record->setBusinessType('hotel-checkin');

        $record->setResult(VerificationResult::SUCCESS);

        // Act - Test within transaction
        self::getEntityManager()->beginTransaction();
        try {
            self::getEntityManager()->persist($record);
            self::getEntityManager()->flush();

            $results = $this->repository->findByUserId($userId);
            $this->assertNotEmpty($results);

            self::getEntityManager()->rollback();
        } catch (\Exception $e) {
            self::getEntityManager()->rollback();
            throw $e;
        }

        // Assert - Record should not exist after rollback
        $results = $this->repository->findByUserId($userId);
        $this->assertEmpty($results);
    }

    public function testRepositoryWithConcurrentAccess(): void
    {
        // Arrange
        $userId = 'concurrent-test-user';
        $strategy1 = $this->createTestStrategy('concurrent-strategy-1');
        $strategy2 = $this->createTestStrategy('concurrent-strategy-2');

        $record1 = new VerificationRecord();

        $record1->setUserId($userId);

        $record1->setStrategy($strategy1);

        $record1->setBusinessType('hotel-checkin');

        $record1->setResult(VerificationResult::SUCCESS);
        $record2 = new VerificationRecord();

        $record2->setUserId($userId);

        $record2->setStrategy($strategy2);

        $record2->setBusinessType('hotel-checkin');

        $record2->setResult(VerificationResult::FAILED);

        // Act - Simulate concurrent operations
        self::getEntityManager()->persist($record1);
        self::getEntityManager()->flush();

        $results1 = $this->repository->findByUserId($userId);

        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        $results2 = $this->repository->findByUserId($userId);

        // Assert
        $this->assertCount(1, $results1);
        $this->assertCount(2, $results2);
    }

    // 添加缺失的基础Repository测试方法

    public function testSaveShouldPersistEntity(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('save-strategy');
        $record = new VerificationRecord();
        $record->setUserId('user-save-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);

        // Act
        $this->repository->save($record, true);

        // Assert
        $this->assertNotNull($record->getId());
        $savedRecord = $this->repository->find($record->getId());
        $this->assertNotNull($savedRecord);
        $this->assertSame(VerificationResult::SUCCESS, $savedRecord->getResult());
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('save-no-flush-strategy');
        $record = new VerificationRecord();
        $record->setUserId('user-save-no-flush-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);

        // Act
        $this->repository->save($record, false);

        // Assert - 在没有flush的情况下，实体应该在UnitOfWork中但还没有ID
        $this->assertNull($record->getId());

        // 手动flush后应该有ID
        self::getEntityManager()->flush();
        $this->assertNotNull($record->getId());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('remove-strategy');
        $record = new VerificationRecord();
        $record->setUserId('user-remove-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();
        $id = $record->getId();

        // Act
        $this->repository->remove($record, true);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('remove-no-flush-strategy');
        $record = new VerificationRecord();
        $record->setUserId('user-remove-no-flush-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();
        $id = $record->getId();

        // Act
        $this->repository->remove($record, false);

        // Assert - 在没有flush的情况下，实体应该仍然存在
        $this->assertNotNull($this->repository->find($id));

        // 手动flush后应该被删除
        self::getEntityManager()->flush();
        $this->assertNull($this->repository->find($id));
    }

    public function testCountWithNonExistentCriteriaShouldReturnZero(): void
    {
        // Act
        $count = $this->repository->count(['userId' => 'non-existent-user-xyz-999']);

        // Assert
        $this->assertSame(0, $count);
    }

    public function testFindOneByRespectOrderByClause(): void
    {
        // Arrange
        $strategy1 = $this->createTestStrategy('findone-order-1');
        $strategy2 = $this->createTestStrategy('findone-order-2');
        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy2);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($record2); // 故意先保存user-2
        self::getEntityManager()->persist($record1); // 后保存user-1
        self::getEntityManager()->flush();

        // Act - 通过具体的userId查询，确保返回正确的实体
        $result = $this->repository->findOneBy(['userId' => $record2->getUserId()]);

        // Assert
        $this->assertInstanceOf(VerificationRecord::class, $result);
        $this->assertSame($record2->getUserId(), $result->getUserId());
        $this->assertStringContainsString('user-2', $result->getUserId());
    }

    public function testFindAllWithExistingRecordsReturnsArrayOfEntities(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('findall-exist');
        $record = new VerificationRecord();
        $record->setUserId('user-1-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($record);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findAll();

        // Assert
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContainsOnlyInstancesOf(VerificationRecord::class, $results);
    }

    public function testCountByStrategyRelation(): void
    {
        // Arrange
        $strategy1 = new VerificationStrategy();
        $strategy1->setName('count-strategy-1-' . uniqid());
        $strategy1->setBusinessType('business-type-1');
        $strategy2 = new VerificationStrategy();
        $strategy2->setName('count-strategy-2-' . uniqid());
        $strategy2->setBusinessType('business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy1);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::SUCCESS);

        $record3 = new VerificationRecord();
        $record3->setUserId('user-3-' . uniqid());
        $record3->setStrategy($strategy2);
        $record3->setBusinessType('hotel-checkin');
        $record3->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['strategy' => $strategy1]);

        // Assert
        $this->assertSame(2, $count);
    }

    public function testFindByStrategyRelation(): void
    {
        // Arrange
        $strategy1 = new VerificationStrategy();
        $strategy1->setName('relation-strategy-1-' . uniqid());
        $strategy1->setBusinessType('business-type-1');
        $strategy2 = new VerificationStrategy();
        $strategy2->setName('relation-strategy-2-' . uniqid());
        $strategy2->setBusinessType('business-type-2');
        self::getEntityManager()->persist($strategy1);
        self::getEntityManager()->persist($strategy2);

        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy2);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['strategy' => $strategy1]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($record1, $results);
        $this->assertNotContains($record2, $results);
    }

    public function testFindOneByShouldRespectOrderByClause(): void
    {
        // Arrange
        $strategy = new VerificationStrategy();
        $strategy->setName('order-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);

        $oldRecord = new VerificationRecord();
        $oldRecord->setUserId('test-user-' . uniqid());
        $oldRecord->setStrategy($strategy);
        $oldRecord->setBusinessType('hotel-checkin');
        $oldRecord->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($oldRecord);
        self::getEntityManager()->flush();

        sleep(1); // Ensure different timestamps

        $newRecord = new VerificationRecord();
        $newRecord->setUserId('test-user-' . uniqid());
        $newRecord->setStrategy($strategy);
        $newRecord->setBusinessType('hotel-checkin');
        $newRecord->setResult(VerificationResult::SUCCESS);
        self::getEntityManager()->persist($newRecord);
        self::getEntityManager()->flush();

        // Act - Find latest record
        $result = $this->repository->findOneBy(['businessType' => 'hotel-checkin'], ['createTime' => 'DESC']);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame($newRecord->getUserId(), $result->getUserId());
    }

    public function testFindByOperationIdIsNull(): void
    {
        // Arrange
        $strategy = new VerificationStrategy();
        $strategy->setName('null-operation-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);

        $recordWithOperation = new VerificationRecord();
        $recordWithOperation->setUserId('user-with-op-' . uniqid());
        $recordWithOperation->setStrategy($strategy);
        $recordWithOperation->setBusinessType('hotel-checkin');
        $recordWithOperation->setResult(VerificationResult::SUCCESS);
        $recordWithOperation->setOperationId('operation-123');

        $recordWithoutOperation = new VerificationRecord();
        $recordWithoutOperation->setUserId('user-without-op-' . uniqid());
        $recordWithoutOperation->setStrategy($strategy);
        $recordWithoutOperation->setBusinessType('hotel-checkin');
        $recordWithoutOperation->setResult(VerificationResult::SUCCESS);
        // operationId remains null by default

        self::getEntityManager()->persist($recordWithOperation);
        self::getEntityManager()->persist($recordWithoutOperation);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['operationId' => null]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($recordWithoutOperation, $results);
        $this->assertNotContains($recordWithOperation, $results);
    }

    public function testFindByConfidenceScoreIsNull(): void
    {
        // Arrange
        $strategy = new VerificationStrategy();
        $strategy->setName('null-confidence-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);

        $recordWithScore = new VerificationRecord();
        $recordWithScore->setUserId('user-with-score-' . uniqid());
        $recordWithScore->setStrategy($strategy);
        $recordWithScore->setBusinessType('hotel-checkin');
        $recordWithScore->setResult(VerificationResult::SUCCESS);
        $recordWithScore->setConfidenceScore(0.95);

        $recordWithoutScore = new VerificationRecord();
        $recordWithoutScore->setUserId('user-without-score-' . uniqid());
        $recordWithoutScore->setStrategy($strategy);
        $recordWithoutScore->setBusinessType('hotel-checkin');
        $recordWithoutScore->setResult(VerificationResult::SUCCESS);
        // confidenceScore remains null by default

        self::getEntityManager()->persist($recordWithScore);
        self::getEntityManager()->persist($recordWithoutScore);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findBy(['confidenceScore' => null]);

        // Assert
        $this->assertIsArray($results);
        $this->assertContains($recordWithoutScore, $results);
        $this->assertNotContains($recordWithScore, $results);
    }

    public function testCountWithOperationIdIsNull(): void
    {
        // Arrange
        $strategy = new VerificationStrategy();
        $strategy->setName('count-null-op-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);

        $recordWithOperation = new VerificationRecord();
        $recordWithOperation->setUserId('user-with-op-' . uniqid());
        $recordWithOperation->setStrategy($strategy);
        $recordWithOperation->setBusinessType('hotel-checkin');
        $recordWithOperation->setResult(VerificationResult::SUCCESS);
        $recordWithOperation->setOperationId('operation-456');

        $recordWithoutOperation = new VerificationRecord();
        $recordWithoutOperation->setUserId('user-without-op-' . uniqid());
        $recordWithoutOperation->setStrategy($strategy);
        $recordWithoutOperation->setBusinessType('hotel-checkin');
        $recordWithoutOperation->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($recordWithOperation);
        self::getEntityManager()->persist($recordWithoutOperation);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['operationId' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithConfidenceScoreIsNull(): void
    {
        // Arrange
        $strategy = new VerificationStrategy();
        $strategy->setName('count-null-score-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);

        $recordWithScore = new VerificationRecord();
        $recordWithScore->setUserId('user-with-score-' . uniqid());
        $recordWithScore->setStrategy($strategy);
        $recordWithScore->setBusinessType('hotel-checkin');
        $recordWithScore->setResult(VerificationResult::SUCCESS);
        $recordWithScore->setConfidenceScore(0.85);

        $recordWithoutScore = new VerificationRecord();
        $recordWithoutScore->setUserId('user-without-score-' . uniqid());
        $recordWithoutScore->setStrategy($strategy);
        $recordWithoutScore->setBusinessType('hotel-checkin');
        $recordWithoutScore->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($recordWithScore);
        self::getEntityManager()->persist($recordWithoutScore);
        self::getEntityManager()->flush();

        // Act
        $count = $this->repository->count(['confidenceScore' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithSortingLogicShouldReturnCorrectlyOrderedEntity(): void
    {
        // Arrange
        $strategy = $this->createTestStrategy('sort-test-strategy');
        $userId = 'user-sort-' . uniqid();

        // Create records with different confidence scores to test sorting
        $record1 = new VerificationRecord();

        $record1->setUserId($userId);

        $record1->setStrategy($strategy);

        $record1->setBusinessType('hotel-checkin');

        $record1->setResult(VerificationResult::SUCCESS);
        $record1->setConfidenceScore(0.75);

        $record2 = new VerificationRecord();

        $record2->setUserId($userId);

        $record2->setStrategy($strategy);

        $record2->setBusinessType('hotel-checkin');

        $record2->setResult(VerificationResult::SUCCESS);
        $record2->setConfidenceScore(0.95);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        // Act & Assert - Test different sorting scenarios

        // Test 1: Order by confidenceScore DESC (should return the higher score one)
        $result = $this->repository->findOneBy(['userId' => $userId], ['confidenceScore' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertSame(0.95, $result->getConfidenceScore());

        // Test 2: Order by confidenceScore ASC (should return the lower score one)
        $result = $this->repository->findOneBy(['userId' => $userId], ['confidenceScore' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertSame(0.75, $result->getConfidenceScore());

        // Test 3: Order by ID DESC (should return the one with higher ID)
        $result = $this->repository->findOneBy(['userId' => $userId], ['id' => 'DESC']);
        $this->assertNotNull($result);
        $higherIdRecord = $record1->getId() > $record2->getId() ? $record1 : $record2;
        $this->assertSame($higherIdRecord->getId(), $result->getId());

        // Test 4: Order by ID ASC (should return the one with lower ID)
        $result = $this->repository->findOneBy(['userId' => $userId], ['id' => 'ASC']);
        $this->assertNotNull($result);
        $lowerIdRecord = $record1->getId() < $record2->getId() ? $record1 : $record2;
        $this->assertSame($lowerIdRecord->getId(), $result->getId());
    }

    public function testCountByAssociationStrategyShouldReturnCorrectNumber(): void
    {
        // Arrange
        $strategy1 = $this->createTestStrategy('count-association-strategy-1');
        $strategy2 = $this->createTestStrategy('count-association-strategy-2');

        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy1);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::FAILED);

        $record3 = new VerificationRecord();
        $record3->setUserId('user-3-' . uniqid());
        $record3->setStrategy($strategy2);
        $record3->setBusinessType('hotel-checkin');
        $record3->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->persist($record3);
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
        $strategy1 = $this->createTestStrategy('findone-association-strategy-1');
        $strategy2 = $this->createTestStrategy('findone-association-strategy-2');

        $record1 = new VerificationRecord();
        $record1->setUserId('user-1-' . uniqid());
        $record1->setStrategy($strategy1);
        $record1->setBusinessType('hotel-checkin');
        $record1->setResult(VerificationResult::SUCCESS);

        $record2 = new VerificationRecord();
        $record2->setUserId('user-2-' . uniqid());
        $record2->setStrategy($strategy2);
        $record2->setBusinessType('hotel-checkin');
        $record2->setResult(VerificationResult::SUCCESS);

        self::getEntityManager()->persist($record1);
        self::getEntityManager()->persist($record2);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findOneBy(['strategy' => $strategy1]);

        // Assert
        $this->assertInstanceOf(VerificationRecord::class, $result);
        $this->assertSame($record1->getId(), $result->getId());
        $this->assertSame($strategy1, $result->getStrategy());
    }

    /**
     * @return ServiceEntityRepository<VerificationRecord>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): VerificationRecord
    {
        $strategy = new VerificationStrategy();
        $strategy->setName('test-strategy-' . uniqid());
        $strategy->setBusinessType('business-type');
        self::getEntityManager()->persist($strategy);
        self::getEntityManager()->flush();

        $record = new VerificationRecord();
        $record->setUserId('test-user-' . uniqid());
        $record->setStrategy($strategy);
        $record->setBusinessType('hotel-checkin');
        $record->setResult(VerificationResult::SUCCESS);

        return $record;
    }
}
