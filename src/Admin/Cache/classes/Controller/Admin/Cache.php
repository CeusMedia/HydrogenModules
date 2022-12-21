<?php

use CeusMedia\HydrogenFramework\Controller;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class Controller_Admin_Cache extends Controller
{
	public function add()
	{
		$post	= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$cache	= $this->getCache();
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
				$cache->set( $key, $value );
		}
		$this->restart( NULL, TRUE );
	}

	public function index()
	{
		$list		= [];
		$cache		= $this->getCache();
		$persistent	= $cache->getType() !== 'Noop';
		if( $cache && $persistent ){
			foreach( $cache->index() as $key ){
				$value	= $cache->get( $key );
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

	/**
	 *	@return		SimpleCacheInterface|NULL
	 */
	protected function getCache(): ?SimpleCacheInterface
	{
		$env	= $this->env->has( 'remote' ) ? $this->env->get( 'remote' ) : $this->env;
		if( $env->has( 'cache' ) )
			return $env->getCache();
		return NULL;
	}
}
