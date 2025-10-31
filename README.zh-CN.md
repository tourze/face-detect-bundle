# Face Detection Bundle

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](https://github.com/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen)](https://github.com/tourze/php-monorepo)
[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-6.4%2B-black)](https://symfony.com/)

[English](README.md) | [中文](README.zh-CN.md)

基于 Symfony 的全面人脸识别和校验模块，支持人脸档案管理、验证策略配置，
以及与百度 AI 等多种人脸识别服务的集成。

## 目录

- [特性](#特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [核心组件](#核心组件)
- [配置](#配置)
- [高级用法](#高级用法)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 特性

- 🎯 **人脸档案管理** - 完整的人脸档案生命周期管理
- 🔐 **验证策略** - 灵活可配置的验证规则体系
- 📊 **操作日志** - 详细的操作追踪和状态管理
- 🤖 **AI 集成** - 支持百度 AI 等多种人脸识别服务
- 📋 **管理界面** - 基于 EasyAdmin 的后台管理
- 🧪 **全面测试** - 524+ 个测试用例，覆盖率 >95%

## 安装

```bash
composer require tourze/face-detect-bundle
```

## 快速开始

### 第一步：注册 Bundle

```php
// config/bundles.php
return [
    // ...
    Tourze\FaceDetectBundle\FaceDetectBundle::class => ['all' => true],
];
```

### 第二步：更新数据库架构

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 第三步：配置服务

Bundle 会自动注册所有服务。你可以在 `services.yaml` 中配置它们：

```yaml
# config/services.yaml
services:
    # 自定义人脸识别服务实现
    App\Service\CustomFaceDetectionService:
        # 你的实现
```

## 核心组件

### 实体类

- **FaceProfile** - 人脸档案存储，带有过期时间和状态管理
- **VerificationStrategy** - 可配置的验证规则和优先级
- **VerificationRecord** - 详细的验证尝试日志
- **OperationLog** - 业务操作追踪
- **StrategyRule** - 细粒度验证规则

### 枚举类

- **FaceProfileStatus** - 活跃、过期、禁用
- **VerificationResult** - 成功、失败、跳过、超时
- **VerificationType** - 必需、可选、强制
- **OperationStatus** - 待处理、处理中、已完成、失败、已取消

### 服务类

- **AdminMenu** - EasyAdmin 菜单集成
- 所有实体的存储库类，提供全面的查询方法

## 配置

### 基本配置

```yaml
# config/packages/face_detect.yaml
face_detect:
    # 配置将根据你的需求扩展
```

### 验证策略

通过代码创建验证策略：

```php
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

$strategy = new VerificationStrategy();
$strategy->setName('high_security')
    ->setBusinessType('payment')
    ->setDescription('支付高安全验证')
    ->setEnabled(true)
    ->setPriority(100)
    ->setConfig([
        'max_attempts' => 3,
        'timeout' => 30,
        'confidence_threshold' => 0.8
    ]);
```

## 高级用法

### 人脸档案管理

```php
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

// 创建新的人脸档案
$profile = new FaceProfile('user123', 'encrypted_face_features');
$profile->setQualityScore(0.95)
    ->setCollectionMethod('auto')
    ->setStatus(FaceProfileStatus::ACTIVE)
    ->setExpiresAfter(new \DateInterval('P1Y')); // 1年后过期
```

### 操作追踪

```php
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

// 创建操作日志
$operation = new OperationLog('user123', 'op_12345', 'payment_verification');
$operation->setVerificationRequired(true)
    ->setMinVerificationCount(2)
    ->setBusinessContext(['amount' => 1000, 'currency' => 'USD']);
```

### 验证记录

```php
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Enum\VerificationResult;

// 记录验证尝试
$record = new VerificationRecord(
    'user123',
    $strategy,
    'payment',
    VerificationResult::SUCCESS
);
$record->setConfidenceScore(0.92)
    ->setVerificationTime(1.5)
    ->setClientInfo(['device' => 'iPhone', 'browser' => 'Safari']);
```

## 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/face-detect-bundle/tests

# 运行带覆盖率的测试
./vendor/bin/phpunit packages/face-detect-bundle/tests --coverage-html coverage

# 运行特定测试类别
./vendor/bin/phpunit packages/face-detect-bundle/tests/Unit
./vendor/bin/phpunit packages/face-detect-bundle/tests/Integration
```

## 贡献

1. Fork 此仓库
2. 创建你的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交你的更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 打开一个 Pull Request

## 许可证

此项目根据 MIT 许可证授权 - 详情请参阅 [LICENSE](LICENSE) 文件。