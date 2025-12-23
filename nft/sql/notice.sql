-- 公告表
-- 创建时间: 2024
-- 说明: 用于存储系统公告信息

CREATE TABLE IF NOT EXISTS `ba_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '公告标题',
  `content` text COMMENT '公告内容',
  `cover_image` varchar(500) DEFAULT '' COMMENT '封面图片',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态: 0=隐藏, 1=显示',
  `start_time` int(10) unsigned DEFAULT NULL COMMENT '开始显示时间（时间戳）',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '结束显示时间（时间戳）',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序（数字越大越靠前）',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`),
  KEY `idx_time` (`start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公告表';

-- 插入测试数据
INSERT INTO `ba_notice` (`title`, `content`, `cover_image`, `status`, `start_time`, `end_time`, `sort`, `view_count`, `create_time`, `update_time`) VALUES
('Welcome to Second Futures', '<p>Welcome to our platform! We provide the best trading experience.</p><p>Features:</p><ul><li>Fast trading</li><li>Low fees</li><li>24/7 support</li></ul>', '', 1, NULL, NULL, 100, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('System Maintenance Notice', '<p>Dear users,</p><p>We will perform system maintenance on December 1st, 2024 from 00:00 to 02:00 UTC.</p><p>During this time, trading services may be temporarily unavailable.</p><p>Thank you for your understanding.</p>', '', 1, NULL, NULL, 90, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('New Trading Pairs Available', '<p>We are excited to announce new trading pairs:</p><ul><li>SOL/USDT</li><li>DOGE/USDT</li><li>ADA/USDT</li></ul><p>Start trading now!</p>', '', 1, NULL, NULL, 80, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());





  ALTER TABLE `ba_user` ADD COLUMN `device_id` VARCHAR(64) DEFAULT NULL COMMENT '设备唯一ID' AFTER `virtualmoney`;
  ALTER TABLE `ba_user` ADD COLUMN `is_guest` TINYINT(1) DEFAULT 0 COMMENT '是否临时用户 0=正式用户 1=临时用户' AFTER `device_id`;
  ALTER TABLE `ba_user` ADD INDEX `idx_device_id` (`device_id`);
  ALTER TABLE `ba_user` ADD INDEX `idx_is_guest` (`is_guest`);



  ALTER TABLE `ba_user` ADD COLUMN `google_secret` VARCHAR(32) DEFAULT NULL;
  ALTER TABLE `ba_user` ADD COLUMN `google_auth_enabled` TINYINT(1) DEFAULT 0;


  ALTER TABLE `ba_soptcoin` ADD COLUMN `recharge_address` VARCHAR(255) DEFAULT NULL;