<?php

namespace Tourze\FaceDetectBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 验证类型枚举
 */
enum VerificationType: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case REQUIRED = 'required';  // 必需验证
    case OPTIONAL = 'optional';  // 可选验证
    case FORCED = 'forced';      // 强制验证

    public function getLabel(): string
    {
        return match($this) {
            self::REQUIRED => '必需验证',
            self::OPTIONAL => '可选验证',
            self::FORCED => '强制验证',
        };
    }

    /**
     * 获取类型描述
     */
    public function getDescription(): string
    {
        return $this->getLabel();
    }

    /**
     * 检查是否为强制类型
     */
    public function isMandatory(): bool
    {
        return $this === self::REQUIRED || $this === self::FORCED;
    }
}
