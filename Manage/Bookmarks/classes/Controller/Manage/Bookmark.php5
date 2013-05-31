<?php
class Controller_Manage_Bookmark extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->model	= new Model_Bookmark( $this->env );
		$this->addData( 'bookmarks', $this->model->getAll( array( 'status' => '0' ), array( 'title' => 'ASC' ) ) );
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
				$bookmarkId	= $this->model->add( $data );
				$messenger->noteSuccess( 'Das Lesezeichen "%s" wurde hinzugefügt.', $data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
	}

	public function edit( $bookmarkId ){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		if( !($bookmark = $this->model->get( $bookmarkId ) ) ){
			$messenger->noteError( 'Dieses Lesezeichen ist nicht vorhanden. Weiterleitung zur Liste.' );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save' ) ){
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
				$data['modifiedAt']	= time();
				$this->model->edit( $bookmarkId, $data );
				$messenger->noteSuccess( 'Das Lesezeichen "%s" wurde gespeichert.', $data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'bookmark', $this->model->get( $bookmarkId ) );
	}

	public function index(){
	}


	public function remove( $bookmarkId ){
		$this->model->remove( $bookmarkId );
		$this->restart( NULL, TRUE );
	}
}
?>
