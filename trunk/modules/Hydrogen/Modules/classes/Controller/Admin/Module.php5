<?php
class Controller_Admin_Module extends CMF_Hydrogen_Controller{
	public function index(){
		$model	= new Model_Module( $this->env );
		$this->addData( 'modules', $model->getAll() );
		$this->addData( 'modulesAvailable', $model->getAvailable() );
		$this->addData( 'modulesInstalled', $model->getInstalled() );
		$this->addData( 'modulesNotInstalled', $model->getNotInstalled() );
	}

	public function view( $moduleId ){
		$model	= new Model_Module( $this->env );
		$this->addData( 'module', $model->get( $moduleId ) );
	}
}
?>
