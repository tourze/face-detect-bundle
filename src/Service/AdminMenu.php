<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

/**
 * 人脸识别菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (!$item->getChild('人脸识别')) {
            $item->addChild('人脸识别');
        }

        $faceDetectMenu = $item->getChild('人脸识别');

        // 人脸档案管理菜单
        $faceDetectMenu->addChild('人脸档案')
            ->setUri($this->linkGenerator->getCurdListPage(FaceProfile::class))
            ->setAttribute('icon', 'fas fa-user-circle');

        // 验证策略管理菜单
        $faceDetectMenu->addChild('验证策略')
            ->setUri($this->linkGenerator->getCurdListPage(VerificationStrategy::class))
            ->setAttribute('icon', 'fas fa-shield-alt');

        // 策略规则管理菜单
        $faceDetectMenu->addChild('策略规则')
            ->setUri($this->linkGenerator->getCurdListPage(StrategyRule::class))
            ->setAttribute('icon', 'fas fa-cogs');

        // 验证记录菜单
        $faceDetectMenu->addChild('验证记录')
            ->setUri($this->linkGenerator->getCurdListPage(VerificationRecord::class))
            ->setAttribute('icon', 'fas fa-history');

        // 操作日志菜单
        $faceDetectMenu->addChild('操作日志')
            ->setUri($this->linkGenerator->getCurdListPage(OperationLog::class))
            ->setAttribute('icon', 'fas fa-clipboard-list');
    }
}
