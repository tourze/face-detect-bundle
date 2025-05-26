# 人脸识别业务流程分析

## 业务场景概述

### 场景1：人脸信息采集

```mermaid
sequenceDiagram
    participant User as 用户
    participant App as 应用系统
    participant FaceService as 人脸识别服务
    participant DB as 数据库

    User->>App: 登录/进入业务流程
    App->>FaceService: 检查用户是否已采集人脸
    FaceService->>DB: 查询用户人脸信息
    DB-->>FaceService: 返回查询结果

    alt 未采集人脸
        FaceService-->>App: 返回需要采集
        App->>User: 提示进行人脸采集
        User->>App: 提交人脸数据
        App->>FaceService: 保存人脸特征数据
        FaceService->>DB: 存储加密的人脸特征
        DB-->>FaceService: 确认保存成功
        FaceService-->>App: 返回采集成功
    else 已采集人脸
        FaceService-->>App: 返回已采集状态
    end
```

### 场景2：验证需求判断

```mermaid
sequenceDiagram
    participant User as 用户
    participant App as 应用系统
    participant FaceService as 人脸识别服务
    participant StrategyEngine as 策略引擎
    participant DB as 数据库

    User->>App: 发起业务操作
    App->>FaceService: 检查是否需要人脸验证
    FaceService->>StrategyEngine: 获取验证策略
    StrategyEngine->>DB: 查询策略配置
    DB-->>StrategyEngine: 返回策略规则

    StrategyEngine->>DB: 查询用户验证历史
    DB-->>StrategyEngine: 返回历史记录

    StrategyEngine-->>FaceService: 返回验证决策

    alt 需要验证
        FaceService-->>App: 返回需要验证
        App->>User: 要求进行人脸验证
        User->>App: 完成人脸验证
        App->>FaceService: 提交验证结果
        FaceService->>DB: 记录验证操作
    else 无需验证
        FaceService-->>App: 返回无需验证
        App->>User: 继续业务流程
    end
```

### 场景3：验证完成确认

```mermaid
sequenceDiagram
    participant User as 用户
    participant App as 应用系统
    participant FaceService as 人脸识别服务
    participant DB as 数据库

    User->>App: 完成业务操作
    App->>FaceService: 确认验证完成状态
    FaceService->>DB: 查询验证记录
    DB-->>FaceService: 返回验证统计

    FaceService->>FaceService: 计算完成度

    alt 满足验证要求
        FaceService-->>App: 返回验证通过
        App->>User: 业务操作成功
    else 未满足验证要求
        FaceService-->>App: 返回验证不足
        App->>User: 提示需要补充验证
    end
```

## 核心业务规则

### 人脸采集规则

1. **首次采集**：用户首次使用时必须进行人脸采集
2. **重新采集**：人脸数据过期或质量不佳时需要重新采集
3. **采集频率**：同一用户24小时内最多采集3次
4. **数据保护**：人脸特征数据加密存储，不保存原始图像

### 验证策略规则

1. **时间策略**：
   - 距离上次验证超过N小时需要重新验证
   - 每日首次操作需要验证
   - 敏感时段（如深夜）强制验证

2. **频率策略**：
   - 连续操作超过N次需要验证
   - 高风险操作每次都需要验证
   - 批量操作需要验证

3. **风险策略**：
   - 异常IP地址访问需要验证
   - 设备指纹变化需要验证
   - 操作金额超过阈值需要验证

### 验证完成规则

1. **次数要求**：根据业务类型设定最低验证次数
2. **时效性**：验证记录有效期限制
3. **质量评估**：验证成功率达到阈值才算完成

## 数据模型设计

### 核心实体关系

```ascii
User (用户)
├── FaceProfile (人脸档案) [1:1]
├── VerificationRecord (验证记录) [1:N]
└── OperationLog (操作日志) [1:N]

VerificationStrategy (验证策略)
├── StrategyRule (策略规则) [1:N]
└── BusinessType (业务类型) [1:N]

VerificationRecord (验证记录)
├── User (用户) [N:1]
├── VerificationStrategy (验证策略) [N:1]
└── OperationLog (操作日志) [1:1]
```

### 状态流转图

```mermaid
stateDiagram-v2
    [*] --> NotCollected: 用户注册
    NotCollected --> Collecting: 开始采集
    Collecting --> Collected: 采集成功
    Collecting --> Failed: 采集失败
    Failed --> Collecting: 重新采集
    Collected --> Verifying: 需要验证
    Verifying --> Verified: 验证成功
    Verifying --> Failed: 验证失败
    Verified --> Verifying: 再次验证
    Collected --> Expired: 数据过期
    Expired --> Collecting: 重新采集
```
