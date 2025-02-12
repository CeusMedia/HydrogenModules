<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer as FileWriter;

class Model_Instance{

	protected $fileName;
	protected $data;

	public function __construct( $env ){
		$this->env		= $env;
		$this->data		= [];
		$this->fileName	= 'config/instances.json';
		if( !file_exists( $this->fileName ) ){
			FileWriter::save( $this->fileName, '[]' );
			chmod( $this->fileName, 0770 );
		}
		$json		= FileReader::load( $this->fileName );
		foreach( json_decode( $json ) as $instanceId => $instance )
			$this->data[$instanceId]	= $instance;
	}

	public function add( $data ){
		if( empty( $data['id'] ) || !strlen( trim( $data['id'] ) ) )
			throw new InvalidArgumentException( 'Instance data is missing an instance ID (id)' );
		$id		= trim( $data['id'] );
		unset( $data['id'] );
		$this->data[$id]	 = $data;
		$this->save();
		return $id;
	}

	public function changeId( $from, $to ){
		$data	= $this->data[$from];
		unset( $this->data[$from] );
		$this->data[$to]	= $data;
		return (boolean) $this->save();
	}

	public function count(){
		return count( $this->data );
	}

	public function edit( $id, $data ){
		$old		= $this->data[$id];
		foreach( $data as $key => $value )
			$this->data[$id][$key]	= $value;
		if( $old != $this->data[$id] )
			return (boolean) $this->save();
		return FALSE;
	}

	public function get( $id ){
		if( !$this->has( $id ) )
			return NULL;
		$data	= $this->data[$id];
		$data->path		= empty( $data->path ) ? '' : $data->path;
		$data->protocol	= empty( $data->protocol ) ? 'https://' : $data->protocol;
		return (object) $data;
	}

	public function getAll(){
		$list		= [];
		foreach( $this->data as $id => $data ){
			$data->path		= empty( $data->path ) ? '' : $data->path;
			$data->protocol	= empty( $data->protocol ) ? 'https://' : $data->protocol;
			$list[$id]	= (object) $this->data[$id];
		}
		return $list;
	}

	public function has( $id, $key = NULL ){
		if( !isset( $this->data[$id] ) )
			return FALSE;
		if( $key )
			return isset( $this->data[$id][$key] );
		return TRUE;
	}

	public function remove( $id ){
		unset( $this->data[$id] );
		$this->save();
	}

	protected function save(){
		$json	= ADT_JSON_Formater::format( json_encode( $this->data ) );
		return FileWriter::save( $this->fileName, $json );
	}
}
?>
