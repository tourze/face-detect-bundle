<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

class OperationLogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $operationLog1 = new OperationLog();
        $operationLog1->setUserId('user_001');
        $operationLog1->setOperationId('op_login_001');
        $operationLog1->setOperationType('user_login');
        $operationLog1->setBusinessContext(['ip' => '192.168.1.1', 'device' => 'mobile']);
        $operationLog1->setVerificationRequired(true);
        $operationLog1->setMinVerificationCount(1);
        $operationLog1->setStatus(OperationStatus::PENDING);
        $manager->persist($operationLog1);

        $operationLog2 = new OperationLog();
        $operationLog2->setUserId('user_002');
        $operationLog2->setOperationId('op_payment_002');
        $operationLog2->setOperationType('payment_process');
        $operationLog2->setBusinessContext(['amount' => 100.00, 'currency' => 'CNY']);
        $operationLog2->setVerificationRequired(true);
        $operationLog2->setVerificationCompleted(true);
        $operationLog2->setMinVerificationCount(2);
        $operationLog2->setVerificationCount(2);
        $operationLog2->setStatus(OperationStatus::COMPLETED);
        $manager->persist($operationLog2);

        $operationLog3 = new OperationLog();
        $operationLog3->setUserId('user_003');
        $operationLog3->setOperationId('op_transfer_003');
        $operationLog3->setOperationType('fund_transfer');
        $operationLog3->setBusinessContext(['target_account' => 'acc_456', 'amount' => 500.00]);
        $operationLog3->setVerificationRequired(false);
        $operationLog3->setStatus(OperationStatus::COMPLETED);
        $manager->persist($operationLog3);

        $manager->flush();
    }
}
