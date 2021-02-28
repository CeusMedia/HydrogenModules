<?php
class Controller_Ajax_Admin_Cache extends CMF_Hydrogen_Controller_Ajax
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
