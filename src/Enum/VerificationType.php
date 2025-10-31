<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 验证类型枚举
 */
enum VerificationType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case REQUIRED = 'required';
    case OPTIONAL = 'optional';
    case FORCED = 'forced';

    public function getLabel(): string
    {
        return $this->getDescription();
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::REQUIRED => '必需验证',
            self::OPTIONAL => '可选验证',
            self::FORCED => '强制验证',
        };
    }

    public function isMandatory(): bool
    {
        return match ($this) {
            self::REQUIRED, self::FORCED => true,
            self::OPTIONAL => false,
        };
    }
}
