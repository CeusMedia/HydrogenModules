<?php
class Controller_Admin_Config extends CMF_Hydrogen_Controller {

	public function index(){
		$words		= (object) $this->getWords( 'msg' );
		$request	= $this->env->getRequest();
		if( $request->has( 'save' ) ){
			$list	= array();
			foreach( $request->getAll() as $key => $value ){
				if( substr_count( $key, "|" ) ){
					list( $moduleId, $key ) = explode( "|", $key );
					$key	= preg_replace( "/([a-z0-9])_(\S)/", "\\1.\\2", $key );
					if( !isset( $list[$moduleId] ) )
						$list[$moduleId]	= array();
					if( substr_count( $value, "\n" ) )
						$value	= str_replace( "\n", ",", $value );
					else if( strlen( $value ) == 0 )
						$value	= NULL;
					$list[$moduleId][$key]	= $value;
				}
			}
			foreach( $list as $moduleId => $pairs )
				$this->configureLocalModule( $moduleId, $pairs );
			$this->env->getMessenger()->noteSuccess( $words->successSaved );
			$this->restart( NULL, TRUE );
		}

		$modules	= $this->env->getModules()->getAll();
		$versions	= array();
		foreach( array_keys( $modules ) as $moduleId ){
			$fileName	= "config/modules/".$moduleId.".xml";
			$file		= new FS_File_Backup( $fileName );
			$version	= $file->getVersion();
			$version	= is_int( $version ) ? $version + 1 : 0;
			$versions[$moduleId]	= $version;
		}
		$this->addData( 'config', $modules );
		$this->addData( 'versions', $versions );
	}

	public function restore( $moduleId ){
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		if( file_exists( $fileName ) ){
			$file	= new FS_File_Backup( $fileName );
			$file->restore( -1, TRUE );
			$words		= (object) $this->getWords( 'msg' );
			$this->env->getMessenger()->noteSuccess( $words->successRestored, $moduleId );
		}
		$this->restart( NULL, TRUE );
	}

	protected function configureLocalModule( $moduleId, $pairs ){
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		$xml		= FS_File_Reader::load( $fileName );
		$tree		= new XML_Element( $xml );
		try{
			foreach( $tree->config as $nr => $node ){
				$name	= $node->getAttribute( 'name' );
				$type	= $node->getAttribute( 'type' );
				if( array_key_exists( $name, $pairs ) )
					if( $type == "boolean" )
						$pairs[$name]	= $pairs[$name] ? "yes" : "no";
					$node->setValue( $pairs[$name] );
			}
			if( $xml == ( $xmlNew = $tree->asXml() ) )
				return 0;
			$file	= new FS_File_Backup( $fileName );
			$file->store();
			return FS_File_Writer::save( $fileName, $xmlNew );
		}
		catch( Exception $e ){
			die( $moduleId.":".$name );
		}
	}
}
