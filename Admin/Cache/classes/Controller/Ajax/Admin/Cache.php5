<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Admin_Cache extends AjaxController
{
	public function remove()
	{
		$post	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$cache	= $this->getCache();
		$result	= NULL;
		if( $cache )
			$result	= $cache->remove( $post->get( 'key' ) );
		$this->respondData( $result );
	}
}
