DROP TABLE IF EXISTS `<%?prefix%>mail_attachments`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>mail_attachments` (
`mailAttachmentId` int(10) unsigned NOT NULL AUTO_INCREMENT,
`status` tinyint(1) NOT NULL,
`className` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
`filename` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
`mimeType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
`countAttached` int(11) NOT NULL,
`createdAt` decimal(12,0) NOT NULL,
PRIMARY KEY (`mailAttachmentId`),
KEY `status` (`status`),
KEY `className` (`className`),
KEY `filename` (`filename`),
KEY `mimeType` (`mimeType`),
KEY `createdAt` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
