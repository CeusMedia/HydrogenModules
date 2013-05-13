<?php
class Controller_Manage_Content_Link extends CMF_Hydrogen_Controller{
	
	public function __onInit(){
		$this->model	= new Model_Link( $this->env );
		$this->addData( 'links', $this->model->getAll( array( 'status' => '0' ), array( 'title' => 'ASC' ) ) );
	}

	public function add(){
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$messenger	= $this->env->getMessenger();
			$data	= array(
				'url'	=> $request->get( 'url' ),
				'title'	=> $request->get( 'title' ),
			);
			if( !strlen( trim( $data['url'] ) ) )
				$messenger->noteError( 'Die Adresse fehlt.' );
			else if( !preg_match( "/^(ht|f)tp:\/\//", $data['url'] ) )
				$messenger->noteError( 'Die Adresse ist ungültig: Das Protokoll fehlt (z.B. http://).' );
			else if( !strlen( trim( $data['title'] ) ) )
				$messenger->noteError( 'Der Titel fehlt.' );
			else{
				$data['createdAt']	= time();
				$linkId	= $this->model->add( $data );
				$messenger->noteSuccess( 'Der Link wurde hinzugefügt.' );
				$this->restart( NULL, TRUE );
			}
		}
	}

	public function edit( $linkId ){
		$data	= $this->env->getRequest()->getAll();
		$linkId	= $this->model->edit( $linkId, $data );
		$this->restart( NULL, TRUE );
	}

	public function index(){
	}


	public function remove( $linkId ){
		$this->model->remove( $linkId );
		$this->restart( NULL, TRUE );
	}	
}
?>