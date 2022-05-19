<?php
class Model_ModuleSource
{
	const STATUS_NONE			= 0;
	const STATUS_OKAY			= 1;

	protected $data			= [];
	protected $fileName;
	protected $hymn;
	protected $status		= self::STATUS_NONE;
	protected $default		= array(
		'active'	=> TRUE,
		'type'		=> 'folder',
		'path'		=> '',
		'title'		=> '',
	);

	public function __construct( $env )
	{
		$this->env		= $env;
		$this->fileName	= $env->uri.'.hymn';

		if( file_exists( $this->fileName ) ){
			$this->hymn	= FS_File_JSON_Reader::load( $this->fileName );
			$this->status	= self::STATUS_OKAY;
			if( property_exists( $this->hymn, 'sources' ) ){
				foreach( $this->hymn->sources as $sourceId => $sourceData ){
					$this->data[$sourceId]	= (object) array_merge( $this->default, (array) $sourceData );
				}
			}
		}
	}

	public function add( array $data ): string
	{
		if( !$this->checkSupport( FALSE ) )
			return '';

		if( empty( $data['id'] ) || !strlen( trim( $data['id'] ) ) )
			throw new InvalidArgumentException( 'Source data is missing a source ID' );

		$id		= trim( $data['id'] );
		unset( $data['id'] );
		$data	 = array_merge( $this->default, $data );
		$data['active']	 = (bool) $data['active'];
		$this->data[$id]	 = (object) $data;
		$this->save();
		return $id;
	}

	public function changeId( string $from, string $to ): bool
	{
		if( !$this->checkSupport( FALSE ) )
			return FALSE;
		$data	= $this->data[$from];
		unset( $this->data[$from] );
		$this->data[$to]	= $data;
		return (boolean) $this->save();
	}

	public function count(): int
	{
		return count( $this->data );
	}

	public function edit( string $id, array $data ): bool
	{
		if( !$this->checkSupport( FALSE ) )
			return FALSE;
		if( !$this->has( $id ) )
			throw new DomainException( 'Invalid source ID' );

		$old	= $this->data[$id];
		$data	= array_merge( $this->default, (array) $old, $data );
		$data['active']	 	= (bool) $data['active'];
		$this->data[$id]	= (object) $data;
		if( $old != $this->data[$id] )
			return (bool) $this->save();
		return FALSE;
	}

	public function get( string $id, bool $strict = TRUE )
	{
		if( !$this->checkSupport( FALSE ) )
			return NULL;
		if( !$this->has( $id ) ){
			if( $strict )
				throw new DomainException( 'Invalid source ID' );
			return NULL;
		}
		return (object) $this->data[$id];
	}

	public function getAll( bool $activeOnly = TRUE ): array
	{
		$list		= [];
		if( !$this->checkSupport( FALSE ) )
			return $list;
		foreach( $this->data as $id => $data ){
			if( !$activeOnly || !empty( $data->active ) )
				$list[$id]	= (object) $this->data[$id];
		}
		return $list;
	}

	public function has( string $id, string $key = NULL ): bool
	{
		if( !$this->checkSupport( FALSE ) )
			return FALSE;
		if( !isset( $this->data[$id] ) )
			return FALSE;
		if( $key )
			return property_exists( $this->data[$id], $key );
		return TRUE;
	}

	public function remove( string $id ): bool
	{
		if( !$this->checkSupport( FALSE ) )
			return FALSE;
		unset( $this->data[$id] );
		return (bool) $this->save();
	}

	//  --  PROTECTED  --  //

	protected function checkSupport( $strict = TRUE ): bool
	{
		if( $this->status === self::STATUS_OKAY )
			return TRUE;
		$message	= 'This feature requires a Hymn file within the application.';
		if( $strict )
			throw new RuntimeException( $message );
		$this->env->getMessenger()->noteFailure( $message );
		return FALSE;
	}

	protected function save(): int
	{
		$this->checkSupport();
		$this->hymn->sources	= $this->data;
		return FS_File_JSON_Writer::save( $this->fileName, $this->hymn, TRUE );
	}
}
