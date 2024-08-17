<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Blog_Category extends Controller
{
	protected MessengerResource $messenger;
	protected Model_Blog_Category $modelCategory;
	protected Model_Blog_Comment $modelComment;
	protected Model_Blog_Post $modelPost;
	protected Model_User $modelUser;
	protected Dictionary $moduleConfig;
	protected HttpRequest $request;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->get( 'save' ) ){
			$data	= [
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'language'		=> $this->request->get( 'language' ),
				'content'		=> $this->request->get( 'content' ),
				'createdAt'		=> time(),
			];
			$categoryId		= $this->modelCategory->add( $data );
			$this->restart( NULL, TRUE );
		}
		$data	= [];
		foreach( $this->modelCategory->getColumns() as $column ){
			$data[$column]	= $this->request->get( $column );
		}
		$this->addData( 'category', (object) $data );
	}

	/**
	 *	@param		int|string		$categoryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $categoryId ): void
	{
		$category	= $this->checkCategory( $categoryId );
		if( $this->request->get( 'save' ) ){
			$data	= [
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'language'		=> $this->request->get( 'language' ),
				'content'		=> $this->request->get( 'content' ),
				'modifiedAt'	=> time(),
			];
			$this->modelCategory->edit( $categoryId, $data, FALSE );
			$this->restart( 'edit/'.$categoryId, TRUE );
		}
		$this->addData( 'category', $category );
	}

	/**
	 *	@param		int|string|NULL		$categoryId
	 *	@return		void
	 */
	public function index( int|string|NULL $categoryId = NULL ): void
	{
		if( $categoryId )
			$this->restart( 'edit/'.$categoryId, TRUE );
		$categories		= $this->modelCategory->getAll();
		$this->addData( 'categories', $categories );
	}

/*
	public function remove( $categoryId )
	{
		$category	= $this->checkCategory( $categoryId );
		$this->addData( 'category', $category );
	}*/

	protected function __onInit(): void
	{
		$this->modelCategory	= new Model_Blog_Category( $this->env );
		$this->modelComment		= new Model_Blog_Comment( $this->env );
		$this->modelPost		= new Model_Blog_Post( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_blog.', TRUE );
		if( $this->moduleConfig->get( 'mail' ) )
			if( !$this->env->getModules()->has( 'Resource_Mail' ) )
				$this->messenger->noteFailure( 'Module Info:Blog has mails enabled, but module Resource:Mail is missing.' );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	/**
	 *	@param		int|string		$categoryId
	 *	@param		bool			$strict
	 *	@return		object|
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkCategory( int|string $categoryId, bool $strict = FALSE ): object
	{
		$category	= $this->modelCategory->get( (int) $categoryId );
		if( !$category ){
			if( $strict )
				throw new OutOfRangeException( 'Invalid category ID' );
			$this->messenger->noteError( 'Invalid category ID.' );
			$this->restart( NULL, TRUE );
		}
		return $category;
	}
}
