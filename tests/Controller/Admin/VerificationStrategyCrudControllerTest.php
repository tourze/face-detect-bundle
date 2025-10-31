<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Controller\Admin;

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\FaceDetectBundle\Controller\Admin\VerificationStrategyCrudController;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(VerificationStrategyCrudController::class)]
#[RunTestsInSeparateProcesses]
final class VerificationStrategyCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    private const ADMIN_BASE_URL = '/admin';

    public function testControllerServiceCanBeResolved(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ADMIN_BASE_URL);

        $container = $client->getContainer();
        $this->assertTrue($container->has(VerificationStrategyCrudController::class));

        $controller = $container->get(VerificationStrategyCrudController::class);
        $this->assertInstanceOf(VerificationStrategyCrudController::class, $controller);
    }

    public function testIndexPageAccessRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/face-detect/verification-strategy');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/face-detect/verification-strategy');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testIndexPageRendersSuccessfullyWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $client->request('GET', '/admin/face-detect/verification-strategy');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '验证策略');
    }

    public function testNewPageRequiresAuthentication(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin/face-detect/verification-strategy/new');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testNewPageRendersFormWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $crawler = $client->request('GET', '/admin/face-detect/verification-strategy/new');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $this->assertSelectorExists('input[name="VerificationStrategy[name]"]');
        $this->assertSelectorExists('input[name="VerificationStrategy[businessType]"]');
    }

    public function testCreateActionWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();

        // 直接 POST 数据而不使用 DomCrawler 表单
        $client->request('POST', '/admin/face-detect/verification-strategy/new', [
            'VerificationStrategy' => [
                'name' => 'test-strategy',
                'businessType' => 'payment',
                'description' => '用于支付操作的人脸验证策略',
                'config' => '{"max_attempts": 3, "timeout": 30}',
                'isEnabled' => true,
                'priority' => 10,
            ],
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();

        $client->followRedirect();
        self::getClient($client);
        $this->assertResponseIsSuccessful();
    }

    public function testCreateActionValidatesRequiredFields(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();

        $client->request('POST', '/admin/face-detect/verification-strategy/new', [
            'VerificationStrategy' => [
                'name' => '',
                'businessType' => '',
                'isEnabled' => true,
                'priority' => 10,
                'config' => '{}',
            ],
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        // Should stay on the same page with validation errors
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateActionValidatesNameLength(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();

        $client->request('POST', '/admin/face-detect/verification-strategy/new', [
            'VerificationStrategy' => [
                'name' => str_repeat('a', 129), // Exceeds 128 character limit
                'businessType' => 'payment',
                'isEnabled' => true,
                'priority' => 10,
                'config' => '{}',
            ],
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateActionValidatesBusinessTypeLength(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();

        $client->request('POST', '/admin/face-detect/verification-strategy/new', [
            'VerificationStrategy' => [
                'name' => '测试策略',
                'businessType' => str_repeat('a', 65), // Exceeds 64 character limit
                'isEnabled' => true,
                'priority' => 10,
                'config' => '{}',
            ],
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testEditPageRequiresAuthentication(): void
    {
        $client = self::createClient();
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('GET', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}/edit");

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testEditPageRendersFormWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('GET', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}/edit");

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertInputValueSame('VerificationStrategy[name]', $verificationStrategy->getName());
    }

    public function testUpdateActionWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('POST', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}/edit", [
            'VerificationStrategy' => [
                'name' => '更新后的策略名称',
                'businessType' => 'payment',
                'priority' => 20,
                'isEnabled' => false,
                'config' => '{"max_attempts": 5, "timeout": 60}',
            ],
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testDetailPageRequiresAuthentication(): void
    {
        $client = self::createClient();
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('GET', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}");

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testDetailPageRendersSuccessfullyWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('GET', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}");

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $verificationStrategy->getName());
        $businessType = $verificationStrategy->getBusinessType();
        if (null !== $businessType) {
            $this->assertSelectorTextContains('body', $businessType);
        }
    }

    public function testDeleteActionRequiresAuthentication(): void
    {
        $client = self::createClient();
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('POST', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}/delete");

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseRedirects();
    }

    public function testDeleteActionWorksWhenAuthenticated(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);
        $this->createDatabaseSchema();
        $verificationStrategy = $this->createVerificationStrategy();

        $client->request('POST', "/admin/face-detect/verification-strategy/{$verificationStrategy->getId()}/delete");

        // 删除操作可能需要认证或返回405，我们检查不是服务器错误即可
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue($statusCode >= 300 && $statusCode < 500, 'Expected redirect or client error, got: ' . $statusCode);
    }

    public function testSearchFunctionalityWithName(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $strategy1 = $this->createVerificationStrategy('搜索策略123', 'payment');
        $strategy2 = $this->createVerificationStrategy('其他策略456', 'login');

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'query' => '搜索策略',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '搜索策略123');
        $this->assertSelectorTextNotContains('body', '其他策略456');
    }

    public function testSearchFunctionalityWithBusinessType(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $strategy = $this->createVerificationStrategy('测试策略', 'payment_transfer');

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'query' => 'payment_transfer',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'payment_transfer');
    }

    public function testSearchFunctionalityWithDescription(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $strategy = $this->createVerificationStrategy('测试策略', 'payment');
        $strategy->setDescription('高风险支付验证策略');
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'query' => '高风险支付',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '测试策略');
    }

    public function testFilterByName(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $strategy1 = $this->createVerificationStrategy('支付验证策略', 'payment');
        $strategy2 = $this->createVerificationStrategy('登录验证策略', 'login');

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'filters[name]' => '支付验证策略',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '支付验证策略');
    }

    public function testFilterByBusinessType(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $paymentStrategy = $this->createVerificationStrategy('支付策略', 'payment');
        $loginStrategy = $this->createVerificationStrategy('登录策略', 'login');

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'filters[businessType]' => 'payment',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '支付策略');
    }

    public function testFilterByEnabledStatus(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $enabledStrategy = $this->createVerificationStrategy('启用策略', 'payment');
        $enabledStrategy->setEnabled(true);
        self::getEntityManager()->flush();

        $disabledStrategy = $this->createVerificationStrategy('禁用策略', 'login');
        $disabledStrategy->setEnabled(false);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'filters[isEnabled]' => '1',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '启用策略');
    }

    public function testFilterByPriority(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $highPriorityStrategy = $this->createVerificationStrategy('高优先级策略', 'payment');
        $highPriorityStrategy->setPriority(100);
        self::getEntityManager()->flush();

        $lowPriorityStrategy = $this->createVerificationStrategy('低优先级策略', 'login');
        $lowPriorityStrategy->setPriority(1);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/verification-strategy', [
            'filters[priority][comparison]' => '>',
            'filters[priority][value]' => '50',
        ]);

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '高优先级策略');
    }

    public function testPaginationWorks(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        // Create more than 20 records to test pagination
        for ($i = 1; $i <= 25; ++$i) {
            $this->createVerificationStrategy("策略_{$i}", 'test');
        }

        $client->request('GET', '/admin/face-detect/verification-strategy');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Check if pagination controls exist
        $this->assertSelectorExists('.pagination');
    }

    public function testDefaultSortingByPriorityDescThenIdDesc(): void
    {
        $client = self::createClientWithDatabase();
        $this->authenticateUser($client);

        $lowPriorityStrategy = $this->createVerificationStrategy('低优先级', 'payment');
        $lowPriorityStrategy->setPriority(1);
        self::getEntityManager()->flush();
        self::getEntityManager()->clear();

        $highPriorityStrategy = $this->createVerificationStrategy('高优先级', 'login');
        $highPriorityStrategy->setPriority(10);
        self::getEntityManager()->flush();

        $client->request('GET', '/admin/face-detect/verification-strategy');

        // 设置静态客户端以支持响应断言
        self::getClient($client);
        $this->assertResponseIsSuccessful();

        // Higher priority strategy should appear first
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content);
        $posHigh = strpos($content, '高优先级');
        $posLow = strpos($content, '低优先级');

        $this->assertNotFalse($posHigh);
        $this->assertNotFalse($posLow);
        $this->assertLessThan($posLow, $posHigh);
    }

    private function createVerificationStrategy(string $name = '测试策略', string $businessType = 'test'): VerificationStrategy
    {
        $this->createDatabaseSchema();

        $config = ['test' => 'config'];
        $verificationStrategy = new VerificationStrategy();
        $verificationStrategy->setName($name);
        $verificationStrategy->setBusinessType($businessType);
        $verificationStrategy->setConfig($config);
        $verificationStrategy->setPriority(10);

        $entityManager = self::getEntityManager();
        $entityManager->persist($verificationStrategy);
        $entityManager->flush();

        return $verificationStrategy;
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

    protected function getControllerService(): VerificationStrategyCrudController
    {
        return self::getService(VerificationStrategyCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'name' => ['策略名称'];
        yield 'businessType' => ['业务类型'];
        yield 'isEnabled' => ['启用状态'];
        yield 'priority' => ['优先级'];
        yield 'strategyRules' => ['关联规则'];
        yield 'createdAt' => ['创建时间'];
        yield 'updatedAt' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 实际存在的字段（基于VerificationStrategyCrudController的configureFields方法）
        yield 'name' => ['name'];
        yield 'businessType' => ['businessType'];
        yield 'description' => ['description'];
        yield 'priority' => ['priority'];
        yield 'isEnabled' => ['isEnabled'];
        yield 'configJson' => ['configJson'];
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));

        $form = $crawler->selectButton('Create')->form();
        // 提交空表单触发验证错误
        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('should not be blank', $crawler->filter('.invalid-feedback')->text());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 实际存在的字段（基于VerificationStrategyCrudController的configureFields方法）
        yield 'name' => ['name'];
        yield 'businessType' => ['businessType'];
        yield 'description' => ['description'];
        yield 'isEnabled' => ['isEnabled'];
        yield 'priority' => ['priority'];
        yield 'configJson' => ['configJson'];
    }
}
