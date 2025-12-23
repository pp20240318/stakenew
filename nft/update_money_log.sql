-- 添加type字段到ba_user_money_log表
ALTER TABLE `ba_user_money_log` 
ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型:1=人工,2=机器人' AFTER `memo`;

-- 更新现有记录，将自动交易设为机器人类型
UPDATE `ba_user_money_log` 
SET `type` = 2 
WHERE `memo` LIKE '%bot%' OR `memo` LIKE '%Bot%' OR `memo` LIKE '%机器人%' OR `business_type` LIKE '%Bot%'; 