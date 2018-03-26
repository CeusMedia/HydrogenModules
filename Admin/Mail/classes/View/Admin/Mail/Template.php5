<?php
class View_Admin_Mail_Template extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
	}
	public function add(){
		$template	= $this->getData( 'template' );
		if( !$template->plain )
			$template->plain	= FS_File_Reader::load( $this->getTemplateUriFromFile( 'admin/mail/template/default.txt' ) );
		if( !$template->html )
			$template->html		= FS_File_Reader::load( $this->getTemplateUriFromFile( 'admin/mail/template/default.html' ) );
		if( !$template->css )
			$template->css		= FS_File_Reader::load( $this->getTemplateUriFromFile( 'admin/mail/template/default.css' ) );
	}
	public function edit(){}
	public function index(){}
	public function remove(){}
}
?>
