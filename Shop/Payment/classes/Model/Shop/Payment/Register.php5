<?php
class Model_Shop_Payment_Register{

	protected $backends	= array();

	public function add( $backend, $key, $title, $path, $priority = 5, $icon = NULL ){
		$this->backends[$key]	= (object) array(
			'backend'	=> $backend,
			'key'		=> $key,
			'title'		=> $title,
			'path'		=> $path,
			'priority'	=> $priority,
			'icon'		=> $icon,
		);
		return $key;
	}

	public function get( $key, $strict = TRUE ){
		if( !$this->has( $key ) ){
			if( $strict )
				throw new RangeException( 'Invalid payment backend key: '.$key );
			return NULL;
		}
		return $this->backends[$key];
	}

	public function getAll(){
		return $this->backends;
	}

	public function has( $key ){
		return array_key_exists( $key, $this->backends );
	}

	public function remove( $key, $strict = TRUE ){
		if( !$this->has( $key ) ){
			if( $strict )
				throw new RangeException( 'Invalid payment backend key: '.$key );
			return FALSE;
		}
		unset( $this->backends[$key] );
		return TRUE;
	}

/*	@todo		implement
	public function sort( $by ){
		$list	= $this->backends;
		while( $by ){
			if( $by & self::SORT_PRIORITY ){

			}
		}
	}*/
}
