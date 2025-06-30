<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Work_FTP extends AjaxController
{
	/**	@var	Logic_FTP	$logic */
	protected Logic_FTP $logic;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function index(): void
	{
		$path		= $this->env->getRequest()->getFromSource( 'path', 'GET' );
		$folders	= [];
		$files		= [];
		foreach( $this->logic->index( $path ) as $entry ){
			$entry	= (object) $entry;
			if( $entry->isdir )
				$folders[$entry->name]	= $entry;
			else
				$files[$entry->name]	= $entry;
		}
		ksort( $folders );
		ksort( $files );
		$list	= $folders + $files;
		$this->respondData( $list );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_FTP( $this->env );
	}
}