<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 验证结果枚举
 */
enum VerificationResult: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
    case TIMEOUT = 'timeout';

    public function getLabel(): string
    {
        return $this->getDescription();
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SUCCESS => '验证成功',
            self::FAILED => '验证失败',
            self::SKIPPED => '跳过验证',
            self::TIMEOUT => '验证超时',
        };
    }

    public function isSuccessful(): bool
    {
        return match ($this) {
            self::SUCCESS => true,
            self::FAILED, self::SKIPPED, self::TIMEOUT => false,
        };
    }

    public function isFailure(): bool
    {
        return match ($this) {
            self::FAILED, self::TIMEOUT => true,
            self::SUCCESS, self::SKIPPED => false,
        };
    }
}
