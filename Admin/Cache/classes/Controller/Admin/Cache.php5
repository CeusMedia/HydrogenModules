<?php
class Controller_Admin_Cache extends CMF_Hydrogen_Controller{

	public function add(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->env->getCache();
		
		$value	= $post->get( 'value' );
		switch( $post->get( 'type' ) ){
			case 'integer':
				$value	= (int) $value;
				break;
			case 'float':
				$value	= (float) $value;
				break;
		}

		$result	= $cache->set( $post->get( 'key' ), serialize( $value ) );
		$this->restart( './admin/cache' );
	}

	public function ajaxEdit(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->env->getCache();
		$result	= $cache->set( $post->get( 'key' ), serialize( $post->get( 'value' ) ) );
		print( json_encode( $result ) );
		die;
	}

	public function ajaxRemove(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->env->getCache();
		$result	= $cache->remove( $post->get( 'key' ) );
		print( json_encode( $result ) );
		die;
	}

	public function index(){
	}
}
?>