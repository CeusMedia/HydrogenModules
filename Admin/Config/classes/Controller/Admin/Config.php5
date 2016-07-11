<?php
class Controller_Admin_Config extends CMF_Hydrogen_Controller {

	protected function __onInit(){
		$modules	= $this->env->getModules()->getAll();
		$versions	= array();
		foreach( array_keys( $modules ) as $moduleId ){
			$fileName	= "config/modules/".$moduleId.".xml";
			$file		= new FS_File_Backup( $fileName );
			$version	= $file->getVersion();
			$version	= is_int( $version ) ? $version + 1 : 0;
			$versions[$moduleId]	= $version;
		}
		$this->addData( 'modules', $modules );
		$this->addData( 'versions', $versions );
	}

	public function index(){
	}

	public function edit( $moduleId ){
		$words		= (object) $this->getWords( 'msg' );
		$request	= $this->env->getRequest();
		$modules	= $this->env->getModules()->getAll();

		if( !array_key_exists( $moduleId, $modules ) ){
			$this->env->getMessenger()->noteError( 'Invalid module ID.' );
			$this->restart( NULL, TRUE );
		}
		$module		= $modules[$moduleId];

		if( $request->has( 'save' ) ){
			$list	= array();
			foreach( $request->getAll() as $key => $value ){
				if( substr_count( $key, "|" ) ){
					list( $partModuleId, $partKey ) = explode( "|", $key );
					$partKey	= preg_replace( "/([a-z0-9])_(\S)/", "\\1.\\2", $partKey );
					if( !isset( $list[$partModuleId] ) )
						$list[$partModuleId]	= array();
					if( preg_match( "@(\r)\n@", $value ) )
						$value	= preg_replace( "@(\r)\n@", ",", $value );
					else if( strlen( $value ) == 0 )
						$value	= NULL;
					$list[$partModuleId][$partKey]	= $value;
				}
			}
			foreach( $list as $moduleId => $pairs )
				$this->configureLocalModule( $moduleId, $pairs );
			$this->env->getMessenger()->noteSuccess( $words->successSaved );
			$this->restart( 'edit/'.$moduleId, TRUE );
		}

/*		$fileName	= "config/modules/".$moduleId.".xml";
		$file		= new FS_File_Backup( $fileName );
		$version	= $file->getVersion();
		$version	= is_int( $version ) ? $version + 1 : 0;
*/
		$this->addData( 'module', $module );
		$this->addData( 'moduleId', $moduleId );
//		$this->addData( 'version', $version );
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
				$type	= $node->getAttribute( 'type' );
				$value	= $node->getValue();
				if( in_array( $type, array ( "bool", "boolean" ) ) ){
					$value	= in_array( $value, array( '1', 'yes', 'true' ) ) ? "true" : "false";
				}
				$node->setValue( $value );
			}
			$original	= $tree->asXml();
			foreach( $tree->config as $nr => $node ){
				$name	= $node->getAttribute( 'name' );
				$type	= $node->getAttribute( 'type' );
				if( array_key_exists( $name, $pairs ) ){
					if( in_array( $type, array ("bool", "boolean" ) ) ){
						$pairs[$name]	= in_array( $pairs[$name], array( '1', 'yes', 'true' ) );
						$pairs[$name]	= $pairs[$name] ? "true" : "false";
					}
					$node->setValue( $pairs[$name] );
				}
			}
			if( $original === ( $xmlNew = $tree->asXml() ) )
				return 0;

			$file	= new FS_File_Backup( $fileName );
			$file->store();
			FS_File_Writer::save( $fileName.".orig", $original );
			return FS_File_Writer::save( $fileName, $xmlNew );
		}
		catch( Exception $e ){
			die( $moduleId.":".$name );
		}
	}
}
