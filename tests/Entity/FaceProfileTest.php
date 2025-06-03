<?php

namespace Tourze\FaceDetectBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

/**
 * FaceProfile 实体测试类
 */
class FaceProfileTest extends TestCase
{
    public function test_construction_with_valid_data(): void
    {
        // Arrange
        $userId = 'user123';
        $faceFeatures = 'encrypted_face_data';

        // Act
        $faceProfile = new FaceProfile($userId, $faceFeatures);

        // Assert
        $this->assertSame($userId, $faceProfile->getUserId());
        $this->assertSame($faceFeatures, $faceProfile->getFaceFeatures());
        $this->assertSame(FaceProfileStatus::ACTIVE, $faceProfile->getStatus());
        $this->assertSame(0.00, $faceProfile->getQualityScore());
        $this->assertSame('manual', $faceProfile->getCollectionMethod());
        $this->assertNull($faceProfile->getDeviceInfo());
        $this->assertNull($faceProfile->getExpiresTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $faceProfile->getCreateTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $faceProfile->getUpdateTime());
    }

    public function test_construction_with_empty_user_id(): void
    {
        // Arrange
        $userId = '';
        $faceFeatures = 'encrypted_face_data';

        // Act
        $faceProfile = new FaceProfile($userId, $faceFeatures);

        // Assert
        $this->assertSame('', $faceProfile->getUserId());
    }

    public function test_construction_with_empty_face_features(): void
    {
        // Arrange
        $userId = 'user123';
        $faceFeatures = '';

        // Act
        $faceProfile = new FaceProfile($userId, $faceFeatures);

        // Assert
        $this->assertSame('', $faceProfile->getFaceFeatures());
    }

    public function test_set_face_features_updates_data_and_timestamp(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'old_data');
        $originalUpdateTime = $faceProfile->getUpdateTime();
        usleep(1000); // 确保时间戳不同

        // Act
        $newFaceFeatures = 'new_encrypted_data';
        $faceProfile->setFaceFeatures($newFaceFeatures);

