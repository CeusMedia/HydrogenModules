<?php
/**
 *	Database model of mail templates.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Database model of mail templates.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Template extends CMF_Hydrogen_Model{

	protected $name		= 'mail_templates';
	protected $columns	= array(
		"mailTemplateId",
		"status",
		"language",
		"title",
		"plain",
		"html",
		"css",
		"styles",
		"images",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'mailTemplateId';
	protected $indices		= array(
		"status",
		"language",
		"title",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
