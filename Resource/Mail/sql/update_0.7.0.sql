DROP TABLE IF EXISTS `<%?prefix%>mail_templates`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>mail_templates` (
`mailTemplateId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`status` tinyint(1) NOT NULL,
`language` varchar(5) DEFAULT NULL,
`title` varchar(100) NOT NULL,
`plain` text,
`html` text NOT NULL,
`css` text NOT NULL,
`styles` text NOT NULL,
`images` text,
`createdAt` decimal(12,0) UNSIGNED NOT NULL,
`modifiedAt` decimal(12,0) UNSIGNED DEFAULT NULL,
PRIMARY KEY (`mailTemplateId`),
KEY `status` (`status`),
KEY `createdAt` (`createdAt`),
KEY `modifiedAt` (`modifiedAt`),
KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
