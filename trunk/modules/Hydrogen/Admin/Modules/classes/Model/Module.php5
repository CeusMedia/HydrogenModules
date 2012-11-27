<?php
class Model_Module{

	const TYPE_UNKNOWN	= 0;
	const TYPE_CUSTOM	= 1;
	const TYPE_COPY		= 2;
	const TYPE_LINK		= 3;
	const TYPE_SOURCE	= 4;

	protected $env;
	protected $pathRepos;
	protected $pathConfig;
	protected $modulesAll			= array();
	protected $modulesAvailable		= array();
	protected $source;
	protected $sources				= array();

	public function __construct( $env ){
		$this->env			= $env;
		$this->pathRepos	= $env->pathModules;
		$this->pathConfig	= $env->pathConfig.'modules/';

		$model	= new Model_ModuleSource( $env );
		foreach( $model->getAll() as $source )
			$this->sources[$source->id]	= $source;

//		$this->modulesAvailable	= $this->getAvailable();											//  @todo	???

		if( !file_exists( $this->pathRepos ) )
			throw new RuntimeException( 'Modules folder missing in "'.$this->pathRepos.'"', 1 );
		if( !file_exists( $this->pathConfig ) )
			if( !mkdir( $this->pathConfig ) )
				throw new RuntimeException( 'Modules configuration folder missing in "'.$this->pathConfig.'" and cannot be created', 2 );
	}

	/**
	 *	@todo		increase performance / scalability
	 */
	public function countAll( $filters = array() ){
		return count( $this->getAll( $filters ) );
	}

	public function get( $moduleId ){
		$all	= $this->getAll();
		if( array_key_exists( $moduleId, $all ) )
			return $all[$moduleId];
		return NULL;
	}

	public function getAll( $filters = array(), $limit = NULL, $offset = NULL ){
		if( !$this->modulesAll ){
			$this->modulesAll		= $this->modulesAvailable;
			foreach( $this->getInstalled( $this->modulesAvailable ) as $moduleId => $module ){
				if( !array_key_exists( $moduleId, $this->modulesAll ) ){
					$module->source	= 'Local';
					$this->modulesAll[$moduleId]	= $module;
				}
				else{
					$module->source	= $this->modulesAll[$moduleId]->source;
					if( $module->type != self::TYPE_LINK )
						$module->type	= self::TYPE_COPY;
				}
				switch( $module->type ){
					case self::TYPE_LINK:
						$module->versionAvailable	= $module->version;
						$module->versionInstalled	= $module->version;
						break;
					case self::TYPE_COPY:
						$module->versionInstalled	= $module->version;
						$module->versionAvailable	= $this->modulesAvailable[$moduleId]->version;
						break;
					case self::TYPE_CUSTOM:
						$module->version			= $module->versionInstalled;
						$module->versionAvailable	= NULL;
						break;
				}
				$this->modulesAll[$moduleId]	= $module;
			}
		}

		$modulesAll	= $this->modulesAll;
		if( $filters ){
			foreach( $filters as $filterKey => $filterValue ){
				foreach( $modulesAll as $moduleId => $module ){
					switch( $filterKey ){
						case 'types':
							if( is_array( $filterValue ) )
								if( !in_array( $module->type, $filterValue ) )
									unset( $modulesAll[$moduleId] );
							break;
						case 'categories':
							if( is_array( $filterValue ) )
								if( !in_array( $module->category, $filterValue ) )
									unset( $modulesAll[$moduleId] );
							break;
						case 'sources':
							if( is_array( $filterValue ) )
								if( !in_array( $module->source, $filterValue ) )
									unset( $modulesAll[$moduleId] );
							break;
						case 'query':
							if( !strlen( trim( $filterValue ) ) )
								continue;
							$text	= $module->title.$module->description;
							$parts	= explode( ' ', trim( $filterValue ) );
							$found	= FALSE;
							foreach( $parts as $part )
								if( substr_count( $text, $part ) )
									$found	= TRUE;	
							if( !$found )
								unset( $modulesAll[$moduleId] );
							break;
					}
				}
			}
		}
		ksort( $modulesAll );
		if( $limit || $offset )
			return array_slice( $modulesAll, (int) $offset, (int) $limit );
		return $modulesAll;
	}

