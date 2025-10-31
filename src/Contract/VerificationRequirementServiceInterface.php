<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Contract;

use Tourze\FaceDetectBundle\Enum\VerificationType;
use Tourze\FaceDetectBundle\Exception\FaceDetectException;

/**
 * 验证需求判断服务接口
 */
interface VerificationRequirementServiceInterface
{
    /**
     * 检查是否需要人脸验证
     *
     * @param string               $userId       用户ID
     * @param string               $businessType 业务类型
     * @param array<string, mixed> $context      业务上下文
     *
     * @return bool 是否需要验证
     *
     * @throws FaceDetectException 检查失败时抛出异常
     */
    public function isVerificationRequired(
        string $userId,
        string $businessType,
        array $context = [],
    ): bool;

    /**
     * 获取验证类型
     *
     * @param string               $userId       用户ID
     * @param string               $businessType 业务类型
     * @param array<string, mixed> $context      业务上下文
     *
     * @return VerificationType 验证类型
     *
     * @throws FaceDetectException 获取失败时抛出异常
     */
    public function getVerificationType(
        string $userId,
        string $businessType,
        array $context = [],
    ): VerificationType;

    /**
     * 获取验证策略配置
     *
     * @param string $businessType 业务类型
     *
     * @return array<string, mixed> 策略配置
     */
    public function getVerificationStrategy(string $businessType): array;

    /**
     * 检查用户验证历史
     *
     * @param string                  $userId       用户ID
     * @param string                  $businessType 业务类型
     * @param \DateTimeInterface|null $since        起始时间
     *
     * @return array<string, mixed> 验证历史统计
     */
    public function getUserVerificationHistory(
        string $userId,
        string $businessType,
        ?\DateTimeInterface $since = null,
    ): array;

    /**
     * 检查是否满足验证频率限制
     *
     * @param string $userId       用户ID
     * @param string $businessType 业务类型
     *
     * @return bool 是否满足频率限制
     */
    public function checkVerificationFrequency(
        string $userId,
        string $businessType,
    ): bool;

    /**
     * 检查是否在验证时间窗口内
     *
     * @param string $userId       用户ID
     * @param string $businessType 业务类型
     *
     * @return bool 是否在时间窗口内
     */
    public function checkVerificationTimeWindow(
        string $userId,
        string $businessType,
    ): bool;

    /**
     * 评估验证风险等级
     *
     * @param string               $userId       用户ID
     * @param string               $businessType 业务类型
     * @param array<string, mixed> $context      业务上下文
     *
     * @return string 风险等级 (low, medium, high)
     */
    public function assessVerificationRisk(
        string $userId,
        string $businessType,
        array $context = [],
    ): string;

    /**
     * 获取验证配置参数
     *
     * @param string $businessType 业务类型
     * @param string $configKey    配置键
     * @param mixed  $default      默认值
     *
     * @return mixed 配置值
     */
    public function getVerificationConfig(
        string $businessType,
        string $configKey,
        mixed $default = null,
    ): mixed;
}
