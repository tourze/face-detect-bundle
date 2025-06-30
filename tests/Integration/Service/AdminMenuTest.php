<?php

namespace Tourze\FaceDetectBundle\Tests\Integration\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Entity\StrategyRule;
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;
use Tourze\FaceDetectBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface $linkGenerator;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function test_admin_menu_can_be_instantiated(): void
    {
        // Assert
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function test_invoke_adds_face_detect_menu(): void
    {
        // Arrange
        $rootItem = $this->createMock(ItemInterface::class);
        $faceDetectItem = $this->createMock(ItemInterface::class);
        
        $rootItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('人脸识别')
            ->willReturnOnConsecutiveCalls(null, $faceDetectItem);
            
        $rootItem->expects($this->once())
            ->method('addChild')
            ->with('人脸识别')
            ->willReturn($faceDetectItem);

        // Mock menu item methods
        $faceDetectItem->expects($this->exactly(5))
            ->method('addChild')
            ->willReturnSelf();

        // Act
        ($this->adminMenu)($rootItem);
    }






    public function test_admin_menu_implements_menu_provider_interface(): void
    {
        // Assert
        $this->assertInstanceOf(
            \Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface::class,
            $this->adminMenu
        );
    }

    public function test_invoke_is_callable(): void
    {
        // Assert - AdminMenu implements __invoke method
        $reflection = new \ReflectionClass($this->adminMenu);
        $this->assertTrue($reflection->hasMethod('__invoke'));
        $this->assertTrue($reflection->getMethod('__invoke')->isPublic());
    }

    private function setupRootItem(ItemInterface $rootItem, ItemInterface $faceDetectItem): void
    {
        $rootItem->expects($this->any())
            ->method('getChild')
            ->with('人脸识别')
            ->willReturn($faceDetectItem);
            
        $rootItem->expects($this->any())
            ->method('addChild')
            ->with('人脸识别')
            ->willReturn($faceDetectItem);
    }

    public function test_invoke_adds_all_required_menu_items(): void
    {
        // Arrange
        $rootItem = $this->createMock(ItemInterface::class);
        $faceDetectItem = $this->createMock(ItemInterface::class);
        
        $this->setupRootItem($rootItem, $faceDetectItem);
        
        $expectedMenuItems = [
            '人脸档案',
            '验证策略',
            '策略规则',
            '验证记录',
            '操作日志'
        ];
        
        $menuItemMock = $this->createMock(ItemInterface::class);
        $menuItemMock->method('setUri')->willReturnSelf();
        $menuItemMock->method('setAttribute')->willReturnSelf();
        
        $faceDetectItem->expects($this->exactly(5))
            ->method('addChild')
            ->willReturnCallback(function ($name) use ($menuItemMock, $expectedMenuItems) {
                $this->assertContains($name, $expectedMenuItems);
                return $menuItemMock;
            });

        // Act
        ($this->adminMenu)($rootItem);
    }
}