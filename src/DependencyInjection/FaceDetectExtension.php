<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class FaceDetectExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
