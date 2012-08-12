<?php
class Controller_Admin_Cache extends CMF_Hydrogen_Controller{

	public function add(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->getCache();
		$result	= NULL;
		if( $cache ){
			$words	= (object) $this->getWords( 'add' );
			$key	= $post->get( 'key' );
			$value	= $post->get( 'value' );
			switch( $post->get( 'type' ) ){
				case 'integer':
					$value	= (int) $value;
					break;
				case 'float':
					$value	= (float) $value;
					break;
			}
			if( !strlen( trim( $key ) ) )
				$this->env->getMessenger()->noteError( $words->errorKeyMissing );
			else
				$result	= $cache->set( $key, serialize( $value ) );
		}
		$this->restart( './admin/cache' );
	}

	public function ajaxEdit(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->getCache();
		$result	= NULL;
		if( $cache )
			$result	= $cache->set( $post->get( 'key' ), serialize( $post->get( 'value' ) ) );
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxRemove(){
		$post	= $this->env->getRequest()->getAllFromSource( "post" );
		$cache	= $this->getCache();
		$result	= NULL;
		if( $cache )
			$result	= $cache->remove( $post->get( 'key' ) );
		print( json_encode( $result ) );
		exit;
	}

	protected function getCache(){
		$env	= $this->env->has( 'remote' ) ? $this->env->getRemote() : $this->env;
		if( $env->has( 'cache' ) )
			return $env->getCache();
		return NULL;
	}
	
	public function index(){
		$list		= array();
		$cache		= $this->getCache();
		$persistent	= get_class( $cache ) != 'CMM_SEA_Adapter_Noop';
		if( $cache && $persistent ){
			foreach( $cache->index() as $key ){
				$value	= unserialize( $cache->get( $key ) );
				$list[]	= (object) array(
					'key'	=> $key,
					'value'	=> $value,
					'type'	=> gettype( $value )
				);
			}
		}
		$this->addData( 'hasCache', $cache && $persistent );
		$this->addData( 'list', $list );
	}
}
?>