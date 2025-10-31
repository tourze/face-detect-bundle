# 数据库设计文档

## 表结构设计

### 1. 人脸档案表 (face_profiles)

存储用户的人脸特征数据和相关信息。

```sql
CREATE TABLE face_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(64) NOT NULL COMMENT '用户ID',
    face_features TEXT NOT NULL COMMENT '加密的人脸特征数据',
    quality_score DECIMAL(3,2) DEFAULT 0.00 COMMENT '人脸质量评分(0-1)',
    collection_method VARCHAR(32) DEFAULT 'manual' COMMENT '采集方式: manual, auto, import',
    device_info JSON COMMENT '采集设备信息',
    status ENUM('active', 'expired', 'disabled') DEFAULT 'active' COMMENT '状态',
    expires_at TIMESTAMP NULL COMMENT '过期时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='人脸档案表';
```

### 2. 验证策略表 (verification_strategies)

定义不同业务场景的验证策略。

```sql
CREATE TABLE verification_strategies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL COMMENT '策略名称',
    business_type VARCHAR(64) NOT NULL COMMENT '业务类型',
    description TEXT COMMENT '策略描述',
    is_enabled BOOLEAN DEFAULT TRUE COMMENT '是否启用',
    priority INT DEFAULT 0 COMMENT '优先级，数值越大优先级越高',
    config JSON NOT NULL COMMENT '策略配置参数',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_name (name),
    INDEX idx_business_type (business_type),
    INDEX idx_enabled_priority (is_enabled, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='验证策略表';
```

### 3. 验证记录表 (verification_records)

记录每次人脸验证的详细信息。

```sql
CREATE TABLE verification_records (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(64) NOT NULL COMMENT '用户ID',
    strategy_id BIGINT NOT NULL COMMENT '使用的策略ID',
    business_type VARCHAR(64) NOT NULL COMMENT '业务类型',
    operation_id VARCHAR(128) COMMENT '关联的业务操作ID',
    verification_type ENUM('required', 'optional', 'forced') DEFAULT 'required' COMMENT '验证类型',
    result ENUM('success', 'failed', 'skipped', 'timeout') NOT NULL COMMENT '验证结果',
    confidence_score DECIMAL(3,2) COMMENT '置信度评分(0-1)',
    verification_time DECIMAL(5,3) COMMENT '验证耗时(秒)',
    client_info JSON COMMENT '客户端信息',
    error_code VARCHAR(32) COMMENT '错误码',
    error_message TEXT COMMENT '错误信息',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_strategy_id (strategy_id),
    INDEX idx_business_type (business_type),
    INDEX idx_operation_id (operation_id),
    INDEX idx_result (result),

    FOREIGN KEY (strategy_id) REFERENCES verification_strategies(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='验证记录表';
```

### 4. 操作日志表 (operation_logs)

记录用户的业务操作和验证关联信息。

```sql
CREATE TABLE operation_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(64) NOT NULL COMMENT '用户ID',
    operation_id VARCHAR(128) NOT NULL COMMENT '操作ID',
    operation_type VARCHAR(64) NOT NULL COMMENT '操作类型',
    business_context JSON COMMENT '业务上下文',
    verification_required BOOLEAN DEFAULT FALSE COMMENT '是否需要验证',
    verification_completed BOOLEAN DEFAULT FALSE COMMENT '是否完成验证',
    verification_count INT DEFAULT 0 COMMENT '验证次数',
    min_verification_count INT DEFAULT 1 COMMENT '最少验证次数',
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    UNIQUE KEY uk_operation_id (operation_id),
    INDEX idx_user_id (user_id),
    INDEX idx_operation_type (operation_type),
    INDEX idx_verification_status (verification_required, verification_completed),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';
```

### 5. 策略规则表 (strategy_rules)

存储验证策略的具体规则配置。

