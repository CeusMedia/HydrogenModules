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
		try{
			$result	= $this->env->getCache()->delete( $post->get( 'key' ) );
		}
		catch( \Psr\SimpleCache\InvalidArgumentException $e ){
			$this->env->getLog()->logException( $e, $this );
			$result	= NULL;
		}
		$this->respondData( $result );
	}
}
