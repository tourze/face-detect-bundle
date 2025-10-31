<?php

namespace Tourze\FaceDetectBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\FaceDetectBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 *
 * @phpstan-ignore-next-line
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 设置测试环境
    }

    public function testAdminMenuImplementsMenuProviderInterface(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testInvokeIsCallable(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $reflection = new \ReflectionClass($adminMenu);
        $this->assertTrue($reflection->hasMethod('__invoke'));
        $this->assertTrue($reflection->getMethod('__invoke')->isPublic());
    }

    public function testInvokeWithValidLinkGeneratorAddsMenu(): void
    {
        // 创建 LinkGenerator mock 并注入到容器
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->willReturn('/admin/test')
        ;

        // 将 mock 的 LinkGenerator 注入到容器中
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);

        // 从容器获取 AdminMenu 服务实例
        $adminMenu = self::getService(AdminMenu::class);

        $item = $this->createMock(ItemInterface::class);
        $faceDetectItem = $this->createMock(ItemInterface::class);

        $item->expects($this->exactly(2))
            ->method('getChild')
            ->with('人脸识别')
            ->willReturnOnConsecutiveCalls(null, $faceDetectItem)
        ;

        $item->expects($this->once())
            ->method('addChild')
            ->with('人脸识别')
            ->willReturn($faceDetectItem)
        ;

        $faceDetectItem->expects($this->exactly(5))
            ->method('addChild')
        ;

        $adminMenu($item);
    }
}
