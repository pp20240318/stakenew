/*
 * 质押系统数据库表设计
 * Date: 2024-03-19
 */

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ba_stake_plan
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_plan`;
CREATE TABLE `ba_stake_plan` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '质押计划名称',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '计划描述',
  `coin_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT' COMMENT '币种类型',
  `min_amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '最小质押金额',
  `max_amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '最大质押金额',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '锁定期(天)',
  `annual_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '年化收益率(%)',
  `daily_rate` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '日收益率(%)',
  `early_redemption_rate` decimal(5,2) DEFAULT '0.00' COMMENT '提前赎回扣除比例(%)',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `is_deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否删除:0=否,1=是',
  `level_limit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '等级限制(JSON格式)',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押计划表';

-- ----------------------------
-- Table structure for ba_stake_record
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_record`;
CREATE TABLE `ba_stake_record` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `plan_id` int(10) UNSIGNED NOT NULL COMMENT '质押计划ID',
  `order_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '质押金额',
  `coin_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT' COMMENT '币种类型',
  `start_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '开始时间',
  `end_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '结束时间',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '锁定期(天)',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态:0=进行中,1=已完成,2=已提前赎回',
  `annual_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '年化收益率(%)',
  `daily_rate` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '日收益率(%)',
  `total_reward` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '总收益',
  `received_reward` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '已领取收益',
  `next_profit_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '下次收益时间',
  `redemption_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '赎回时间',
  `redemption_fee` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '赎回手续费',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_plan_id` (`plan_id`) USING BTREE,
  KEY `idx_order_no` (`order_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押记录表';

-- ----------------------------
-- Table structure for ba_stake_profit_record
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_profit_record`;
CREATE TABLE `ba_stake_profit_record` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `stake_record_id` int(10) UNSIGNED NOT NULL COMMENT '质押记录ID',
  `plan_id` int(10) UNSIGNED NOT NULL COMMENT '质押计划ID',
  `amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '收益金额',
  `coin_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT' COMMENT '币种类型',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态:0=未领取,1=已领取',
  `receive_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '领取时间',
  `day_index` int(11) NOT NULL DEFAULT '1' COMMENT '收益天数索引',
  `profit_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收益日期(格式:yyyy-mm-dd)',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_stake_record_id` (`stake_record_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押收益记录表';

-- ----------------------------
-- Table structure for ba_stake_level
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_level`;
CREATE TABLE `ba_stake_level` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '等级描述',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '等级值',
  `required_amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '要求质押金额',
  `required_duration` int(11) NOT NULL DEFAULT '0' COMMENT '要求质押时长(天)',
  `reward_boost` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '收益加成(%)',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '等级图标',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押等级表';

-- ----------------------------
-- Table structure for ba_stake_user_level
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_user_level`;
CREATE TABLE `ba_stake_user_level` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `level_id` int(10) UNSIGNED NOT NULL COMMENT '等级ID',
  `total_staked` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '总质押金额',
  `max_duration` int(11) NOT NULL DEFAULT '0' COMMENT '最长质押天数',
  `expire_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '等级过期时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:0=失效,1=有效',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户质押等级表';

-- ----------------------------
-- Table structure for ba_stake_referral_reward
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_referral_reward`;
CREATE TABLE `ba_stake_referral_reward` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `referral_id` int(10) UNSIGNED NOT NULL COMMENT '被推荐人ID',
  `stake_record_id` int(10) UNSIGNED NOT NULL COMMENT '质押记录ID',
  `level` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '推荐层级:1=一级,2=二级',
  `amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '奖励金额',
  `coin_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT' COMMENT '币种类型',
  `rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '奖励比例(%)',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态:0=未发放,1=已发放',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_referral_id` (`referral_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='推荐质押奖励表';

-- ----------------------------
-- Table structure for ba_stake_config
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_config`;
CREATE TABLE `ba_stake_config` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名称',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置值',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配置描述',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押系统配置表';

-- ----------------------------
-- Records of ba_stake_config
-- ----------------------------
INSERT INTO `ba_stake_config` (`name`, `value`, `description`, `create_time`, `update_time`) VALUES
('level1_referral_rate', '5.00', '一级推荐奖励比例(%)', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
('level2_referral_rate', '2.00', '二级推荐奖励比例(%)', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
('min_stake_amount', '10.00000', '最小质押金额', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
('stake_reward_time', '00:00:00', '质押收益发放时间', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
('redemption_fee_rate', '2.00', '提前赎回手续费比例(%)', UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW()));

-- ----------------------------
-- Table structure for ba_stake_activity
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_activity`;
CREATE TABLE `ba_stake_activity` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '活动标题',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '活动描述',
  `start_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '开始时间',
  `end_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '结束时间',
  `bonus_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '额外收益比例(%)',
  `min_stake_amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '最小参与质押金额',
  `min_stake_duration` int(11) NOT NULL DEFAULT '0' COMMENT '最小参与质押天数',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态:0=未开始,1=进行中,2=已结束',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押活动表';

-- ----------------------------
-- Table structure for ba_stake_reward_rule
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_reward_rule`;
CREATE TABLE `ba_stake_reward_rule` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plan_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '质押计划ID(0表示全局规则)',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'amount' COMMENT '规则类型:amount=金额阶梯,duration=时长阶梯',
  `min_value` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '最小值(金额/天数)',
  `max_value` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '最大值(金额/天数)',
  `reward_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '奖励比例(%)',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '规则描述',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_plan_id` (`plan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押奖励规则表';

-- ----------------------------
-- Records of ba_stake_reward_rule
-- ----------------------------
INSERT INTO `ba_stake_reward_rule` (`plan_id`, `type`, `min_value`, `max_value`, `reward_rate`, `description`, `status`, `create_time`, `update_time`) VALUES
(0, 'amount', 100.00000, 1000.00000, 1.00, '100-1000 USDT额外1%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'amount', 1000.00000, 10000.00000, 2.00, '1000-10000 USDT额外2%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'amount', 10000.00000, 999999999.99999, 3.00, '10000+ USDT额外3%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'duration', 30.00000, 90.00000, 0.50, '30-90天额外0.5%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'duration', 90.00000, 180.00000, 1.00, '90-180天额外1%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'duration', 180.00000, 365.00000, 2.00, '180-365天额外2%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW())),
(0, 'duration', 365.00000, 999999.00000, 3.00, '365天以上额外3%年化收益', 1, UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW()));

-- ----------------------------
-- Table structure for ba_stake_wallet_record
-- ----------------------------
DROP TABLE IF EXISTS `ba_stake_wallet_record`;
CREATE TABLE `ba_stake_wallet_record` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '记录类型:stake=质押,profit=收益,referral=推荐奖励,redemption=赎回',
  `amount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT '金额',
  `coin_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT' COMMENT '币种类型',
  `related_id` int(10) UNSIGNED DEFAULT NULL COMMENT '关联记录ID',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `create_time` bigint(16) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质押钱包记录表';

SET FOREIGN_KEY_CHECKS = 1; 