<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Enum\VerificationResult;
use Tourze\FaceDetectBundle\Enum\VerificationType;

class VerificationRecordFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $paymentStrategy = $this->getReference(VerificationStrategyFixtures::STRATEGY_PAYMENT_REFERENCE, VerificationStrategy::class);
        assert($paymentStrategy instanceof VerificationStrategy);

        $record1 = new VerificationRecord();
        $record1->setUserId('user_001');
        $record1->setStrategy($paymentStrategy);
        $record1->setBusinessType('payment');
        $record1->setResult(VerificationResult::SUCCESS);
        $record1->setOperationId('op_payment_001');
        $record1->setVerificationType(VerificationType::REQUIRED);
        $record1->setConfidenceScore(0.96);
        $record1->setVerificationTime(1.234);
        $record1->setClientInfo(['device' => 'iPhone', 'version' => '15.0', 'app_version' => '2.1.0']);
        $manager->persist($record1);

        $record2 = new VerificationRecord();
        $record2->setUserId('user_002');
        $record2->setStrategy($paymentStrategy);
        $record2->setBusinessType('payment');
        $record2->setResult(VerificationResult::FAILED);
        $record2->setOperationId('op_payment_002');
        $record2->setVerificationType(VerificationType::REQUIRED);
        $record2->setConfidenceScore(0.45);
        $record2->setVerificationTime(2.567);
        $record2->setError('FACE_NOT_MATCH', '人脸匹配度不足');
        $record2->setClientInfo(['device' => 'Android', 'version' => '12.0']);
        $manager->persist($record2);

        $loginStrategy = $this->getReference(VerificationStrategyFixtures::STRATEGY_LOGIN_REFERENCE, VerificationStrategy::class);
        assert($loginStrategy instanceof VerificationStrategy);

        $record3 = new VerificationRecord();
        $record3->setUserId('user_003');
        $record3->setStrategy($loginStrategy);
        $record3->setBusinessType('login');
        $record3->setResult(VerificationResult::SUCCESS);
        $record3->setVerificationType(VerificationType::OPTIONAL);
        $record3->setConfidenceScore(0.78);
        $record3->setVerificationTime(0.845);
        $record3->setClientInfo(['device' => 'Web', 'browser' => 'Chrome', 'version' => '91.0']);
        $manager->persist($record3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VerificationStrategyFixtures::class,
        ];
    }
}