        // Assert
        $this->assertSame($newFaceFeatures, $faceProfile->getFaceFeatures());
        $this->assertGreaterThan($originalUpdateTime, $faceProfile->getUpdateTime());
    }

    public function test_set_quality_score_with_valid_values(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act & Assert - 测试边界值
        $faceProfile->setQualityScore(0.0);
        $this->assertSame(0.0, $faceProfile->getQualityScore());

        $faceProfile->setQualityScore(1.0);
        $this->assertSame(1.0, $faceProfile->getQualityScore());

        $faceProfile->setQualityScore(0.85);
        $this->assertSame(0.85, $faceProfile->getQualityScore());
    }

    public function test_set_quality_score_updates_timestamp(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $originalUpdateTime = $faceProfile->getUpdateTime();
        usleep(1000);

        // Act
        $faceProfile->setQualityScore(0.95);

        // Assert
        $this->assertGreaterThan($originalUpdateTime, $faceProfile->getUpdateTime());
    }

    public function test_set_collection_method_with_valid_values(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act & Assert
        $faceProfile->setCollectionMethod('auto');
        $this->assertSame('auto', $faceProfile->getCollectionMethod());

        $faceProfile->setCollectionMethod('import');
        $this->assertSame('import', $faceProfile->getCollectionMethod());

        $faceProfile->setCollectionMethod('manual');
        $this->assertSame('manual', $faceProfile->getCollectionMethod());
    }

    public function test_set_device_info_with_array_data(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $deviceInfo = [
            'browser' => 'Chrome',
            'os' => 'Windows 10',
            'ip' => '192.168.1.1'
        ];

        // Act
        $faceProfile->setDeviceInfo($deviceInfo);

        // Assert
        $this->assertSame($deviceInfo, $faceProfile->getDeviceInfo());
    }

    public function test_set_device_info_with_null(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act
        $faceProfile->setDeviceInfo(null);

        // Assert
        $this->assertNull($faceProfile->getDeviceInfo());
    }

    public function test_set_device_info_with_empty_array(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act
        $faceProfile->setDeviceInfo([]);

        // Assert
        $this->assertSame([], $faceProfile->getDeviceInfo());
    }

    public function test_set_status_with_all_enum_values(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act & Assert
        foreach (FaceProfileStatus::cases() as $status) {
            $faceProfile->setStatus($status);
            $this->assertSame($status, $faceProfile->getStatus());
        }
    }

    public function test_set_expires_time_with_future_date(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $futureDate = new \DateTimeImmutable('+1 month');

        // Act
        $faceProfile->setExpiresTime($futureDate);

        // Assert
        $this->assertSame($futureDate, $faceProfile->getExpiresTime());
    }

    public function test_set_expires_time_with_null(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act
        $faceProfile->setExpiresTime(null);

        // Assert
        $this->assertNull($faceProfile->getExpiresTime());
    }

    public function test_is_expired_with_null_expires_time(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');

        // Act & Assert
        $this->assertFalse($faceProfile->isExpired());
    }

    public function test_is_expired_with_future_expires_time(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $futureDate = new \DateTimeImmutable('+1 hour');
        $faceProfile->setExpiresTime($futureDate);

        // Act & Assert
        $this->assertFalse($faceProfile->isExpired());
    }

    public function test_is_expired_with_past_expires_time(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $pastDate = new \DateTimeImmutable('-1 hour');
        $faceProfile->setExpiresTime($pastDate);

        // Act & Assert
        $this->assertTrue($faceProfile->isExpired());
    }

    public function test_is_expired_with_current_time(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        // 设置一个稍微过去的时间以确保过期
        $pastTime = new \DateTimeImmutable('-1 second');
        $faceProfile->setExpiresTime($pastTime);

        // Act & Assert - 过去的时间应该被认为是过期的
        $this->assertTrue($faceProfile->isExpired());
    }

    public function test_is_available_with_active_status_and_no_expiry(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);

        // Act & Assert
        $this->assertTrue($faceProfile->isAvailable());
    }

    public function test_is_available_with_active_status_and_future_expiry(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);
        $faceProfile->setExpiresTime(new \DateTimeImmutable('+1 hour'));

        // Act & Assert
        $this->assertTrue($faceProfile->isAvailable());
    }

    public function test_is_available_with_expired_status(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::EXPIRED);

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function test_is_available_with_disabled_status(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::DISABLED);

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function test_is_available_with_active_status_but_expired(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $faceProfile->setStatus(FaceProfileStatus::ACTIVE);
        $faceProfile->setExpiresTime(new \DateTimeImmutable('-1 hour'));

        // Act & Assert
        $this->assertFalse($faceProfile->isAvailable());
    }

    public function test_set_expires_after_with_interval(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
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

    public function test_set_expires_after_with_zero_interval(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
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

    public function test_to_string_method(): void
    {
        // Arrange
        $userId = 'test_user_123';
        $faceProfile = new FaceProfile($userId, 'data');

        // Act
        $result = (string) $faceProfile;

        // Assert
        $this->assertStringContainsString('FaceProfile', $result);
        $this->assertStringContainsString($userId, $result);
        $this->assertStringContainsString('0', $result); // ID 应该是 0，因为未持久化
    }

    public function test_to_string_with_special_characters_in_user_id(): void
    {
        // Arrange
        $userId = 'user@domain.com';
        $faceProfile = new FaceProfile($userId, 'data');

        // Act
        $result = (string) $faceProfile;

        // Assert
        $this->assertStringContainsString($userId, $result);
    }

    public function test_multiple_property_updates_affect_update_time(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $originalUpdateTime = $faceProfile->getUpdateTime();
        usleep(1000);

        // Act - 多个属性修改
        $faceProfile->setQualityScore(0.8);
        $firstUpdateTime = $faceProfile->getUpdateTime();
        usleep(1000);
        
        $faceProfile->setCollectionMethod('auto');
        $secondUpdateTime = $faceProfile->getUpdateTime();

        // Assert
        $this->assertGreaterThan($originalUpdateTime, $firstUpdateTime);
        $this->assertGreaterThan($firstUpdateTime, $secondUpdateTime);
    }

    public function test_immutability_of_timestamps(): void
    {
        // Arrange
        $faceProfile = new FaceProfile('user123', 'data');
        $createTime = $faceProfile->getCreateTime();
        $updateTime = $faceProfile->getUpdateTime();

        // Act - 尝试修改时间戳对象
        if ($createTime instanceof \DateTime) {
            $createTime->modify('+1 day');
        }

        // Assert - 原始时间戳不应被影响
        $this->assertEquals($createTime->getTimestamp(), $faceProfile->getCreateTime()->getTimestamp());
    }
} 