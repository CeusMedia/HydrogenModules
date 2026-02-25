ALTER TABLE `<%?prefix%>mails`
    ADD COLUMN `priority` TINYINT(1) NOT NULL DEFAULT '0' AFTER `templateId`,
    ADD INDEX `priority` (`priority`);
