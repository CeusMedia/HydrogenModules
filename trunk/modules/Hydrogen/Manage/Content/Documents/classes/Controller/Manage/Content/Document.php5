<?php
class Controller_Manage_Content_Document extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->path		= '../documents/';
		$this->model	= new Model_Document( $this->env, $this->path );
		$this->addData( 'documents', $this->model->index() );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$upload	= (object) $request->get( 'upload' );
			if( $upload->error ){
			}
			else{
				move_uploaded_file( $upload->tmp_name, $this->path.$upload->name );
				$this->env->getMessenger()->noteSuccess( 'Datei hochgeladen.' );
				$this->restart( NULL, TRUE );
			}
		}
	}

	public function index(){
	}

	public function remove(){
	}
}
?>
