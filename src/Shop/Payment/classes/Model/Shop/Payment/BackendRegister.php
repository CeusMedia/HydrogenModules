<?php

use CeusMedia\HydrogenFramework\Environment;

class Model_Shop_Payment_BackendRegister implements Countable
{
	/**	@var		array			$backends */
	protected array $backends		= [];

	/**	@var		Environment		$env */
	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		array		$map
	 *	@return		string
	 */
	public function add( array $map ): string
	{
		$object	= (object) array_merge( [
			'backend'		=> '',
			'key'			=> '',
			'title'			=> '',
			'description'	=> '',
			'path'			=> '',
			'priority'		=> 0,
			'icon'			=> '',
			'feeExclusive'	=> FALSE,
			'feeFormula'	=> '',
			'countries'		=> [],
		], $map );
		$id		= $object->key ?? 'unknown';
		if( array_key_exists( $id, $this->backends ) )
			throw new RuntimeException( 'Backend with key "'.$id.'" already registered' );
		$this->backends[$id]	= $object;
		return $id;
	}

	public function count(): int
	{
		return count( $this->backends );
	}
	/**
	 * @param $backend
	 * @param string $key
	 * @param string $title
	 * @param string $path
	 * @param int $priority
	 * @param string|null $icon
	 * @return object
	 * @deprecated use ::add instead
	 */
	public function create( $backend, string $key, string $title, string $path, int $priority = 5, ?string $icon = NULL ): object
	{
		$this->backends[$key]	= (object) [
			'backend'		=> $backend,
			'key'			=> $key,
			'title'			=> $title,
			'path'			=> $path,
			'priority'		=> $priority,
			'icon'			=> $icon,
			'countries'		=> [],
			'feeExclusive'	=> FALSE,
			'feeFormula'	=> '',
		];
		return $this->backends[$key];
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
