DROP TABLE IF EXISTS `<%?prefix%>mails`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>mails` (
`mailId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`senderId` int(10) UNSIGNED NOT NULL DEFAULT '0',
`receiverId` int(10) UNSIGNED NOT NULL DEFAULT '0',
`templateId` int(10) UNSIGNED NULL DEFAULT '0',
`status` tinyint(1) NOT NULL DEFAULT '0',
`attempts` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
`language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
`senderAddress` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
`receiverAddress` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
`receiverName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
`subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
`mailClass` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
`compression` tinyint(1) UNSIGNED NOT NULL COMMENT '0: UNKNOWN, 1: BASE64, 2: GZIP, 3: BZIP',
`object` longblob NOT NULL,
`enqueuedAt` decimal(12,0) UNSIGNED NOT NULL,
`attemptedAt` decimal(12,0) UNSIGNED DEFAULT '0',
`sentAt` decimal(12,0) UNSIGNED DEFAULT '0',
PRIMARY KEY (`mailId`),
KEY `senderId` (`senderId`),
KEY `receiverId` (`receiverId`),
KEY `templateId` (`templateId`),
KEY `status` (`status`),
KEY `attempts` (`attempts`),
KEY `language` (`language`),
KEY `senderAddress` (`senderAddress`),
KEY `receiverAddress` (`receiverAddress`),
KEY `receiverName` (`receiverName`),
KEY `subject` (`subject`),
KEY `mailClass` (`mailClass`),
KEY `compression` (`compression`),
KEY `enqueuedAt` (`enqueuedAt`),
KEY `attemptedAt` (`attemptedAt`),
KEY `sentAt` (`sentAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `<%?prefix%>mail_attachments`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>mail_attachments` (
`mailAttachmentId` int(10) unsigned NOT NULL AUTO_INCREMENT,
`status` tinyint(1) NOT NULL,
`language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
`className` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
`filename` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
`mimeType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
`countAttached` int(11) NULL DEFAULT '0',
`createdAt` decimal(12,0) NOT NULL,
PRIMARY KEY (`mailAttachmentId`),
KEY `status` (`status`),
KEY `className` (`className`),
KEY `filename` (`filename`),
KEY `mimeType` (`mimeType`),
KEY `createdAt` (`createdAt`),
KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `<%?prefix%>mail_templates`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>mail_templates` (
`mailTemplateId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`status` tinyint(1) NOT NULL,
`language` varchar(5) DEFAULT NULL,
`title` varchar(100) NOT NULL,
`plain` text,
`html` text NOT NULL,
`css` text NOT NULL,
`styles` text NULL,
`images` text,
`createdAt` decimal(12,0) UNSIGNED NOT NULL,
`modifiedAt` decimal(12,0) UNSIGNED DEFAULT NULL,
PRIMARY KEY (`mailTemplateId`),
KEY `status` (`status`),
KEY `createdAt` (`createdAt`),
KEY `modifiedAt` (`modifiedAt`),
KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
