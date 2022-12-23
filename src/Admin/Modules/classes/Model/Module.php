<?php

use CeusMedia\Common\FS\File\RecursiveNameFilter as RecursiveFileFinder;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;
use CeusMedia\Common\XML\ElementReader as XmlElementReader;
use CeusMedia\HydrogenFramework\Environment;

class Model_Module
{
	const TYPE_UNKNOWN	= 0;
	const TYPE_CUSTOM	= 1;
	const TYPE_COPY		= 2;
	const TYPE_LINK		= 3;
	const TYPE_SOURCE	= 4;

	const TYPES			= [
		self::TYPE_UNKNOWN,
		self::TYPE_CUSTOM,
		self::TYPE_COPY,
		self::TYPE_LINK,
		self::TYPE_SOURCE,
	];

	protected $env;

	protected $cache;

	protected $pathConfig;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->pathRepos	= $env->config->get( 'module.admin_modules.path' );
		$this->pathConfig	= 'config/modules/';
		$this->cache		= [];
	}

	public function getAll(): array
	{
		$globalModules	= $this->getAvailable();
		$localModules	= $this->getInstalled( $globalModules );
		$list			= $globalModules;
		foreach( $localModules as $moduleId => $module ){
			if( !array_key_exists( $moduleId, $list ) )
				$list[$moduleId]	= $module;
			else if( $module->type != self::TYPE_LINK )
				$module->type	= self::TYPE_COPY;
			switch( $module->type ){
				case self::TYPE_LINK:
					$module->versionAvailable	= $module->version;
					$module->versionInstalled	= $module->version;
					break;
				case self::TYPE_COPY:
					$module->versionInstalled	= $module->version;
					$module->versionAvailable	= $globalModules[$moduleId]->version;
					break;
				case self::TYPE_CUSTOM:
					$module->version			= $module->versionInstalled;
					$module->versionAvailable	= NULL;
					break;
			}
			$list[$moduleId]	= $module;
		}
		ksort( $list );
		return $list;
	}

	public function get( string $moduleId )
	{
		$all	= $this->getAll();
		if( array_key_exists( $moduleId, $all ) )
			return $all[$moduleId];
		return NULL;
	}

	public function getPath( string $moduleId = NULL ): string
	{
		if( $moduleId )
			return $this->pathRepos.str_replace( '_', '/', $moduleId ).'/';
		return $this->pathRepos;
	}

	public function getInstalled(): array
	{
		$list	= [];
		$index	= new RecursiveRegexFileIndex( $this->pathConfig, '/^\w+.xml$/' );
		foreach( $index as $entry )
		{
			$id	= preg_replace( '/\.xml$/i', '', $entry->getFilename() );
			try{
				$module	= $this->readXml( $entry->getPathname() );
			}
			catch( Exception $e ){
				$this->env->messenger->noteFailure( 'XML of Module "'.$id.'" is broken.' );
			}
			$module->type	= self::TYPE_CUSTOM;
			if( is_link( 'config/modules/'.$id.'.xml' ) ){
				$module->type	= self::TYPE_LINK;
			}
			$module->id		= $id;
			$module->versionInstalled	= $module->version;
			$list[$id]		= $module;
		}
		ksort( $list );
		return $list;
	}

	public function getAvailable(): array
	{
		if( $this->cache )
			return $this->cache;
		$list	= [];
		$index	= new RecursiveFileFinder( $this->pathRepos, 'module.xml' );
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

	public function getNotInstalled(): array
	{
		$globalModules	= $this->getAvailable();
		$localModules	= $this->getInstalled( $globalModules );
		return array_diff_key( $globalModules, $localModules );
	}

	public function install( string $moduleId )
	{
	}

	public function uninstall( string $moduleId )
	{
	}

	protected function readXml( string $fileName )
	{
		$xml	= XmlElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->title				= (string) $xml->title;
		$obj->description		= (string) $xml->description;
		$obj->files				= new stdClass();
		$obj->files->classes	= [];
		$obj->files->locales	= [];
		$obj->files->templates	= [];
		$obj->files->styles		= [];
		$obj->files->scripts	= [];
		$obj->files->images		= [];
		$obj->config			= [];
		$obj->version			= (string) $xml->version;
		$obj->versionAvailable	= NULL;
		$obj->versionInstalled	= NULL;
		$obj->sql				= [];
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
		foreach( $xml->sql as $sql ){
			$event	= $sql->getAttribute( 'on' );
			$type	= $sql->hasAttribute( 'type' ) ? $sql->getAttribute( 'type' ) : '*';
			foreach( explode( ',', $type ) as $type ){
				$key	= $event.'@'.$type;
				$obj->sql[$key]	= (string) $sql;
			}
		}
		return $obj;
	}
}
