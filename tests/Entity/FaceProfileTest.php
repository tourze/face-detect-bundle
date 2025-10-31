<?php

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * FaceProfile 实体测试类
 *
 * @internal
 */
#[CoversClass(FaceProfile::class)]
final class FaceProfileTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $entity = new FaceProfile();
        $entity->setUserId('test_user');
        $entity->setFaceFeatures('test_face_features');

        return $entity;
    }

    private function createFaceProfile(string $userId, string $faceFeatures): FaceProfile
    {
        $profile = new FaceProfile();
        $profile->setUserId($userId);
        $profile->setFaceFeatures($faceFeatures);

        return $profile;
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'userId' => ['userId', 'new_user_id'];
        yield 'faceFeatures' => ['faceFeatures', 'new_face_features'];
        yield 'qualityScore' => ['qualityScore', 0.95];
        yield 'collectionMethod' => ['collectionMethod', 'auto'];
        yield 'deviceInfo' => ['deviceInfo', ['browser' => 'Chrome']];
        yield 'status' => ['status', FaceProfileStatus::DISABLED];
        yield 'expiresTime' => ['expiresTime', new \DateTimeImmutable('+1 day')];
    }

    public function testConstructionWithValidData(): void
    {
        // Arrange
        $userId = 'user123';
        $faceFeatures = 'encrypted_face_data';

        // Act
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId($userId);
        $faceProfile->setFaceFeatures($faceFeatures);

        // Assert
        $this->assertSame($userId, $faceProfile->getUserId());
        $this->assertSame($faceFeatures, $faceProfile->getFaceFeatures());
        $this->assertSame(FaceProfileStatus::ACTIVE, $faceProfile->getStatus());
        $this->assertSame(0.00, $faceProfile->getQualityScore());
        $this->assertSame('manual', $faceProfile->getCollectionMethod());
        $this->assertNull($faceProfile->getDeviceInfo());
        $this->assertNull($faceProfile->getExpiresTime());
        $this->assertNull($faceProfile->getCreateTime());
        $this->assertNull($faceProfile->getUpdateTime());
    }

    public function testConstructionWithEmptyUserId(): void
    {
        // Arrange
        $userId = '';
        $faceFeatures = 'encrypted_face_data';

        // Act
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId($userId);
        $faceProfile->setFaceFeatures($faceFeatures);

        // Assert
        $this->assertSame('', $faceProfile->getUserId());
    }

    public function testConstructionWithEmptyFaceFeatures(): void
    {
        // Arrange
        $userId = 'user123';
        $faceFeatures = '';

        // Act
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId($userId);
        $faceProfile->setFaceFeatures($faceFeatures);

        // Assert
        $this->assertSame('', $faceProfile->getFaceFeatures());
    }

    public function testSetFaceFeaturesUpdatesData(): void
    {
        // Arrange
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId('user123');
        $faceProfile->setFaceFeatures('old_data');

        // Act
        $newFaceFeatures = 'new_encrypted_data';
        $faceProfile->setFaceFeatures($newFaceFeatures);

        // Assert
        $this->assertSame($newFaceFeatures, $faceProfile->getFaceFeatures());
    }

    public function testSetQualityScoreWithValidValues(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act & Assert - 测试边界值
        $faceProfile->setQualityScore(0.0);
        $this->assertSame(0.0, $faceProfile->getQualityScore());

        $faceProfile->setQualityScore(1.0);
        $this->assertSame(1.0, $faceProfile->getQualityScore());

        $faceProfile->setQualityScore(0.85);
        $this->assertSame(0.85, $faceProfile->getQualityScore());
    }

    public function testSetQualityScoreWithHighValue(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act
        $faceProfile->setQualityScore(0.95);

        // Assert
        $this->assertSame(0.95, $faceProfile->getQualityScore());
    }

    public function testSetCollectionMethodWithValidValues(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act & Assert
        $faceProfile->setCollectionMethod('auto');
        $this->assertSame('auto', $faceProfile->getCollectionMethod());

        $faceProfile->setCollectionMethod('import');
        $this->assertSame('import', $faceProfile->getCollectionMethod());

        $faceProfile->setCollectionMethod('manual');
        $this->assertSame('manual', $faceProfile->getCollectionMethod());
    }

    public function testSetDeviceInfoWithArrayData(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $deviceInfo = [
            'browser' => 'Chrome',
            'os' => 'Windows 10',
            'ip' => '192.168.1.1',
        ];

        // Act
        $faceProfile->setDeviceInfo($deviceInfo);

        // Assert
        $this->assertSame($deviceInfo, $faceProfile->getDeviceInfo());
    }

    public function testSetDeviceInfoWithNull(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act
        $faceProfile->setDeviceInfo(null);

        // Assert
        $this->assertNull($faceProfile->getDeviceInfo());
    }

    public function testSetDeviceInfoWithEmptyArray(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act
        $faceProfile->setDeviceInfo([]);

        // Assert
        $this->assertSame([], $faceProfile->getDeviceInfo());
    }

    public function testSetStatusWithAllEnumValues(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
            $faceProfile->setStatus($status);
            $this->assertSame($status, $faceProfile->getStatus());
        }
    }

    public function testSetExpiresTimeWithFutureDate(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $futureDate = new \DateTimeImmutable('+1 month');

        // Act
        $faceProfile->setExpiresTime($futureDate);

        // Assert
        $this->assertSame($futureDate, $faceProfile->getExpiresTime());
    }

    public function testSetExpiresTimeWithNull(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act
        $faceProfile->setExpiresTime(null);

        // Assert
        $this->assertNull($faceProfile->getExpiresTime());
    }

    public function testIsExpiredWithNullExpiresTime(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act & Assert
        $this->assertFalse($faceProfile->isExpired());
    }

    public function testIsExpiredWithFutureExpiresTime(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $futureDate = new \DateTimeImmutable('+1 hour');
        $faceProfile->setExpiresTime($futureDate);

        // Act & Assert
        $this->assertFalse($faceProfile->isExpired());
    }

    public function testIsExpiredWithPastExpiresTime(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $pastDate = new \DateTimeImmutable('-1 hour');
        $faceProfile->setExpiresTime($pastDate);

        // Act & Assert
        $this->assertTrue($faceProfile->isExpired());
    }

    public function testIsExpiredWithCurrentTime(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        // 设置一个稍微过去的时间以确保过期
        $pastTime = new \DateTimeImmutable('-1 second');
        $faceProfile->setExpiresTime($pastTime);

        // Act & Assert - 过去的时间应该被认为是过期的
        $this->assertTrue($faceProfile->isExpired());
    }

    public function testIsAvailableWithActiveStatusAndNoExpiry(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);

        // Act & Assert
        $this->assertTrue($faceProfile->isAvailable());
    }

    public function testIsAvailableWithActiveStatusAndFutureExpiry(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);
        $faceProfile->setExpiresTime(new \DateTimeImmutable('+1 hour'));

        // Act & Assert
        $this->assertTrue($faceProfile->isAvailable());
    }

    public function testIsAvailableWithExpiredStatus(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::EXPIRED);

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function testIsAvailableWithDisabledStatus(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::DISABLED);

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function testIsAvailableWithActiveStatusButExpired(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);
        $faceProfile->setExpiresTime(new \DateTimeImmutable('-1 hour'));

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function testSetExpiresAfterWithInterval(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $interval = new \DateInterval('P30D'); // 30 days
        $beforeTime = new \DateTimeImmutable();

        // Act
        $faceProfile->setExpiresAfter($interval);

        // Assert
        $expiresTime = $faceProfile->getExpiresTime();
        $this->assertInstanceOf(\DateTimeInterface::class, $expiresTime);
        $this->assertGreaterThan($beforeTime, $expiresTime);

        // 验证大约是30天后（允许几秒误差）
        $expectedTime = $beforeTime->add($interval);
        $diff = abs($expiresTime->getTimestamp() - $expectedTime->getTimestamp());
        $this->assertLessThan(10, $diff); // 允许10秒误差
    }

    public function testSetExpiresAfterWithZeroInterval(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');
        $interval = new \DateInterval('PT0S'); // 0 seconds
        $beforeTime = new \DateTimeImmutable();

        // Act
        $faceProfile->setExpiresAfter($interval);

        // Assert
        $expiresTime = $faceProfile->getExpiresTime();
        $this->assertInstanceOf(\DateTimeInterface::class, $expiresTime);

        // 应该接近当前时间
        $diff = abs($expiresTime->getTimestamp() - $beforeTime->getTimestamp());
        $this->assertLessThan(5, $diff);
    }

    public function testToStringMethod(): void
    {
        // Arrange
        $userId = 'test_user_123';
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId($userId);
        $faceProfile->setFaceFeatures('data');

        // Act
        $result = (string) $faceProfile;

        // Assert
        $this->assertStringContainsString('FaceProfile', $result);
        $this->assertStringContainsString($userId, $result);
        $this->assertStringContainsString('0', $result); // ID 应该是 0，因为未持久化
    }

    public function testToStringWithSpecialCharactersInUserId(): void
    {
        // Arrange
        $userId = 'user@domain.com';
        $faceProfile = new FaceProfile();
        $faceProfile->setUserId($userId);
        $faceProfile->setFaceFeatures('data');

        // Act
        $result = (string) $faceProfile;

        // Assert
        $this->assertStringContainsString($userId, $result);
    }

    public function testMultiplePropertyUpdates(): void
    {
        // Arrange
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Act - 多个属性修改
        $faceProfile->setQualityScore(0.8);
        $faceProfile->setCollectionMethod('auto');
        $faceProfile->setStatus(FaceProfileStatus::DISABLED);

        // Assert
        $this->assertSame(0.8, $faceProfile->getQualityScore());
        $this->assertSame('auto', $faceProfile->getCollectionMethod());
        $this->assertSame(FaceProfileStatus::DISABLED, $faceProfile->getStatus());
    }

    public function testTimestampsAreInitiallyNull(): void
    {
        // Arrange & Act
        $faceProfile = $this->createFaceProfile('user123', 'data');

        // Assert
        $this->assertNull($faceProfile->getCreateTime());
        $this->assertNull($faceProfile->getUpdateTime());
    }
}
