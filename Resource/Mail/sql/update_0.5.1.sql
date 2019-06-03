ALTER TABLE  `<%?prefix%>mails` ADD `language` VARCHAR( 10 ) NOT NULL AFTER  `attempts` ,
ADD INDEX (  `language` ) ;
UPDATE `<%?prefix%>mails` SET language="de" WHERE language="";

ALTER TABLE  `<%?prefix%>mail_attachments` ADD `language` VARCHAR( 10 ) NOT NULL AFTER  `status` ,
ADD INDEX (  `language` ) ;
UPDATE `<%?prefix%>mail_attachments` SET language="de" WHERE language="";
