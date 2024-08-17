<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Admin_Cache extends AjaxController
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function remove(): void
	{
		$post	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$cache	= $this->env->getCache();
		$result	= NULL;
		$key	= $post->get( 'key' );
		try{
			$result	= $cache->delete( $key );
		}
		catch( \Psr\SimpleCache\InvalidArgumentException $e ){
		}
		$this->respondData( $result );
	}
}
