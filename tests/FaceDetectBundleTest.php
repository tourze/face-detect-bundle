<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\FaceDetectBundle\FaceDetectBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(FaceDetectBundle::class)]
#[RunTestsInSeparateProcesses]
final class FaceDetectBundleTest extends AbstractBundleTestCase
{
}
