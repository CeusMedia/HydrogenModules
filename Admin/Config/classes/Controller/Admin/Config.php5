<?php
class Controller_Admin_Config extends CMF_Hydrogen_Controller {

	protected function __onInit(){
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
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

/*	public function direct(){
		$words		= (object) $this->getWords( 'msg' );
		$request	= $this->env->getRequest();
		$modules	= $this->env->getModules()->getAll();

		$versions	= array();
		foreach( array_keys( $modules ) as $moduleId ){
			$fileName	= "config/modules/".$moduleId.".xml";
			$file		= new FS_File_Backup( $fileName );
			$version	= $file->getVersion();
			$version	= is_int( $version ) ? $version + 1 : 0;
			$versions[$moduleId]	= $version;
		}
		$this->addData( 'versions', $versions );
	}*/

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( 'filter_admin_config_category' );
			$this->session->remove( 'filter_admin_config_moduleId' );
			$this->session->remove( 'filter_admin_config_query' );
		}
		if( $this->request->has( 'category' ) )
			$this->session->set( 'filter_admin_config_category', trim( $this->request->get( 'category' ) ) );
		if( $this->request->has( 'moduleId' ) )
			$this->session->set( 'filter_admin_config_moduleId', trim( $this->request->get( 'moduleId' ) ) );
		if( !$this->session->get( 'filter_admin_config_category' ) )
			$this->session->remove( 'filter_admin_config_moduleId' );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$filterCategory	= $this->session->get( 'filter_admin_config_category' );
		$filterModuleId	= $this->session->get( 'filter_admin_config_moduleId' );

		$foundModules	= $this->env->getModules()->getAll();
		$categories		= array();
		foreach( $foundModules as $module ){
			if( $module->category && $module->config ){
				if( !isset( $categories[$module->category] ) )
					$categories[$module->category]	= 0;
				$categories[$module->category]	+= 1;
			}
		}
		$filteredModules	= $foundModules;

		if( $filterCategory ){
			$modules		= array();
			foreach( $foundModules as $moduleId => $module )
				if( $module->config )
					if( $filterCategory === $module->category )
						$modules[$moduleId]	= $module;
			$filteredModules	= $foundModules	= $modules;
			if( !$filterModuleId && count( $foundModules ) === 1 ){
				$module	= array_slice( array_keys( $foundModules ), 0, 1 );
				$this->restart( 'filter?moduleId='.$module[0], TRUE );
			}
		}
		if( $filterModuleId ){
			if( !array_key_exists( $filterModuleId, $foundModules ) )
				$this->restart( 'filter?moduleId=', TRUE );
			$modules		= array();
			foreach( $foundModules as $moduleId => $module )
				if( $module->id === $filterModuleId )
					$modules[$moduleId]	= $module;
			$foundModules	= $modules;
		}

		$this->addData( 'filterCategory', $this->session->get( 'filter_admin_config_category' ) );
		$this->addData( 'filterModuleId', $this->session->get( 'filter_admin_config_moduleId' ) );
		$this->addData( 'categories', $categories );
		$this->addData( 'filteredModules', $filteredModules );
		$this->addData( 'foundModules', $foundModules );
	}

	public function edit( $moduleId = NULL ){
		$words		= (object) $this->getWords( 'msg' );
		$request	= $this->env->getRequest();
		$modules	= $this->env->getModules()->getAll();

		if( $moduleId ){
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
				print_m( $list );
				$pairs	= $list[$moduleId];
				$this->configureLocalModule( $moduleId, $pairs );
				$this->env->getMessenger()->noteSuccess( $words->successSaved );
				$this->restart( NULL, TRUE );
				$this->restart( 'edit/'.$moduleId, TRUE );
			}

			$versions	= array();
			$fileName	= "config/modules/".$moduleId.".xml";
			$file		= new FS_File_Backup( $fileName );
			$version	= $file->getVersion();
			$version	= is_int( $version ) ? $version + 1 : 0;
			$versions	= $version;
			$this->addData( 'module', $module );
			$this->addData( 'versions', $versions );
		}
		$this->addData( 'moduleId', $moduleId );
	}

	public function restore( $moduleId ){
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		if( file_exists( $fileName ) ){
			$file	= new FS_File_Backup( $fileName );
			$fileVersion	= $file->getVersion();
			if( $fileVersion === NULL ){
				$this->env->getMessenger()->noteError( 'No backup available for module "'.$moduleId0.'"' );
				$this->restart( 'module/'.$moduleId, TRUE );
			}
			$file->restore( -1, TRUE );
			$words		= (object) $this->getWords( 'msg' );
			$this->env->getMessenger()->noteSuccess( $words->successRestored, $moduleId );
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	protected function configureLocalModule( $moduleId, $pairs ){
		$fileName	= $this->env->uri.'config/modules/'.$moduleId.'.xml';
		if( !is_writable( $fileName ) )
			throw new RuntimeException( 'Config file of module "'.$moduleId.'" is not writable' );
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

			@unlink( "config/modules.cache.serial" );
			return FS_File_Writer::save( $fileName, $xmlNew );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( $e->getMessage() );
			$this->env->getMessenger()->noteNotice( UI_HTML_Exception_View::render( $e ) );
		}
	}
}