	public function getAllNeededModules( $moduleId, $uninstalledOnly = FALSE, $list = array() ){
		$module	= $this->get( $moduleId );
		if( !$module )
			$list[$moduleId]	= 0;
		else{
			foreach( $module->relations->needs as $moduleName ){
				if( array_key_exists( $moduleName, $list ) )
					continue;
				$isInstalled	= $this->isInstalled( $moduleName );
				if( $uninstalledOnly && $isInstalled )
					continue;
				$list[$moduleName]	= $isInstalled;
				$needs	= $this->getAllNeededModules( $moduleName, $uninstalledOnly, $list );
				foreach( $needs as $id => $status )
					if( $id !== $moduleId)
						$list[$id]	= $status;
			}
		}
		return $list;
	}

	public function getAllSupportedModules( $moduleId, $uninstalledOnly = FALSE, $list = array() ){
		$module	= $this->get( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not available' );
		foreach( $module->relations->supports as $moduleName ){
			if( array_key_exists( $moduleName, $list ) )
				continue;
			$isInstalled	= $this->isInstalled( $moduleName );
			if( $uninstalledOnly && $isInstalled )
				continue;
			$list[$moduleName]	= $isInstalled;
			foreach( $this->getAllSupportedModules( $moduleName, $uninstalledOnly,$list ) as $id => $status )
				if( $id !== $moduleId)
					$list[$id]	= $status;
		}
		return $list;
	}

	public function getAvailable(){
		return $this->modulesAvailable;
	}

	public function getCategories(){
		$list	= array();
		foreach( $this->getAll() as $module )
			if( !empty( $module->category ) )
				$list[]	= $module->category;
		$list	= array_unique( $list );
		natcasesort( $list );
		$list	= array_values( $list );
		return $list;
	}

	public function getInstalled(){
		$list		= array();
		$modules	= $this->env->getRemote()->getModules();
		if( $modules ){
			foreach( $modules->getAll() as $id => $module ){
				$module->type	= self::TYPE_CUSTOM;
	#			if( is_link( $this->pathConfig.$id.'.xml' ) ){
	#				$module->type	= self::TYPE_LINK;
	#			}
				if( !empty( $this->modulesAvailable[$id] ) ){
					$module->icon	= $this->modulesAvailable[$id]->icon;
				}
				$list[$id]		= $module;
			}
			ksort( $list );
		}
		return $list;
	}

	/**
	 *	Reads and returns XML of locale module, in plain text or parsed to XML object structure.
	 *	@access		public
	 *	@param		string		$moduleId		Module ID
	 *	@param		boolean		$parse			Flag: parse XML string to object structure
	 *	@return		XML_Element
	 */
	public function getLocalModuleXml( $moduleId, $parse = FALSE ){
		$moduleFile	= $this->pathConfig.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new InvalidArgumentException( 'Module "'.$moduleId.'" is not installed' );
		if( $parse )
			return XML_ElementReader::readFile( $moduleFile );
		return File_Reader::load( $moduleFile );
	}

	public function getNeededModulesWithStatus( $moduleId ){										//  @todo	refactor to getNeededModuleIdsWithStatus
		$module	= $this->get( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not available' );
		$list		= array();
		$modules	= $this->getAll();
		foreach( $module->relations->needs as $relatedModuleId ){
			$status	= self::TYPE_UNKNOWN;
			if( array_key_exists( $relatedModuleId, $modules ) )
				$status	= self::TYPE_SOURCE;
			if( $status && $this->isInstalled( $relatedModuleId ) )
				$status	= self::TYPE_COPY;
			$list[$relatedModuleId]	= $status;
		}
		array_unique( $list );
		return $list;
	}

	public function getNotInstalled(){
		$localModules	= $this->getInstalled( $this->modulesAvailable );
		return array_diff_key( $this->modulesAvailable, $localModules );
	}

	public function getPath( $moduleId = NULL ){
		$module		= $this->get( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not available' );
		if( $moduleId ){
			if( !empty( $module->path ) )								// @todo: finish this hack usind $module->pathSource or ->pathCopy depending on module installation status
				return preg_replace( "/\/*$/", '/', $module->path );
			return $this->pathRepos.str_replace( '_', '/', $moduleId ).'/';
		}
		return $this->pathRepos;
	}

	public function getStatus( $moduleId ){
		$module		= $this->get( $moduleId );
		if( !$module )
			return self::TYPE_UNKNOWN;
		if( $this->isInstalled( $moduleId ) )
			return self::TYPE_COPY;
		if( array_key_exists( $moduleId, $this->getAll() ) )
			return self::TYPE_SOURCE;
	}

	public function getSupportedModulesWithStatus( $moduleId ){										//  @todo	refactor to getSupportedModuleIdsWithStatus
		$module	= $this->get( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Module "'.$moduleId.'" is not available' );
		$list		= array();
		$modules	= $this->getAll();
		foreach( $module->relations->supports as $relatedModuleId ){
			$status	= Model_Module::TYPE_UNKNOWN;
			if( array_key_exists( $relatedModuleId, $modules ) )
				$status	= Model_Module::TYPE_SOURCE;
			if( $status && $this->isInstalled( $relatedModuleId ) )
				$status	= Model_Module::TYPE_COPY;
			$list[$relatedModuleId]	= $status;
		}
		array_unique( $list );
		return $list;
	}

	/**
	 *	@deprecated
	 */
	public function isInstalled( $moduleId ){
		$list	= array();
		$index	= new File_RecursiveRegexFilter( $this->pathConfig, '/^\w+.xml$/' );
		foreach( $index as $entry ){
			$id	= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			if( $id == $moduleId )
				return TRUE;
		}
		return FALSE;
	}

	public function loadSources(){
		$list		= array();
		$results	= array();
		foreach( $this->sources as $source ){
			try{
				$results[$source->id]	= 0;
				if( $source->active ){
					$library	= new CMF_Hydrogen_Environment_Resource_Module_Library_Source( $this->env, $source );
					$results[$source->id]	= 1;
					foreach( $library->getAll() as $module ){
						$module->source			= $source->id;
						$module->type			= self::TYPE_SOURCE;
						$list[$module->id]		= $module;
						$results[$source->id]	= 2;
					}
				}
			}
			catch( Exception $e ){
				$results[$source->id]	= $e;
			}
			$this->env->clock->profiler->tick( 'Model_Module: Source: '.$source->title );
		}
		ksort( $list );
		$this->modulesAvailable	= $list;
#		print_m( $this->modulesAvailable['JS_jQuery'] );
#		die;
		return $results;
	}

	public function registerLocalFile( $moduleId, $type, $fileName ){								//  @todo: use getLocalModuleXml instead
		$moduleFile	= $this->pathConfig.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new InvalidArgumentException( 'Module "'.$moduleId.'" is not installed' );
		$xml	= XML_ElementReader::readFile( $moduleFile );
		$xml->files->addChild( $type, $fileName );													//  @todo: add attribute support
		File_Writer::save( $moduleFile, XML_DOM_Formater::format( $xml->asXml() ) );
	}

	public function setLocalModuleXml( $moduleId, $content ){
		if( $content instanceof SimpleXMLElement )
			$content	= XML_DOM_Formater::format( $content->asXML(), TRUE );
		if( !is_string( $content ) )
			throw new InvalidArgumentException( 'No valid XML string given' );
		$moduleFile	= $this->pathConfig.$moduleId.'.xml';
		if( !file_exists( $moduleFile ) )
			throw new InvalidArgumentException( 'Module "'.$moduleId.'" is not installed' );
		return File_Writer::save( $moduleFile, $content );
	}
}
?>
