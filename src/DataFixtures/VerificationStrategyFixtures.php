<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

class VerificationStrategyFixtures extends Fixture
{
    public const STRATEGY_PAYMENT_REFERENCE = 'strategy-payment';
    public const STRATEGY_LOGIN_REFERENCE = 'strategy-login';
    public const STRATEGY_IDENTITY_REFERENCE = 'strategy-identity';

    public function load(ObjectManager $manager): void
    {
        $strategy1 = new VerificationStrategy();
        $strategy1->setName('高安全支付策略');
        $strategy1->setBusinessType('payment');
        $strategy1->setConfig([
            'min_confidence' => 0.9,
            'max_attempts' => 3,
            'timeout' => 30,
        ]);
        $strategy1->setDescription('用于高额支付场景的人脸验证策略');
        $strategy1->setPriority(10);
        $strategy1->setEnabled(true);
        $manager->persist($strategy1);

        $strategy2 = new VerificationStrategy();
        $strategy2->setName('普通登录策略');
        $strategy2->setBusinessType('login');
        $strategy2->setConfig([
            'min_confidence' => 0.7,
            'max_attempts' => 5,
            'timeout' => 60,
        ]);
        $strategy2->setDescription('用于日常登录场景的人脸验证策略');
        $strategy2->setPriority(5);
        $strategy2->setEnabled(true);
        $manager->persist($strategy2);

        $strategy3 = new VerificationStrategy();
        $strategy3->setName('身份验证策略');
        $strategy3->setBusinessType('identity_verification');
        $strategy3->setConfig([
            'min_confidence' => 0.95,
            'max_attempts' => 2,
            'timeout' => 20,
        ]);
        $strategy3->setDescription('用于身份认证场景的严格验证策略');
        $strategy3->setPriority(15);
        $strategy3->setEnabled(false);
        $manager->persist($strategy3);

        $manager->flush();

        $this->addReference(self::STRATEGY_PAYMENT_REFERENCE, $strategy1);
        $this->addReference(self::STRATEGY_LOGIN_REFERENCE, $strategy2);
        $this->addReference(self::STRATEGY_IDENTITY_REFERENCE, $strategy3);
    }
}
