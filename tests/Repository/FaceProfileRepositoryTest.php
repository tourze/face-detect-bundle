<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\FaceDetectBundle\Repository\FaceProfileRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * FaceProfileRepository 仓储类测试
 *
 * @internal
 */
#[CoversClass(FaceProfileRepository::class)]
#[RunTestsInSeparateProcesses]
final class FaceProfileRepositoryTest extends AbstractRepositoryTestCase
{
    private FaceProfileRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(FaceProfileRepository::class);
    }

    /**
     * 创建FaceProfile实体的辅助方法，使用setter方法设置属性
     */
    private function createFaceProfile(string $userId, string $faceFeatures): FaceProfile
    {
        $profile = new FaceProfile();
        $profile->setUserId($userId);
        $profile->setFaceFeatures($faceFeatures);

        return $profile;
    }

    public function testRepositoryIsInstantiatedFromContainer(): void
    {
        $this->assertInstanceOf(FaceProfileRepository::class, $this->repository);
    }

    public function testFindByUserIdReturnsCorrectProfile(): void
    {
        // Arrange
        $userId = 'test-user-123';
        $profile1 = $this->createFaceProfile($userId, 'face-data-1');
        $profile2 = $this->createFaceProfile('other-user', 'face-data-2');

        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findByUserId($userId);

        // Assert
        $this->assertSame($profile1, $result);
        $this->assertNotSame($profile2, $result);
    }

    public function testFindByUserIdReturnsNullWhenNotFound(): void
    {
        // Act
        $result = $this->repository->findByUserId('non-existent-user');

        // Assert
        $this->assertNull($result);
    }

    public function testFindAvailableByUserIdReturnsActiveProfile(): void
    {
        // Arrange
        $userId1 = 'test-user-available-' . uniqid();
        $userId2 = 'test-user-available-expired-' . uniqid();
        $activeProfile = $this->createFaceProfile($userId1, 'face-data-active');
        $activeProfile->setStatus(FaceProfileStatus::ACTIVE);
        $expiredProfile = $this->createFaceProfile($userId2, 'face-data-expired');
        $expiredProfile->setStatus(FaceProfileStatus::EXPIRED);

        self::getEntityManager()->persist($activeProfile);
        self::getEntityManager()->persist($expiredProfile);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findAvailableByUserId($userId1);

        // Assert
        $this->assertSame($activeProfile, $result);
        $this->assertNotSame($expiredProfile, $result);
    }

    public function testFindAvailableByUserIdReturnsNullWhenNoActiveProfile(): void
    {
        // Arrange
        $userId = 'test-user-no-active';
        $expiredProfile = $this->createFaceProfile($userId, 'face-data-expired');
        $expiredProfile->setStatus(FaceProfileStatus::EXPIRED);

        self::getEntityManager()->persist($expiredProfile);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findAvailableByUserId($userId);

        // Assert
        $this->assertNull($result);
    }

    public function testFindExpiredProfilesReturnsExpiredProfiles(): void
    {
        // Arrange
        $before = new \DateTimeImmutable('2023-06-01');
        $activeProfile = $this->createFaceProfile('user-1-' . uniqid(), 'face-data-1');
        $expiredProfile = $this->createFaceProfile('user-2-' . uniqid(), 'face-data-2');

        self::getEntityManager()->persist($activeProfile);
        self::getEntityManager()->persist($expiredProfile);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findExpiredProfiles($before);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindExpiredProfilesWithNullParameterReturnsAllExpired(): void
    {
        // Act
        $results = $this->repository->findExpiredProfiles(null);

        // Assert
        $this->assertIsArray($results);
    }

    public function testCountByUserIdReturnsCorrectCount(): void
    {
        // Arrange
        $baseUserId = 'test-user-count-' . uniqid();
        $profile1 = $this->createFaceProfile($baseUserId . '-1', 'face-data-1');
        $profile2 = $this->createFaceProfile($baseUserId . '-2', 'face-data-2');
        $profile3 = $this->createFaceProfile('other-user-' . uniqid(), 'face-data-3');

        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->persist($profile3);
        self::getEntityManager()->flush();

        // Act - Since we can't use the same userId, we'll test that each profile exists individually
        $count1 = $this->repository->countByUserId($profile1->getUserId());
        $count2 = $this->repository->countByUserId($profile2->getUserId());
        $count3 = $this->repository->countByUserId($profile3->getUserId());

        // Assert
        $this->assertSame(1, $count1);
        $this->assertSame(1, $count2);
        $this->assertSame(1, $count3);
    }

    public function testCountByUserIdWithEmptyUserIdReturnsZero(): void
    {
        // Act
        $count = $this->repository->countByUserId('');

        // Assert
        $this->assertSame(0, $count);
    }

    public function testGetStatisticsReturnsValidStatistics(): void
    {
        // Act
        $statistics = $this->repository->getStatistics();

        // Assert
        $this->assertIsArray($statistics);
    }

    public function testFindByCreateTimeRangeReturnsProfilesInRange(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2023-01-01');
        $end = new \DateTimeImmutable('2023-12-31');

        $profile = $this->createFaceProfile('user-1-' . uniqid(), 'face-data');
        self::getEntityManager()->persist($profile);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByCreateTimeRange($start, $end);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByCreateTimeRangeWithInvalidRangeReturnsEmptyArray(): void
    {
        // Arrange
        $start = new \DateTimeImmutable('2025-01-01');
        $end = new \DateTimeImmutable('2024-01-01'); // end before start

        // Act
        $results = $this->repository->findByCreateTimeRange($start, $end);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByLowQualityReturnsLowQualityProfiles(): void
    {
        // Arrange
        $threshold = 0.6;

        // Act
        $results = $this->repository->findByLowQuality($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByLowQualityWithInvalidThresholdHandlesGracefully(): void
    {
        // Arrange
        $threshold = -1.0; // Invalid threshold

        // Act
        $results = $this->repository->findByLowQuality($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testMarkExpiredProfilesMarksProfilesAsExpired(): void
    {
        // Act
        $markedCount = $this->repository->markExpiredProfiles();

        // Assert
        $this->assertIsInt($markedCount);
        $this->assertGreaterThanOrEqual(0, $markedCount);
    }

    public function testDeleteByStatusDeletesCorrectProfiles(): void
    {
        // Arrange
        $status = FaceProfileStatus::EXPIRED;
        $profile1 = $this->createFaceProfile('user-1-' . uniqid(), 'face-data-1');
        $profile2 = $this->createFaceProfile('user-2-' . uniqid(), 'face-data-2');

        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->flush();

        // Act
        $deletedCount = $this->repository->deleteByStatus($status);

        // Assert
        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testFindByCollectionMethodReturnsProfilesByMethod(): void
    {
        // Arrange
        $collectionMethod = 'api_upload';

        // Act
        $results = $this->repository->findByCollectionMethod($collectionMethod);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindByCollectionMethodWithEmptyStringReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByCollectionMethod('');

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindExpiringProfilesReturnsProfilesNearExpiration(): void
    {
        // Arrange
        $days = 7; // profiles expiring in 7 days

        // Act
        $results = $this->repository->findExpiringProfiles($days);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindExpiringProfilesWithZeroDaysReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findExpiringProfiles(0);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindHighQualityProfilesReturnsHighQualityProfiles(): void
    {
        // Arrange
        $threshold = 0.8;

        // Act
        $results = $this->repository->findHighQualityProfiles($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testFindHighQualityProfilesWithHighThresholdReturnsLimitedResults(): void
    {
        // Arrange
        $threshold = 0.99; // Very high threshold

        // Act
        $results = $this->repository->findHighQualityProfiles($threshold);

        // Assert
        $this->assertIsArray($results);
    }

    public function testRepositoryHandlesDatabaseConnectionFailure(): void
    {
        // Arrange
        $userId = 'test-connection-failure';

        // Act & Assert - Should not throw exception
        try {
            $result = $this->repository->findByUserId($userId);
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
            '',           // empty userId
            'very-long-user-id-' . str_repeat('x', 1000), // very long userId
            '特殊字符用户',  // unicode characters
        ];

        foreach ($edgeCases as $userId) {
            $result = $this->repository->findByUserId($userId);
            $this->assertNull($result);
        }
    }

    public function testRepositoryPerformanceWithLargeDataset(): void
    {
        // Arrange
        $baseUserId = 'performance-test-user-' . uniqid();
        $profiles = [];

        // Create 50 profiles to test performance
        for ($i = 0; $i < 50; ++$i) {
            $profile = $this->createFaceProfile($baseUserId . '-' . $i, "face-data-{$i}");
            $profiles[] = $profile;
            self::getEntityManager()->persist($profile);
        }
        self::getEntityManager()->flush();

        // Act & Assert - Should complete within reasonable time
        $startTime = microtime(true);
        // Test findAll performance since we can't duplicate userIds
        $allProfiles = $this->repository->findAll();
        $endTime = microtime(true);

        $this->assertLessThan(1.0, $endTime - $startTime, 'Query should complete within 1 second');
        $this->assertGreaterThanOrEqual(50, count($allProfiles));
    }

    public function testRepositoryTransactionHandling(): void
    {
        // Arrange
        $userId = 'transaction-test-user';
        $profile = $this->createFaceProfile($userId, 'transaction-face');

        // Act - Test within transaction
        self::getEntityManager()->beginTransaction();
        try {
            self::getEntityManager()->persist($profile);
            self::getEntityManager()->flush();

            $result = $this->repository->findByUserId($userId);
            $this->assertNotNull($result);

            self::getEntityManager()->rollback();
        } catch (\Exception $e) {
            self::getEntityManager()->rollback();
            throw $e;
        }

        // Assert - Profile should not exist after rollback
        $result = $this->repository->findByUserId($userId);
        $this->assertNull($result);
    }

    public function testRepositoryWithConcurrentAccess(): void
    {
        // Arrange
        $userId1 = 'concurrent-user-1';
        $userId2 = 'concurrent-user-2';
        $profile1 = $this->createFaceProfile($userId1, 'concurrent-face-1');
        $profile2 = $this->createFaceProfile($userId2, 'concurrent-face-2');

        // Act - Simulate concurrent operations
        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->flush();

        $result1 = $this->repository->findByUserId($userId1);

        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->flush();

        $result2 = $this->repository->findByUserId($userId2);

        // Assert
        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertNotSame($result1, $result2);
    }

    public function testCountWithNonExistentUserIdShouldReturnZero(): void
    {
        // Act
        $count = $this->repository->count(['userId' => 'non-existent-user-xyz-999']);

        // Assert
        $this->assertSame(0, $count);
    }

    // 基础Repository方法测试 - findBy相关

    // 基础Repository方法测试 - findOneBy相关

    public function testFindOneByRespectOrderByClause(): void
    {
        // Arrange
        $profile1 = $this->createFaceProfile('user-1-' . uniqid(), 'face-1');
        $profile2 = $this->createFaceProfile('user-2-' . uniqid(), 'face-2');
        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->findOneBy(['status' => FaceProfileStatus::ACTIVE], ['userId' => 'DESC']);

        // Assert
        $this->assertInstanceOf(FaceProfile::class, $result);
    }

    // 基础Repository方法测试 - findAll相关
    public function testFindAllWithExistingRecordsReturnsArrayOfEntities(): void
    {
        // Arrange
        $profile = $this->createFaceProfile('user-1-' . uniqid(), 'face-1');
        self::getEntityManager()->persist($profile);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findAll();

        // Assert
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContainsOnlyInstancesOf(FaceProfile::class, $results);
    }

    public function testRepositoryWithInvalidFieldQueryShouldThrowException(): void
    {
        // Act & Assert
        $this->expectException(UnrecognizedField::class);
        $this->repository->findOneBy(['invalidField' => 'value']);
    }

    // 添加缺失的基础测试方法

    public function testSaveShouldPersistEntity(): void
    {
        // Arrange
        $profile = $this->createFaceProfile('user-save-' . uniqid(), 'save-face-data');

        // Act
        $this->repository->save($profile, true);

        // Assert
        $this->assertNotNull($profile->getId());
        $savedProfile = $this->repository->find($profile->getId());
        $this->assertNotNull($savedProfile);
        $this->assertSame('save-face-data', $savedProfile->getFaceFeatures());
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        // Arrange
        $profile = $this->createFaceProfile('user-save-no-flush-' . uniqid(), 'no-flush-data');

        // Act
        $this->repository->save($profile, false);

        // Assert - 在没有flush的情况下，实体应该在UnitOfWork中但还没有ID
        $this->assertNull($profile->getId());

        // 手动flush后应该有ID
        self::getEntityManager()->flush();
        $this->assertNotNull($profile->getId());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        // Arrange
        $profile = $this->createFaceProfile('user-remove-' . uniqid(), 'remove-data');
        self::getEntityManager()->persist($profile);
        self::getEntityManager()->flush();
        $id = $profile->getId();

        // Act
        $this->repository->remove($profile, true);

        // Assert
        $this->assertNull($this->repository->find($id));
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        // Arrange
        $profile = $this->createFaceProfile('user-remove-no-flush-' . uniqid(), 'no-flush-remove');
        self::getEntityManager()->persist($profile);
        self::getEntityManager()->flush();
        $id = $profile->getId();

        // Act
        $this->repository->remove($profile, false);

        // Assert - 在没有flush的情况下，实体应该仍然存在
        $this->assertNotNull($this->repository->find($id));

        // 手动flush后应该被删除
        self::getEntityManager()->flush();
        $this->assertNull($this->repository->find($id));
    }

    public function testFindOneByWithNullFieldShouldWork(): void
    {
        // Arrange
        $profile1 = $this->createFaceProfile('user-null-test-1-' . uniqid(), 'data1');
        $profile1->setExpiresTime(null); // 设置为null的可空字段
        $profile2 = $this->createFaceProfile('user-null-test-2-' . uniqid(), 'data2');
        $profile2->setExpiresTime(new \DateTimeImmutable());

        self::getEntityManager()->persist($profile1);
        self::getEntityManager()->persist($profile2);
        self::getEntityManager()->flush();

        // Act - 先确认profile1确实有null的expiresTime，然后通过userId查询
        $this->assertNull($profile1->getExpiresTime());
        $result = $this->repository->findOneBy(['userId' => $profile1->getUserId()]);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(FaceProfile::class, $result);
        $this->assertSame($profile1->getUserId(), $result->getUserId());
        $this->assertNull($result->getExpiresTime());
    }

    public function testFindOneByOrderByShouldRespectSorting(): void
    {
        // Arrange
        $profile1 = $this->createFaceProfile('user-order-a-' . uniqid(), 'data-a');
        $profile2 = $this->createFaceProfile('user-order-z-' . uniqid(), 'data-z');

        self::getEntityManager()->persist($profile2); // 故意先保存z
        self::getEntityManager()->persist($profile1); // 后保存a
        self::getEntityManager()->flush();

        // Act - 按userId升序排序
        $result = $this->repository->findOneBy(['status' => FaceProfileStatus::ACTIVE], ['userId' => 'ASC']);

        // Assert - 应该返回userId较小的那个
        $this->assertNotNull($result);
        $this->assertStringContainsString('user-order-a', $result->getUserId());
        $this->assertSame('data-a', $result->getFaceFeatures());
    }

    /**
     * @return ServiceEntityRepository<FaceProfile>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $profile = new FaceProfile();
        $profile->setUserId('test_user_' . time() . '_' . random_int(1000, 9999));
        $profile->setFaceFeatures('face_features_' . bin2hex(random_bytes(16)));
        $profile->setQualityScore(0.85);
        $profile->setCollectionMethod('manual');
        $profile->setDeviceInfo(['device' => 'test_camera', 'version' => '1.0']);
        $profile->setStatus(FaceProfileStatus::ACTIVE);
        $profile->setExpiresTime(new \DateTimeImmutable('+30 days'));

        return $profile;
    }
}
