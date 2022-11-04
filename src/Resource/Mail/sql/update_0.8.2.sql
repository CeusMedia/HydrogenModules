ALTER TABLE  `<%?prefix%>mails` ADD `templateId` INT UNSIGNED NULL DEFAULT '0' AFTER `receiverId` ,
ADD INDEX ( `templateId` ) ;
