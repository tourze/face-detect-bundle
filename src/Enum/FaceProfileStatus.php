<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 人脸档案状态枚举
 */
enum FaceProfileStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';

    public function getLabel(): string
    {
        return $this->getDescription();
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ACTIVE => '活跃',
            self::EXPIRED => '已过期',
            self::DISABLED => '已禁用',
        };
    }

    public function isUsable(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            self::EXPIRED, self::DISABLED => false,
        };
    }
}
