<?php

namespace Tourze\FaceDetectBundle\Contract;

use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;

/**
 * 人脸信息采集服务接口
 */
interface FaceCollectionServiceInterface
{
    /**
     * 检查用户是否已采集人脸信息
     */
    public function hasCollectedFace(string $userId): bool;

    /**
     * 采集用户人脸信息
     *
     * @param string $userId 用户ID
     * @param string $imageData 人脸图片数据（base64编码）
     * @param array $deviceInfo 设备信息
     * @param string $collectionMethod 采集方式
     * @return FaceProfile 人脸档案
     * @throws FaceDetectException 采集失败时抛出异常
     */
    public function collectFace(
        string $userId,
        string $imageData,
        array $deviceInfo = [],
        string $collectionMethod = 'manual'
    ): FaceProfile;

    /**
     * 重新采集用户人脸信息
     *
     * @param string $userId 用户ID
     * @param string $imageData 人脸图片数据（base64编码）
     * @param array $deviceInfo 设备信息
     * @return FaceProfile 更新后的人脸档案
     * @throws FaceDetectException 采集失败时抛出异常
     */
    public function recollectFace(
        string $userId,
        string $imageData,
        array $deviceInfo = []
    ): FaceProfile;

    /**
     * 获取用户人脸档案
     *
     * @param string $userId 用户ID
     * @return FaceProfile|null 人脸档案，不存在时返回null
     */
    public function getFaceProfile(string $userId): ?FaceProfile;

    /**
     * 检查人脸档案是否可用
     *
     * @param string $userId 用户ID
     * @return bool 是否可用
     */
    public function isFaceProfileAvailable(string $userId): bool;

    /**
     * 删除用户人脸信息
     *
     * @param string $userId 用户ID
     * @return bool 删除是否成功
     */
    public function deleteFaceProfile(string $userId): bool;

    /**
     * 批量处理过期的人脸档案
     *
     * @return int 处理的档案数量
     */
    public function processExpiredProfiles(): int;

    /**
     * 验证人脸图片质量
     *
     * @param string $imageData 人脸图片数据（base64编码）
     * @return array 质量检测结果
     * @throws FaceDetectException 检测失败时抛出异常
     */
    public function validateFaceQuality(string $imageData): array;
}
