<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Contract;

use Tourze\FaceDetectBundle\Entity\VerificationRecord;

/**
 * 验证完成服务接口
 */
interface VerificationCompletionServiceInterface
{
    /**
     * 处理验证完成后的业务逻辑
     *
     * @param VerificationRecord $verificationRecord 验证记录
     */
    public function handleVerificationCompletion(VerificationRecord $verificationRecord): void;
}
