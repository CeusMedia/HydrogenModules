<?php
class Model_Instance{

	public function __construct( $env ){
		$this->env		= $env;
		$this->fileName	= 'config/instances.ini';
		if( !file_exists( $this->fileName ) ){
			touch( $this->fileName );
			chmod( $this->fileName, 0770 );
		}
		$this->file		= new File_INI_Editor( $this->fileName, TRUE );
	}

	public function add( $data ){
		if( empty( $data['id'] ) || !strlen( trim( $data['id'] ) ) )
			throw new InvalidArgumentException( 'Instance data is missing an instance ID (id)' );
		$id		= trim( $data['id'] );
		$this->file->addSection( $id );
		unset( $data['id'] );
		foreach( $data as $key => $value )
			$this->file->setProperty( $key, $value, $id );
		return $id;
	}

	public function changeId( $from, $to ){
		return $this->file->renameSection( $from, $to );
	}

	public function count(){
		return count( $this->file->getSections() );
	}

	public function edit( $id, $data ){
		$changed	= FALSE;
		if( $this->file->hasSection( $id ) ){
			foreach( $data as $key => $value ){
				if( $this->file->hasProperty( $key, $id ) ){
					if( strlen( trim( $value ) ) )
						$this->file->setProperty( $key, $value, $id );
					else
						$this->file->removeProperty( $key, $id );
				}
				else if( strlen( trim( $value ) ) )
					$this->file->addProperty( $key, $value, NULL, TRUE, $id );
				$changed	= TRUE;
			}
		}
		return $changed;
	}

	public function has( $id ){
		return $this->file->hasSection( $id );
	}

	public function get( $id ){
		$data			= (object) $this->file->getProperties( FALSE, $id );
		$data->id		= $id;
		$data->path		= empty( $data->path ) ? '' : $data->path;
		$data->protocol	= empty( $data->protocol ) ? 'http://' : $data->protocol;
		return $data;
	}

	public function getAll(){
		$list		= array();
		$sections	= $this->file->getSections();
		foreach( $sections as $section ){
			$data	= (object) array( 'id' => $section );
			foreach( $this->file->getProperties( NULL, $section ) as $key => $value )
				$data->$key	= $value;
			$data->path		= empty( $data->path ) ? '' : $data->path;
			$data->protocol	= empty( $data->protocol ) ? 'http://' : $data->protocol;
			$list[$section]		= $data;
		}
		return $list;
	}

	public function remove( $id ){
		$this->file->removeSection( $id );
	}
}
?>