```sql
CREATE TABLE strategy_rules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    strategy_id BIGINT NOT NULL COMMENT '策略ID',
    rule_type VARCHAR(64) NOT NULL COMMENT '规则类型: time, frequency, risk, amount',
    rule_name VARCHAR(128) NOT NULL COMMENT '规则名称',
    conditions JSON NOT NULL COMMENT '规则条件',
    actions JSON NOT NULL COMMENT '规则动作',
    is_enabled BOOLEAN DEFAULT TRUE COMMENT '是否启用',
    priority INT DEFAULT 0 COMMENT '规则优先级',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_strategy_id (strategy_id),
    INDEX idx_rule_type (rule_type),
    INDEX idx_enabled_priority (is_enabled, priority),

    FOREIGN KEY (strategy_id) REFERENCES verification_strategies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='策略规则表';
```

### 6. 配置参数表 (system_configs)

存储系统级别的配置参数。

```sql
CREATE TABLE system_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(128) NOT NULL COMMENT '配置键',
    config_value TEXT NOT NULL COMMENT '配置值',
    config_type VARCHAR(32) DEFAULT 'string' COMMENT '配置类型: string, int, float, bool, json',
    description TEXT COMMENT '配置描述',
    is_encrypted BOOLEAN DEFAULT FALSE COMMENT '是否加密存储',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_config_key (config_key),
    INDEX idx_config_type (config_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';
```

## 索引优化策略

### 1. 查询优化索引

```sql
-- 用户验证历史查询优化
CREATE INDEX idx_user_verification_history ON verification_records(user_id, created_at DESC);

-- 业务类型验证统计优化
CREATE INDEX idx_business_verification_stats ON verification_records(business_type, result, created_at);

-- 策略规则查询优化
CREATE INDEX idx_strategy_rules_lookup ON strategy_rules(strategy_id, rule_type, is_enabled);

-- 操作验证状态查询优化
CREATE INDEX idx_operation_verification_status ON operation_logs(user_id, verification_required, verification_completed, status);
```

### 2. 分区策略

```sql
-- 验证记录表按月分区（适用于大数据量场景）
ALTER TABLE verification_records PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202501 VALUES LESS THAN (202502),
    PARTITION p202502 VALUES LESS THAN (202503),
    PARTITION p202503 VALUES LESS THAN (202504),
    -- ... 继续添加分区
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

## 数据字典

### 验证策略配置示例

```json
{
  "time_rules": {
    "max_interval_hours": 24,
    "daily_first_required": true,
    "sensitive_hours": ["22:00-06:00"]
  },
  "frequency_rules": {
    "max_continuous_operations": 5,
    "high_risk_always_verify": true,
    "batch_operation_threshold": 10
  },
  "risk_rules": {
    "ip_whitelist": ["192.168.1.0/24"],
    "device_fingerprint_check": true,
    "amount_threshold": 10000.00
  },
  "verification_requirements": {
    "min_confidence_score": 0.85,
    "max_verification_time": 30,
    "retry_limit": 3
  }
}
```

### 策略规则条件示例

```json
{
  "type": "time_interval",
  "operator": "greater_than",
  "value": 24,
  "unit": "hours",
  "reference": "last_verification"
}
```

### 策略规则动作示例

```json
{
  "action": "require_verification",
  "parameters": {
    "verification_type": "required",
    "min_confidence": 0.85,
    "timeout_seconds": 30
  }
}
```

## 数据安全考虑

### 1. 敏感数据加密

- `face_features`: 使用 AES-256 加密存储
- `system_configs.config_value`: 标记为加密的配置项使用加密存储

### 2. 数据脱敏

- 日志记录中的用户ID进行脱敏处理
- 错误信息不包含敏感的系统内部信息

### 3. 数据保留策略

- 验证记录保留6个月，超期自动清理
- 人脸特征数据在用户注销后30天内删除
- 操作日志保留1年用于审计

### 4. 访问控制

- 数据库连接使用专用账户，最小权限原则
- 敏感表的访问需要额外的权限验证
- 所有数据访问操作记录审计日志
