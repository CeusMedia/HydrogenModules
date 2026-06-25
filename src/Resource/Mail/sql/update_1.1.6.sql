ALTER TABLE `<%?prefix%>mails`
    ADD COLUMN `toBeSentAt` DECIMAL(12,0) UNSIGNED DEFAULT '0' AFTER `raw`,
    ADD INDEX `toBeSentAt` (`toBeSentAt`);
