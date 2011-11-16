<?php
class Model_Module{

	const TYPE_CUSTOM	= 0;
	const TYPE_COPY		= 1;
	const TYPE_LINK		= 2;
	const TYPE_SOURCE	= 3;

	public function __construct( $env ){
		$this->env		= $env;
		$this->pathRepos	= './modules/';
		$this->pathConfig	= 'config/modules/';
		$this->cache		= array();
	}

	public function getAll(){
		$globalModules	= $this->getAvailable();
		$localModules	= $this->getInstalled( $globalModules );
		$list			= $globalModules;
		foreach( $localModules as $moduleId => $module ){
			if( !array_key_exists( $moduleId, $list ) )
				$list[$moduleId]	= $module;
			switch( $module->type ){
				case self::TYPE_LINK:
					$list[$moduleId]->version			= $module->versionAvailable;
					$list[$moduleId]->versionInstalled	= $module->versionAvailable;
					break;
				case self::TYPE_COPY:
					$list[$moduleId]->versionInstalled	= $module->version;
					$list[$moduleId]->versionAvailable	= $globalModules[$moduleId]->version;
					break;
				case self::TYPE_CUSTOM:
					break;
			}
		}
		ksort( $list );
		return $list;
	}

	public function get( $moduleId ){
		$all	= $this->getAll();
		if( array_key_exists( $moduleId, $all ) )
			return $all[$moduleId];
		return NULL;
	}

	public function getPath( $moduleId = NULL ){
		if( $moduleId )
			return $this->pathRepos.str_replace( '_', '/', $moduleId ).'/';
		return $this->pathRepos;
	}
	
	
	
	public function getInstalled( $globalModules ){
		$list	= array();
		$index	= new File_RecursiveRegexFilter( $this->pathConfig, '/^\w+.xml$/' );
		foreach( $index as $entry )
		{
			$id	= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			try{
				$type	= self::TYPE_CUSTOM;
				if( array_key_exists( $id, $globalModules ) ){
					if( is_link( 'config/modules/'.$id.'.xml' ) ){
						$list[$id]			= $globalModules[$id];
						$list[$id]->type	= self::TYPE_LINK;
						continue;
					}
					$type	= self::TYPE_COPY;
				}
				$list[$id]			= $this->readXml( $entry->getPathname() );
				$list[$id]->type	= $type;
				$list[$id]->versionInstalled	= $list[$id]->version;
				$list[$id]->id		= $id;
			}
			catch( Exception $e ){
				$this->env->messenger->noteFailure( 'XML of Module "'.$id.'" is broken.' );
			}

		}
		ksort( $list );
		return $list;
	}
	public function getAvailable(){
		if( $this->cache )
			return $this->cache;
		$list	= array();
		$index	= new File_RecursiveNameFilter( $this->pathRepos, 'module.xml' );
		foreach( $index as $entry ){
			$id		= preg_replace( '@^'.$this->pathRepos.'@', '', $entry->getPath() );
			$id		= str_replace( '/', '_', $id );
			try{
				$obj	= $this->readXml( $entry->getPathname() );
				$obj->path	= $entry->getPath();
				$obj->file	= $entry->getPathname();
				$obj->type	= self::TYPE_SOURCE;
				$obj->id	= $id;
				$obj->versionAvailable	= $obj->version;
				$list[$id]	= $obj;
			}
			catch( Exception $e ){
				$this->env->messenger->noteFailure( 'a: XML of Module "'.$id.'" is broken.' );
			}
		}
		$this->cache	= $list;
		ksort( $list );
		return $list;
	}
	public function getNotInstalled(){
		$globalModules	= $this->getAvailable();
		$localModules	= $this->getInstalled( $globalModules );
		return array_diff_key( $globalModules, $localModules );
	}

	public function install( $moduleId ){
	}

	public function uninstall( $moduleId ){
	}

	protected function readXml( $fileName ){
		$xml	= @XML_ElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->title				= (string) $xml->title;
		$obj->description		= (string) $xml->description;
		$obj->files				= new stdClass();
		$obj->files->classes	= array();
		$obj->files->locales	= array();
		$obj->files->templates	= array();
		$obj->files->styles		= array();
		$obj->files->scripts	= array();
		$obj->files->images		= array();
		$obj->config			= array();
		$obj->version			= (string) $xml->version;
		$obj->versionAvailable	= NULL;
		$obj->versionInstalled	= NULL;
		$obj->sql				= array();
		foreach( $xml->files->class as $link )
			$obj->files->classes[]	= (string) $link;
		foreach( $xml->files->locale as $link )
			$obj->files->locales[]	= (string) $link;
		foreach( $xml->files->template as $link )
			$obj->files->templates[]	= (string) $link;
		foreach( $xml->files->style as $link )
			$obj->files->styles[]	= (string) $link;
		foreach( $xml->files->script as $link )
			$obj->files->scripts[]	= (string) $link;
		foreach( $xml->files->image as $link )
			$obj->files->images[]	= (string) $link;
		foreach( $xml->config as $pair )
			$obj->config[$pair->getAttribute( 'name' )]	= (string) $pair;
		foreach( $xml->sql as $sql )
			$obj->sql[$sql->getAttribute( 'on' )]	= (string) $sql;
		return $obj;
	}
}
?>
