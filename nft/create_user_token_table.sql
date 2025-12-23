CREATE TABLE IF NOT EXISTS `user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `token` varchar(255) NOT NULL COMMENT '认证token',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `expire_time` int(11) NOT NULL COMMENT '过期时间',
  `last_active_time` int(11) DEFAULT NULL COMMENT '最后活动时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户认证token表'; 