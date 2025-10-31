<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

class FaceProfileFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faceProfile1 = new FaceProfile();
        $faceProfile1->setUserId('user_001');
        $faceProfile1->setFaceFeatures('encrypted_face_features_data_1');
        $faceProfile1->setQualityScore(0.95);
        $faceProfile1->setCollectionMethod('manual');
        $faceProfile1->setDeviceInfo(['device' => 'mobile', 'version' => '1.0']);
        $faceProfile1->setStatus(FaceProfileStatus::ACTIVE);
        $manager->persist($faceProfile1);

        $faceProfile2 = new FaceProfile();
        $faceProfile2->setUserId('user_002');
        $faceProfile2->setFaceFeatures('encrypted_face_features_data_2');
        $faceProfile2->setQualityScore(0.87);
        $faceProfile2->setCollectionMethod('auto');
        $faceProfile2->setStatus(FaceProfileStatus::DISABLED);
        $manager->persist($faceProfile2);

        $faceProfile3 = new FaceProfile();
        $faceProfile3->setUserId('user_003');
        $faceProfile3->setFaceFeatures('encrypted_face_features_data_3');
        $faceProfile3->setQualityScore(0.72);
        $faceProfile3->setCollectionMethod('import');
        $faceProfile3->setStatus(FaceProfileStatus::ACTIVE);
        $faceProfile3->setExpiresAfter(new \DateInterval('P30D'));
        $manager->persist($faceProfile3);

        $manager->flush();
    }
}
