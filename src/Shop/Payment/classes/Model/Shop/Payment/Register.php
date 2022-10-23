<?php
class Model_Shop_Payment_Register
{
	protected $backends	= [];

	public function add( $backend, string $key, string $title, string $path, int $priority = 5, string $icon = NULL )
	{
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

	public function get( string $key, bool $strict = TRUE )
	{
		if( !$this->has( $key ) ){
			if( $strict )
				throw new RangeException( 'Invalid payment backend key: '.$key );
			return NULL;
		}
		return $this->backends[$key];
	}

	public function getAll(): array
	{
		return $this->backends;
	}

	public function has( string $key ): bool
	{
		return array_key_exists( $key, $this->backends );
	}

	public function remove( string $key, bool $strict = TRUE ): bool
	{
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
