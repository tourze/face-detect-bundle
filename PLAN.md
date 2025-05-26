# face-detect-bundle 开发计划

## 1. 功能描述

人脸检测包，负责安全生产培训过程中的身份验证和防代学检测。通过人脸识别技术、活体检测、人像抓拍等功能，确保学习者身份的真实性，防止代学行为，满足培训监管要求。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 基础人脸检测功能
- ✅ 与学习会话关联

### 2.2 需要增强的能力

#### 2.2.1 每个视频随机验证不少于1次

- [ ] 视频播放过程中随机触发人脸验证
- [ ] 验证时机智能算法
- [ ] 验证频率配置管理
- [ ] 验证结果记录

#### 2.2.2 每两次验证间隔不超过15分钟

- [ ] 验证间隔时间控制
- [ ] 自动触发验证机制
- [ ] 间隔时间配置
- [ ] 超时自动验证

#### 2.2.3 验证失败自动处理

- [ ] 验证失败次数限制
- [ ] 自动暂停学习
- [ ] 失败原因分析
- [ ] 重新验证机制

#### 2.2.4 抓拍图片存储管理

- [ ] 高质量人像抓拍
- [ ] 图片压缩和存储
- [ ] 图片加密保护
- [ ] 存储空间管理

#### 2.2.5 与公安部系统对接

- [ ] 身份证信息验证
- [ ] 人脸比对接口
- [ ] 实名认证集成
- [ ] 数据安全传输

#### 2.2.6 活体检测功能

- [ ] 眨眼检测
- [ ] 张嘴检测
- [ ] 头部转动检测
- [ ] 随机动作指令

## 3. 实体设计

### 3.1 需要新增的实体

#### FaceDetection（人脸检测记录）
```php
class FaceDetection
{
    private string $id;
    private LearnSession $session;
    private string $detectionType;  // 检测类型（random, interval, manual）
    private \DateTimeInterface $detectionTime;  // 检测时间
    private string $capturedImagePath;  // 抓拍图片路径
    private string $referenceImagePath;  // 参考图片路径
    private float $similarityScore;  // 相似度分数
    private bool $isSuccess;  // 是否成功
    private string $failureReason;  // 失败原因
    private array $detectionData;  // 检测数据
    private string $deviceInfo;  // 设备信息
    private string $ipAddress;  // IP地址
    private \DateTimeInterface $createTime;
}
```

#### LivenessDetection（活体检测记录）
```php
class LivenessDetection
{
    private string $id;
    private FaceDetection $faceDetection;
    private string $livenessType;  // 活体检测类型
    private array $actionSequence;  // 动作序列
    private array $detectionResults;  // 检测结果
    private bool $isLive;  // 是否活体
    private float $confidenceScore;  // 置信度分数
    private \DateTimeInterface $createTime;
}
```

