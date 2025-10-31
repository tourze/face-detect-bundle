# Face Detection Bundle

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](https://github.com/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen)](https://github.com/tourze/php-monorepo)
[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-6.4%2B-black)](https://symfony.com/)

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive face detection and verification module for Symfony applications, 
supporting face profile management, verification strategies, and integration with 
various AI services like Baidu AI.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Components](#core-components)
- [Configuration](#configuration)
- [Advanced Usage](#advanced-usage)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🎯 **Face Profile Management** - Complete lifecycle management of face profiles
- 🔐 **Verification Strategies** - Flexible and configurable verification rules
- 📊 **Operation Logging** - Detailed operation tracking and status management
- 🤖 **AI Integration** - Support for Baidu AI and other face detection services
- 📋 **Admin Interface** - EasyAdmin-based management dashboard
- 🧪 **Comprehensive Testing** - 1100+ test cases with >95% coverage

## Installation

```bash
composer require tourze/face-detect-bundle
```

## Quick Start

### Step 1: Register the Bundle

```php
// config/bundles.php
return [
    // ...
    Tourze\FaceDetectBundle\FaceDetectBundle::class => ['all' => true],
];
```

### Step 2: Update Database Schema

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Step 3: Configure Services

The bundle automatically registers all services. You can configure them in your `services.yaml`:

```yaml
# config/services.yaml
services:
    # Custom face detection service implementations
    App\Service\CustomFaceDetectionService:
        # Your implementation
```

## Core Components

### Entities

- **FaceProfile** - Face profile storage with expiration and status management
- **VerificationStrategy** - Configurable verification rules and priorities
- **VerificationRecord** - Detailed verification attempt logging
- **OperationLog** - Business operation tracking
- **StrategyRule** - Fine-grained verification rules

### Enums

- **FaceProfileStatus** - Active, Expired, Disabled
- **VerificationResult** - Success, Failed, Skipped, Timeout
- **VerificationType** - Required, Optional, Forced
- **OperationStatus** - Pending, Processing, Completed, Failed, Cancelled

### Services

- **AdminMenu** - EasyAdmin menu integration
- Repository classes for all entities with comprehensive query methods

## Configuration

### Basic Configuration

```yaml
# config/packages/face_detect.yaml
face_detect:
    # Configuration will be expanded based on your needs
```

### Verification Strategies

Create verification strategies programmatically:

```php
use Tourze\FaceDetectBundle\Entity\VerificationStrategy;

$strategy = new VerificationStrategy();
$strategy->setName('high_security')
    ->setBusinessType('payment')
    ->setDescription('High security verification for payments')
    ->setEnabled(true)
    ->setPriority(100)
    ->setConfig([
        'max_attempts' => 3,
        'timeout' => 30,
        'confidence_threshold' => 0.8
    ]);
```

## Advanced Usage

### Face Profile Management

```php
use Tourze\FaceDetectBundle\Entity\FaceProfile;
use Tourze\FaceDetectBundle\Enum\FaceProfileStatus;

// Create a new face profile
$profile = new FaceProfile('user123', 'encrypted_face_features');
$profile->setQualityScore(0.95)
    ->setCollectionMethod('auto')
    ->setStatus(FaceProfileStatus::ACTIVE)
    ->setExpiresAfter(new \DateInterval('P1Y')); // Expires in 1 year
```

### Operation Tracking

```php
use Tourze\FaceDetectBundle\Entity\OperationLog;
use Tourze\FaceDetectBundle\Enum\OperationStatus;

// Create operation log
$operation = new OperationLog('user123', 'op_12345', 'payment_verification');
$operation->setVerificationRequired(true)
    ->setMinVerificationCount(2)
    ->setBusinessContext(['amount' => 1000, 'currency' => 'USD']);
```

### Verification Records

```php
use Tourze\FaceDetectBundle\Entity\VerificationRecord;
use Tourze\FaceDetectBundle\Enum\VerificationResult;

// Log verification attempt
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

## Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/phpunit packages/face-detect-bundle/tests

# Run with coverage
./vendor/bin/phpunit packages/face-detect-bundle/tests --coverage-html coverage

# Run specific test category
./vendor/bin/phpunit packages/face-detect-bundle/tests/Unit
./vendor/bin/phpunit packages/face-detect-bundle/tests/Integration
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.