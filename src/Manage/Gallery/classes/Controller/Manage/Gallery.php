<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Gallery extends Controller
{
	protected string $path;

	/**
	 *	@param		string|NULL		$folder
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( ?string $folder = NULL ): void
	{
		$this->addData( 'folder', $folder );
		$request	= $this->env->getRequest();
		if( $request->has( 'add' ) ){
			$model	= new Model_Gallery( $this->env );
			$title	= $request->get( 'title' );
			$data	= array(
				'folder'	=> $request->get( 'folder' ),
				'title'		=> $title,
				'content'	=> $request->get( 'content' ),
				'createdAt'	=> time(),
				'status'	=> 0,
			);
			$galleryId	= $model->add( $data, FALSE );
			$this->env->getMessenger()->noteSuccess( 'Gallery "'.$title.'" imported.' );
			$this->restart( 'edit/'.$galleryId, TRUE );
		}
	}

	/**
	 *	@param		int|string|NULL		$galleryId
	 *	@return		void
	 */
	public function index( int|string|NULL $galleryId = NULL )
	{
	}

	/**
	 *	@param		int|string		$galleryId
	 *	@return		void
	 */
	public function edit( int|string $galleryId )
	{

	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$config		= $this->env->getConfig();
#		$this->path	= $config->get( 'path.images' ).$config->get( 'module.gallery_compact.path' );
	}
}