#### FaceVerificationConfig（人脸验证配置）
```php
class FaceVerificationConfig
{
    private string $id;
    private Course $course;
    private int $minVerificationInterval;  // 最小验证间隔（秒）
    private int $maxVerificationInterval;  // 最大验证间隔（秒）
    private int $maxFailureCount;  // 最大失败次数
    private float $similarityThreshold;  // 相似度阈值
    private bool $livenessRequired;  // 是否需要活体检测
    private array $livenessActions;  // 活体检测动作
    private bool $isActive;  // 是否启用
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### FaceImage（人脸图片）
```php
class FaceImage
{
    private string $id;
    private string $userId;
    private string $imageType;  // 图片类型（reference, captured）
    private string $imagePath;  // 图片路径
    private string $encryptedPath;  // 加密图片路径
    private array $faceFeatures;  // 人脸特征
    private string $imageHash;  // 图片哈希
    private int $imageSize;  // 图片大小
    private string $imageFormat;  // 图片格式
    private bool $isActive;  // 是否有效
    private \DateTimeInterface $createTime;
}
```

## 4. 服务设计

### 4.1 核心服务

#### FaceDetectionService
```php
class FaceDetectionService
{
    public function triggerRandomVerification(string $sessionId): FaceDetection;
    public function triggerIntervalVerification(string $sessionId): FaceDetection;
    public function performFaceVerification(string $sessionId, string $capturedImagePath): FaceDetection;
    public function calculateSimilarity(string $referenceImagePath, string $capturedImagePath): float;
    public function handleVerificationFailure(string $sessionId, string $reason): void;
    public function getVerificationHistory(string $sessionId): array;
}
```

#### LivenessDetectionService
```php
class LivenessDetectionService
{
    public function generateActionSequence(): array;
    public function performLivenessDetection(string $faceDetectionId, array $actionResults): LivenessDetection;
    public function validateLivenessAction(string $actionType, array $actionData): bool;
    public function calculateLivenessScore(array $actionResults): float;
}
```

#### FaceImageService
```php
class FaceImageService
{
    public function captureImage(string $sessionId): string;
    public function extractFaceFeatures(string $imagePath): array;
    public function encryptImage(string $imagePath): string;
    public function compressImage(string $imagePath, int $quality): string;
    public function validateImageQuality(string $imagePath): bool;
    public function cleanupExpiredImages(): void;
}
```

#### PoliceSystemService
```php
class PoliceSystemService
{
    public function verifyIdentity(string $idCardNumber, string $imagePath): array;
    public function compareFaceWithIdCard(string $idCardNumber, string $imagePath): float;
    public function getIdCardPhoto(string $idCardNumber): ?string;
    public function validateIdCardInfo(array $idCardInfo): bool;
}
```

#### FaceVerificationScheduler
```php
class FaceVerificationScheduler
{
    public function scheduleVerification(string $sessionId): void;
    public function checkVerificationDue(string $sessionId): bool;
    public function getNextVerificationTime(string $sessionId): \DateTimeInterface;
    public function cancelScheduledVerification(string $sessionId): void;
}
```

## 5. Command设计

### 5.1 验证管理命令

#### FaceVerificationScheduleCommand
```php
class FaceVerificationScheduleCommand extends Command
{
    protected static $defaultName = 'face:verification:schedule';
    
    // 调度人脸验证任务
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### FaceImageCleanupCommand
```php
class FaceImageCleanupCommand extends Command
{
    protected static $defaultName = 'face:image:cleanup';
    
    // 清理过期的人脸图片
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.2 数据处理命令

#### FaceDataAnalysisCommand
```php
class FaceDataAnalysisCommand extends Command
{
    protected static $defaultName = 'face:data:analysis';
    
    // 分析人脸检测数据
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### FaceFeatureUpdateCommand
```php
class FaceFeatureUpdateCommand extends Command
{
    protected static $defaultName = 'face:feature:update';
    
    // 更新人脸特征数据
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

## 6. 配置和集成

### 6.1 Bundle配置

无需配置

### 6.2 依赖包

- `real-name-authentication-bundle` - 实名认证
- `doctrine-entity-checker-bundle` - 实体检查
- `train-record-bundle` - 学习记录

## 7. 测试计划

### 7.1 单元测试

- [ ] FaceDetectionService测试
- [ ] LivenessDetectionService测试
- [ ] FaceImageService测试
- [ ] 相似度计算测试

### 7.2 集成测试

- [ ] 完整验证流程测试
- [ ] 活体检测流程测试
- [ ] 公安系统对接测试
- [ ] 图片处理流程测试

### 7.3 性能测试

- [ ] 大量并发验证测试
- [ ] 图片处理性能测试
- [ ] AI接口响应时间测试

## 8. 部署和运维

### 8.1 部署要求

- PHP 8.2+
- 图片处理扩展（GD/ImageMagick）
- 足够的存储空间
- 稳定的网络连接（AI接口）

### 8.2 监控指标

- 验证成功率
- 验证响应时间
- 活体检测准确率
- 图片存储使用率
- AI接口可用性

---

**文档版本**: v1.0
**创建日期**: 2024年12月
**负责人**: 开发团队 