<?php
class Model_ModuleSource{

	/**	@var	File_INI_Editor		$file		INI editor pointing to module sources file */
	protected $file;
	protected $fileName;
	
	public function __construct( $env ){
		$this->env		= $env;
		$this->fileName	= 'config/modules/sources.ini';

		if( !file_exists( dirname( $this->fileName ) ) ){
			Folder_Editor::createFolder( dirname( $this->fileName ), 0770 );
		}
		if( !file_exists( $this->fileName ) ){
			touch( $this->fileName );
			chmod( $this->fileName, 0770 );
		}
		$this->file		= new File_INI_Editor( $this->fileName, TRUE );
		
	}

	public function add( $data ){
		$id		= $data['id'];
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
					if( strlen( trim( $value ) ) || is_bool( $value ) )
						$this->file->setProperty( $key, $value, $id );
					else
						$this->file->removeProperty( $key, $id );
					$changed	= TRUE;
				}
				else{
					$this->file->addProperty( $key, $value, NULL, TRUE, $id );
					$changed	= TRUE;
				}
			}
		}
		return $changed;
	}

	public function get( $id ){
		$data		= (object) $this->file->getProperties( FALSE, $id );
		$data->id	= $id;
		return $data;
	}

	public function getAll( $activeOnly = TRUE ){
		$list		= array();
		$sections	= $this->file->getSections();
		foreach( $sections as $section ){
			$data	= array( 'id' => $section );
			foreach( $this->file->getProperties( NULL, $section ) as $key => $value )
				$data[$key]	= $value;
			if( $activeOnly && !$data['active'] )
				continue;
			$list[$section]	= (object) $data;
		}
		return $list;
	}

	public function has( $id ){
		return $this->file->hasSection( $id );
	}

	public function remove( $id ){
		$this->file->removeSection( $id );
	}
}
?>