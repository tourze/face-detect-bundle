<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

class StrategyRuleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $actions
     */
    private function createStrategyRule(string $ruleType, string $ruleName, array $conditions, array $actions): StrategyRule
    {
        $rule = new StrategyRule();
        $rule->setRuleType($ruleType);
        $rule->setRuleName($ruleName);
        $rule->setConditions($conditions);
        $rule->setActions($actions);

        return $rule;
    }

    public function load(ObjectManager $manager): void
    {
        $paymentStrategy = $this->getReference(VerificationStrategyFixtures::STRATEGY_PAYMENT_REFERENCE, VerificationStrategy::class);
        assert($paymentStrategy instanceof VerificationStrategy);

        $rule1 = $this->createStrategyRule('amount', '高额支付规则', [
            'min_amount' => 10000,
            'currency' => 'CNY',
        ], [
            'require_verification' => true,
            'min_confidence' => 0.95,
        ]);
        $rule1->setStrategy($paymentStrategy);
        $rule1->setPriority(10);
        $rule1->setEnabled(true);
        $manager->persist($rule1);

        $rule2 = $this->createStrategyRule('time', '非工作时间规则', [
            'start_hour' => 22,
            'end_hour' => 6,
        ], [
            'require_verification' => true,
            'extra_verification' => true,
        ]);
        $rule2->setStrategy($paymentStrategy);
        $rule2->setPriority(5);
        $rule2->setEnabled(true);
        $manager->persist($rule2);

        $loginStrategy = $this->getReference(VerificationStrategyFixtures::STRATEGY_LOGIN_REFERENCE, VerificationStrategy::class);
        assert($loginStrategy instanceof VerificationStrategy);

        $rule3 = $this->createStrategyRule('frequency', '频繁登录规则', [
            'max_attempts' => 5,
            'time_window' => 300,
        ], [
            'require_verification' => true,
            'block_duration' => 900,
        ]);
        $rule3->setStrategy($loginStrategy);
        $rule3->setPriority(8);
        $rule3->setEnabled(true);
        $manager->persist($rule3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VerificationStrategyFixtures::class,
        ];
    }
}
