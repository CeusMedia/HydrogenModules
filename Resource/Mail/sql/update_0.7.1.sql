ALTER TABLE `<%?prefix%>mails` ADD `mailClass` VARCHAR(200) NOT NULL AFTER `subject`, ADD INDEX (`mailClass`);
