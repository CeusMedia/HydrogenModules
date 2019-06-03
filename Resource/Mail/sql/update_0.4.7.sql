ALTER TABLE  `<%?prefix%>mails` ADD  `senderAddress` VARCHAR( 255 ) NOT NULL AFTER `attempts` ,
ADD INDEX (  `senderAddress` ) ;
