<?php
class Controller_Manage_Bookmark extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->model	= new Model_Bookmark( $this->env );
		$this->addData( 'bookmarks', $this->model->getAll( array( 'status' => '0' ), array( 'title' => 'ASC' ) ) );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$words		= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes	= (object) $words['link-prefixes'];

		$list		= array();
		$model		= new Model_Bookmark( $env );
		$orders		= array( 'title' => 'ASC' );
		foreach( $model->getAll( array(), $orders ) as $nr => $link ){
			$list[]	= (object) array(
				'title'	=> /*$prefixes->bookmark.*/$link->title,
				'type'	=> 'link:bookmark',
				'value'	=> $link->url,
			);
		}
		$list	= array( (object) array(
			'title'	=> $prefixes->bookmark,
			'menu'	=> array_values( $list ),
		) );
//		$context->list	= array_merge( $context->list, array_values( $list ) );
		$context->list	= array_merge( $context->list, $list );
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
			else if( !preg_match( "/^(ht|f)tps?:\/\//", $data['url'] ) )
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
			else if( !preg_match( "/^(http|https|ftp):\/\//", $data['url'] ) )
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
