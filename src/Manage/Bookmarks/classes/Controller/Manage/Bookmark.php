<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Bookmark extends Controller
{
	protected Model_Bookmark $model;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$messenger	= $this->env->getMessenger();
			$data	= [
				'url'	=> $request->get( 'url' ),
				'title'	=> $request->get( 'title' ),
			];
			if( !strlen( trim( $data['url'] ) ) )
				$messenger->noteError( 'Die Adresse fehlt.' );
			else if( !preg_match( "/^(ht|f)tps?:\/\//", $data['url'] ) )
				$messenger->noteError( 'Die Adresse ist ungültig: Das Protokoll fehlt (z.B. https://).' );
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

	/**
	 *	@param		int|string		$bookmarkId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $bookmarkId ): void
	{
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$bookmark	= $this->model->get( $bookmarkId );
		if( NULL === $bookmark ){
			$messenger->noteError( 'Dieses Lesezeichen ist nicht vorhanden. Weiterleitung zur Liste.' );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save' ) ){
			$data	= [
				'url'	=> $request->get( 'url' ),
				'title'	=> $request->get( 'title' ),
			];
			if( !strlen( trim( $data['url'] ) ) )
				$messenger->noteError( 'Die Adresse fehlt.' );
			else if( !preg_match( "/^(http|https|ftp):\/\//", $data['url'] ) )
				$messenger->noteError( 'Die Adresse ist ungültig: Das Protokoll fehlt (z.B. https://).' );
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

	public function index()
	{
	}

	/**
	 *	@param		int|string		$bookmarkId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $bookmarkId ): void
	{
		$this->model->remove( $bookmarkId );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model	= new Model_Bookmark( $this->env );
		$this->addData( 'bookmarks', $this->model->getAll( ['status' => '0'], ['title' => 'ASC'] ) );
	}
}
