<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 测试用户实现
 *
 * @internal
 */
class TestUser implements UserInterface
{
    private string $username;

    private array $roles;

    public function __construct(string $username = 'testuser', array $roles = ['ROLE_USER'])
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // do nothing
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
