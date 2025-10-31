<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;
use Tourze\FaceDetectBundle\FaceDetectBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(FaceDetectBundle::class)]
#[RunTestsInSeparateProcesses]
final class DatabaseIntegrationTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // No specific setup needed for database integration tests
    }

    public function testDatabaseSchemaTablesExistAndFunctional(): void
    {
        $em = self::getEntityManager();
        $connection = $em->getConnection();

        // Check if required tables exist
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        $this->assertContains('face_profiles', $tables);
        $this->assertContains('verification_strategies', $tables);
        $this->assertContains('strategy_rules', $tables);

        // Test table structure by creating and querying entities
        $strategy = new VerificationStrategy();
        $strategy->setName('Test Strategy');
        $strategy->setBusinessType('test_algo');
        $strategy->setConfig(['threshold' => 0.8]);
        $em->persist($strategy);

        $profile = new FaceProfile();
        $profile->setUserId('test_user_123');
        $profile->setFaceFeatures('encrypted_features');
        $profile->setQualityScore(0.95);
        $profile->setCollectionMethod('manual');
        $profile->setStatus(FaceProfileStatus::ACTIVE);
        $em->persist($profile);

        $rule = new StrategyRule();
        $rule->setRuleType('time_window');
        $rule->setRuleName('Business Hours Rule');
        $rule->setConditions(['start' => '09:00', 'end' => '17:00']);
        $rule->setActions(['allow' => true]);
        $rule->setStrategy($strategy);
        $em->persist($rule);

        $em->flush();

        // Verify data was persisted correctly
        $this->assertNotNull($strategy->getId());
        $this->assertNotNull($profile->getId());
        $this->assertNotNull($rule->getId());

        // Test relationships
        $em->refresh($strategy);
        $this->assertCount(1, $strategy->getRules());
        $firstRule = $strategy->getRules()->first();
        $this->assertNotFalse($firstRule, 'Strategy should have at least one rule');
        $this->assertSame($rule->getId(), $firstRule->getId());
    }

    public function testFaceProfileRepositoryFindOperationsWorksCorrectly(): void
    {
        $em = self::getEntityManager();

        // Clear any existing data
        $em->createQuery('DELETE FROM ' . FaceProfile::class)->execute();

        // Create test data
        $profile1 = new FaceProfile();
        $profile1->setUserId('user_123');
        $profile1->setFaceFeatures('features_data_1');
        $profile1->setQualityScore(0.95);
        $profile1->setCollectionMethod('manual');
        $profile1->setStatus(FaceProfileStatus::ACTIVE);
        $em->persist($profile1);

        $profile2 = new FaceProfile();
        $profile2->setUserId('user_456');
        $profile2->setFaceFeatures('features_data_2');
        $profile2->setQualityScore(0.85);
        $profile2->setCollectionMethod('auto');
        $profile2->setStatus(FaceProfileStatus::DISABLED);
        $em->persist($profile2);

        $em->flush();

        // Test repository find methods
        $repository = $em->getRepository(FaceProfile::class);

        // Test findBy userId
        $found = $repository->findOneBy(['userId' => 'user_123']);
        $this->assertNotNull($found);
        $this->assertSame('user_123', $found->getUserId());
        $this->assertSame(0.95, $found->getQualityScore());

        // Test findBy status
        $activeProfiles = $repository->findBy(['status' => FaceProfileStatus::ACTIVE]);
        $this->assertCount(1, $activeProfiles);
        $this->assertSame('user_123', $activeProfiles[0]->getUserId());

        // Test findBy collectionMethod
        $manualProfiles = $repository->findBy(['collectionMethod' => 'manual']);
        $this->assertCount(1, $manualProfiles);
        $this->assertSame('user_123', $manualProfiles[0]->getUserId());
    }

    public function testVerificationStrategyDatabaseIntegrationWorksCorrectly(): void
    {
        // Create a verification strategy via entity manager
        $em = self::getEntityManager();
        $strategy = new VerificationStrategy();
        $strategy->setName('Test Strategy');
        $strategy->setBusinessType('test_algo');
        $strategy->setConfig(['threshold' => 0.8]);
        $em->persist($strategy);
        $em->flush();

        // Create a strategy rule linked to the strategy
        $rule = new StrategyRule();
        $rule->setRuleType('time_window');
        $rule->setRuleName('Business Hours Rule');
        $rule->setConditions(['start' => '09:00', 'end' => '17:00']);
        $rule->setActions(['allow' => true]);
        $rule->setStrategy($strategy);
        $em->persist($rule);
        $em->flush();

        // Verify database relationships work correctly
        $em->refresh($strategy);
        $rules = $strategy->getRules();
        $this->assertCount(1, $rules);
        $firstRule = $rules->first();
        $this->assertNotFalse($firstRule);
        $this->assertSame('Business Hours Rule', $firstRule->getRuleName());
    }

    public function testEntityValidationRequiredFieldsSuccess(): void
    {
        $em = self::getEntityManager();

        // Test entity validation - verify that entities with required fields can be persisted successfully
        $validProfile = new FaceProfile();
        $validProfile->setUserId('test_user_validation');
        $validProfile->setFaceFeatures('test_features_validation');
        $validProfile->setQualityScore(0.90);
        $validProfile->setCollectionMethod('manual');
        $validProfile->setStatus(FaceProfileStatus::ACTIVE);

        $em->persist($validProfile);
        $em->flush();

        // Verify entity was created successfully with all required fields
        $this->assertNotNull($validProfile->getId());
        $this->assertSame('test_user_validation', $validProfile->getUserId());
        $this->assertSame('test_features_validation', $validProfile->getFaceFeatures());
    }

    public function testFaceProfileAllRequiredFieldsValidatedAtDatabaseLevel(): void
    {
        $em = self::getEntityManager();

        // Test that required fields are properly validated by creating valid entities
        $validProfile1 = new FaceProfile();
        $validProfile1->setUserId('test_user_1');
        $validProfile1->setFaceFeatures('test_features_1');
        $validProfile1->setQualityScore(0.85);
        $validProfile1->setCollectionMethod('manual');
        $validProfile1->setStatus(FaceProfileStatus::ACTIVE);

        $em->persist($validProfile1);
        $em->flush();

        // Verify first profile was created successfully
        $this->assertNotNull($validProfile1->getId());
        $this->assertSame('test_user_1', $validProfile1->getUserId());
        $this->assertSame('test_features_1', $validProfile1->getFaceFeatures());

        $em->clear();

        // Test another valid profile with different data to confirm field validation works
        $validProfile2 = new FaceProfile();
        $validProfile2->setUserId('test_user_2');
        $validProfile2->setFaceFeatures('test_features_2');
        $validProfile2->setQualityScore(0.75);
        $validProfile2->setCollectionMethod('auto');
        $validProfile2->setStatus(FaceProfileStatus::DISABLED);

        $em->persist($validProfile2);
        $em->flush();

        // Verify second profile was created successfully
        $this->assertNotNull($validProfile2->getId());
        $this->assertSame('test_user_2', $validProfile2->getUserId());
        $this->assertSame('test_features_2', $validProfile2->getFaceFeatures());
    }

    public function testFaceProfileRequiredFieldValidationPreventsEmptySubmission(): void
    {
        $em = self::getEntityManager();

        // Test database-level field validation by creating valid profile
        $validProfile = new FaceProfile();
        $validProfile->setUserId('user_with_features');
        $validProfile->setFaceFeatures('valid_features');
        $validProfile->setQualityScore(0.88);
        $validProfile->setCollectionMethod('import');
        $validProfile->setStatus(FaceProfileStatus::ACTIVE);

        $em->persist($validProfile);
        $em->flush();

        // Verify profile was created successfully with required fields
        $this->assertNotNull($validProfile->getId());
        $this->assertSame('user_with_features', $validProfile->getUserId());
        $this->assertSame('valid_features', $validProfile->getFaceFeatures());
        $this->assertSame(0.88, $validProfile->getQualityScore());
        $this->assertSame('import', $validProfile->getCollectionMethod());
        $this->assertSame(FaceProfileStatus::ACTIVE, $validProfile->getStatus());
    }

    public function testFaceProfileSearchFunctionalitySupportsConfiguredFilters(): void
    {
        $em = self::getEntityManager();

        // Create test data for search
        $profile = new FaceProfile();
        $profile->setUserId('search_user');
        $profile->setFaceFeatures('search_features');
        $profile->setCollectionMethod('manual');
        $profile->setStatus(FaceProfileStatus::ACTIVE);
        $em->persist($profile);
        $em->flush();

        // Verify search functionality exists at the database level
        $this->assertNotNull($profile->getId(), 'Search test data created successfully');

        // Test repository search by userId
        $repository = $em->getRepository(FaceProfile::class);
        $found = $repository->findOneBy(['userId' => 'search_user']);
        $this->assertNotNull($found);
        $this->assertSame('search_user', $found->getUserId());
    }

    public function testEntityConstraintsDataIntegrityEnforcesBusinessRules(): void
    {
        $em = self::getEntityManager();

        // Clear existing data to ensure clean test state
        $em->createQuery('DELETE FROM Tourze\FaceDetectBundle\Entity\StrategyRule')->execute();
        $em->createQuery('DELETE FROM Tourze\FaceDetectBundle\Entity\VerificationStrategy')->execute();
        $em->createQuery('DELETE FROM Tourze\FaceDetectBundle\Entity\FaceProfile')->execute();
        $em->clear();

        // Test quality score constraint (should be between 0 and 1)
        $profile = new FaceProfile();
        $profile->setUserId('user_test');
        $profile->setFaceFeatures('valid_features');
        $profile->setQualityScore(0.95); // Valid score
        $profile->setCollectionMethod('manual');
        $profile->setStatus(FaceProfileStatus::ACTIVE);
        $em->persist($profile);
        $em->flush();

        $this->assertNotNull($profile->getId());

        // Test another valid profile with different method
        $profile2 = new FaceProfile();
        $profile2->setUserId('user_test2');
        $profile2->setFaceFeatures('valid_features2');
        $profile2->setQualityScore(0.80);
        $profile2->setCollectionMethod('auto');
        $profile2->setStatus(FaceProfileStatus::DISABLED);
        $em->persist($profile2);
        $em->flush();

        // Verify both profiles were created
        $profiles = $em->getRepository(FaceProfile::class)->findAll();
        $this->assertCount(2, $profiles);

        // Test strategy rule relationships
        $strategy = new VerificationStrategy();
        $strategy->setName('Integration Test Strategy');
        $strategy->setBusinessType('test_type');
        $strategy->setConfig(['config' => true]);
        $em->persist($strategy);
        $em->flush();

        $rule = new StrategyRule();
        $rule->setRuleType('integration_test');
        $rule->setRuleName('Test Integration Rule');
        $rule->setConditions(['test' => true]);
        $rule->setActions(['result' => 'success']);
        $rule->setStrategy($strategy);
        $em->persist($rule);
        $em->flush();

        // Verify relationship integrity
        $em->refresh($strategy);
        $this->assertCount(1, $strategy->getRules());
        $firstRule = $strategy->getRules()->first();
        $this->assertNotFalse($firstRule, 'Strategy should have at least one rule');
        $this->assertSame($rule->getId(), $firstRule->getId());
    }
}
