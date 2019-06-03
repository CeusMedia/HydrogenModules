ALTER TABLE `<%?prefix%>mails` ADD `compression` TINYINT(1) UNSIGNED NOT NULL COMMENT '0: UNKNOWN, 1: NONE, 2: BASE64, 3: GZIP, 4: BZIP' AFTER `subject`, ADD INDEX (`compression`);
ALTER TABLE `<%?prefix%>mails` CHANGE `attemptedAt` `attemptedAt` DECIMAL(12,0) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<%?prefix%>mails` CHANGE `sentAt` `sentAt` DECIMAL(12,0) UNSIGNED NULL DEFAULT '0';
