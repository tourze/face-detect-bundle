<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Controller\Admin;

use Doctrine\ORM\Tools\SchemaTool;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\FaceDetectBundle\Controller\Admin\OperationLogCrudController;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OperationLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OperationLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testIndexPageAccessRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/face-detect/operation-log');

        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/face-detect/operation-log');

        self::getClient($client);
        $this->assertResponseRedirects();
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testIndexPageRendersSuccessfullyWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $client->request('GET', '/admin/face-detect/operation-log');

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '操作日志');
    }

    public function testNewActionIsDisabled(): void
    {
        $this->expectException(ForbiddenActionException::class);

        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();

        $client->request('GET', '/admin/face-detect/operation-log/new');
    }

    public function testEditActionIsDisabled(): void
    {
        $this->expectException(ForbiddenActionException::class);

        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();
        $operationLog = $this->createOperationLog();

        $client->request('GET', "/admin/face-detect/operation-log/{$operationLog->getId()}/edit");
    }

    public function testDeleteActionIsDisabled(): void
    {
        $this->expectException(ForbiddenActionException::class);

        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();
        $operationLog = $this->createOperationLog();

        $client->request('POST', "/admin/face-detect/operation-log/{$operationLog->getId()}/delete");
    }

    public function testDetailPageRequiresAuthentication(): void
    {
        $client = self::createClient();
        $operationLog = $this->createOperationLog();

        $client->request('GET', "/admin/face-detect/operation-log/{$operationLog->getId()}");

        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testDetailPageRendersSuccessfullyWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $operationLog = $this->createOperationLog();

        $client->request('GET', "/admin/face-detect/operation-log/{$operationLog->getId()}");

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $operationLog->getUserId());
        $this->assertSelectorTextContains('body', $operationLog->getOperationId());
    }

    public function testSearchFunctionalityWithUserId(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $operationLog1 = $this->createOperationLog('search_user_123', 'op_001', 'payment');
        $operationLog2 = $this->createOperationLog('another_user_456', 'op_002', 'login');

        $client->request('GET', '/admin/face-detect/operation-log', [
            'query' => 'search_user',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'search_user_123');
        $this->assertSelectorTextNotContains('body', 'another_user_456');
    }

    public function testSearchFunctionalityWithOperationId(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $operationLog = $this->createOperationLog('user_123', 'search_operation_123', 'payment');

        $client->request('GET', '/admin/face-detect/operation-log', [
            'query' => 'search_operation',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'search_operation_123');
    }

    public function testSearchFunctionalityWithOperationType(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $operationLog = $this->createOperationLog('user_123', 'op_001', 'payment_transfer');

        $client->request('GET', '/admin/face-detect/operation-log', [
            'query' => 'payment_transfer',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'payment_transfer');
    }

    public function testFilterByUserId(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $userLog = $this->createOperationLog('filter_user_123', 'op_001', 'payment');
        $otherLog = $this->createOperationLog('other_user_456', 'op_002', 'login');

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[userId]' => 'filter_user_123',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'filter_user_123');
    }

    public function testFilterByOperationType(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $paymentLog = $this->createOperationLog('user_123', 'op_001', 'payment');
        $loginLog = $this->createOperationLog('user_456', 'op_002', 'login');

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[operationType]' => 'payment',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'payment');
    }

    public function testFilterByVerificationRequired(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $verificationLog = $this->createOperationLog('user_123', 'op_001', 'payment');
        $verificationLog->setVerificationRequired(true);
        self::getEntityManager()->flush();

        $noVerificationLog = $this->createOperationLog('user_456', 'op_002', 'login');
        $noVerificationLog->setVerificationRequired(false);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[verificationRequired]' => '1',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'op_001');
    }

    public function testFilterByVerificationCompleted(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $completedLog = $this->createOperationLog('user_123', 'op_001', 'payment');
        $completedLog->setVerificationCompleted(true);
        self::getEntityManager()->flush();

        $pendingLog = $this->createOperationLog('user_456', 'op_002', 'transfer');
        $pendingLog->setVerificationCompleted(false);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[verificationCompleted]' => '1',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'op_001');
    }

    public function testFilterByStatus(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $completedLog = $this->createOperationLog('user_123', 'op_001', 'payment');
        $completedLog->setStatus(OperationStatus::COMPLETED);
        self::getEntityManager()->flush();

        $pendingLog = $this->createOperationLog('user_456', 'op_002', 'transfer');
        $pendingLog->setStatus(OperationStatus::PENDING);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[status]' => OperationStatus::COMPLETED->value,
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'op_001');
    }

    public function testFilterByVerificationCount(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $multiVerificationLog = $this->createOperationLog('user_123', 'op_001', 'payment');
        $multiVerificationLog->setVerificationCount(3);
        self::getEntityManager()->flush();

        $singleVerificationLog = $this->createOperationLog('user_456', 'op_002', 'transfer');
        $singleVerificationLog->setVerificationCount(1);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/operation-log', [
            'filters[verificationCount][comparison]' => '>',
            'filters[verificationCount][value]' => '2',
        ]);

        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'op_001');
    }

    public function testPaginationWorks(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        // Create more than 20 records to test pagination
        for ($i = 1; $i <= 25; ++$i) {
            $this->createOperationLog("user_{$i}", "op_{$i}", 'test_operation');
        }

        $client->request('GET', '/admin/face-detect/operation-log');

        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Check if pagination controls exist
        $this->assertSelectorExists('.pagination');
    }

    public function testDefaultSortingByIdDesc(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $oldLog = $this->createOperationLog('user_1', 'op_old', 'payment');
        self::getEntityManager()->flush();
        self::getEntityManager()->clear();

        $newLog = $this->createOperationLog('user_2', 'op_new', 'transfer');
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/operation-log');

        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Newer log should appear first due to DESC sorting
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content);
        $posNew = strpos($content, 'op_new');
        $posOld = strpos($content, 'op_old');

        $this->assertNotFalse($posNew);
        $this->assertNotFalse($posOld);
        $this->assertLessThan($posOld, $posNew);
    }

    private function createOperationLog(string $userId = 'test_user', string $operationId = 'test_op_123', string $operationType = 'test_operation'): OperationLog
    {
        $this->createDatabaseSchema();

        $operationLog = new OperationLog();
        $operationLog->setUserId($userId);
        $operationLog->setOperationId($operationId);
        $operationLog->setOperationType($operationType);

        $entityManager = self::getEntityManager();
        $entityManager->persist($operationLog);
        $entityManager->flush();

        return $operationLog;
    }

    private function authenticateUser(KernelBrowser $client): void
    {
        // 使用简单的模拟认证，避免依赖 biz_user 表
        $client->loginUser(new InMemoryUser('admin', 'password', ['ROLE_ADMIN']));
    }

    private function createDatabaseSchema(): void
    {
        $entityManager = self::getEntityManager();
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->updateSchema($metadata);
    }

    protected function getControllerService(): OperationLogCrudController
    {
        return self::getService(OperationLogCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'userId' => ['用户ID'];
        yield 'operationId' => ['操作ID'];
        yield 'operationType' => ['操作类型'];
        yield 'verificationRequired' => ['需要验证'];
        yield 'verificationCompleted' => ['验证完成'];
        yield 'verificationCount' => ['验证次数'];
        yield 'minVerificationCount' => ['最少验证次数'];
        yield 'status' => ['操作状态'];
        yield 'startTime' => ['开始时间'];
        yield 'completedTime' => ['完成时间'];
    }

    /**
     * NEW操作数据提供器
     *
     * 说明：OperationLogCrudController是只读控制器，禁用了NEW操作。
     * 理想情况下，相关测试应该被跳过，但测试框架的isActionEnabled方法
     * 无法正确检测被禁用的操作，导致以下问题：
     *
     * 1. 如果返回空数组：PHPUnit报"Empty data set provided by data provider"
     * 2. 如果提供数据：测试运行但抛出ForbiddenActionException
     *
     * 我们选择提供最小的虚拟数据，让测试正确地失败（抛出期望的异常），
     * 证明操作确实被禁用。这比数据提供器错误更有意义。
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 提供最小的虚拟数据，测试会因操作被禁用而正确失败
        yield 'readonly_controller_test' => ['dummyField'];
    }

    public function testValidationErrors(): void
    {
        // OperationLogCrudController禁用了NEW操作，使用Validator服务直接测试实体验证
        $client = self::createClientWithDatabase();

        $validator = self::getContainer()->get('validator');
        $this->assertInstanceOf(ValidatorInterface::class, $validator);

        // 测试空的OperationLog实体应该有验证错误
        $operationLog = new OperationLog();
        $violations = $validator->validate($operationLog);
        $this->assertGreaterThan(0, $violations->count(), '空的OperationLog实体应该有验证错误');

        // 验证必填字段的"should not be blank"错误
        $foundNotBlankMessages = [];
        $requiredFields = ['userId', 'operationId', 'operationType'];

        foreach ($violations as $violation) {
            $message = (string) $violation->getMessage();
            if (str_contains($message, 'should not be blank') || str_contains($message, 'This value should not be blank')) {
                $foundNotBlankMessages[] = $violation->getPropertyPath() . ': ' . $message;
            }
        }

        $this->assertNotEmpty($foundNotBlankMessages, 'Should find "should not be blank" validation messages for required fields');

        // 验证具体的必填字段都有验证错误
        $foundErrors = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            if (in_array($propertyPath, $requiredFields, true)) {
                $foundErrors[] = $propertyPath;
            }
        }

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $foundErrors, "应该有 {$field} 字段验证错误");
        }
    }

    /**
     * EDIT操作数据提供器
     *
     * 说明：OperationLogCrudController是只读控制器，禁用了EDIT操作。
     * 理想情况下，相关测试应该被跳过，但测试框架的isActionEnabled方法
     * 无法正确检测被禁用的操作。
     *
     * 我们提供最小的虚拟数据，让测试正确地失败（抛出ForbiddenActionException），
     * 证明操作确实被禁用。
     *
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 提供最小的虚拟数据，测试会因操作被禁用而正确失败
        yield 'readonly_controller_test' => ['dummyField'];
    }

    /**
     * 直接验证控制器配置，替代有问题的基类测试
     * 由于基类的isActionEnabled方法有缺陷，我们通过异常来验证操作确实被禁用
     */
    public function testOperationLogOnlyAllowsReadOnlyActions(): void
    {
        // 验证NEW操作被禁用
        $this->expectException(ForbiddenActionException::class);
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();
        $client->request('GET', '/admin/face-detect/operation-log/new');
    }
}
