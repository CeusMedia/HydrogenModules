<?php
class Model_Module{

	const TYPE_CUSTOM	= 0;
	const TYPE_COPY		= 1;
	const TYPE_LINK		= 2;
	const TYPE_SOURCE	= 3;

	public function __construct( $env ){
		$this->env		= $env;
		$this->pathRepos	= './modules/';
		$this->pathConfig	= $env->config->get( 'path.config' );
		$this->cache		= array();
	}

	public function getAll(){
		$list	= array_merge( $this->getAvailable(), $this->getInstalled() );
		ksort( $list );
		return $list;
	}

	public function get( $moduleId ){
		$all	= $this->getAll();
		if( array_key_exists( $moduleId, $all ) )
			return $all[$moduleId];
		return NULL;
	}

	public function getInstalled(){
		$available	= $this->getAvailable();
		$list		= array();
		$fileName	= $this->pathConfig.'modules.list';
		if( file_exists( $fileName ) )
			foreach( File_Reader::loadArray( $fileName ) as $moduleId ){
				if( !array_key_exists( $moduleId, $available ) ){
					$available[$moduleId]	= $this->readXml( 'config/modules/'.$moduleId.'.xml' );
					$available[$moduleId]->type	= self::TYPE_CUSTOM;
					$available[$moduleId]->versionInstalled	= $available[$moduleId]->version;
				}
				else
					$available[$moduleId]->type	= self::TYPE_LINK;
				$list[$moduleId]	= $available[$moduleId];
			}
		ksort( $list );
		return $list;
	}
	public function getAvailable(){
//		if( $this->cache )
//			return $this->cache;
		$list	= array();
		$index	= new File_RecursiveNameFilter( $this->pathRepos, 'module.xml' );
		foreach( $index as $entry ){
			$id		= basename( $entry->getPath() );
			$obj	= $this->readXml( $entry->getPathname() );
			$obj->path	= $entry->getPath();
			$obj->file	= $entry->getPathname();
			$obj->type	= self::TYPE_SOURCE;
			$obj->id	= $id;
			$obj->versionAvailable	= $obj->version;
			$list[$id]	= $obj;
		}
//		$this->cache	= $list;
		ksort( $list );
		return $list;
	}
	public function getNotInstalled(){
		return array_diff_key( $this->getAvailable(), $this->getInstalled() );
	}

	public function install( $moduleId ){
	}

	public function uninstall( $moduleId ){
	}

	protected function readXml( $fileName ){
		$xml	= XML_ElementReader::readFile( $fileName );
		$obj	= new stdClass();
		$obj->title				= (string) $xml->title;
		$obj->description		= (string) $xml->description;
		$obj->links				= new stdClass();
		$obj->links->classes	= array();
		$obj->links->locales	= array();
		$obj->links->templates	= array();
		$obj->links->styles		= array();
		$obj->links->scripts	= array();
		$obj->links->images		= array();
		$obj->config			= array();
		$obj->version			= (string) $xml->version;
		$obj->versionAvailable	= NULL;
		$obj->versionInstalled	= NULL;
		$obj->sql				= array();
		foreach( $xml->links->class as $link )
			$obj->links->classes[]	= (string) $link;
		foreach( $xml->links->locale as $link )
			$obj->links->locales[]	= (string) $link;
		foreach( $xml->links->template as $link )
			$obj->links->templates[]	= (string) $link;
		foreach( $xml->links->style as $link )
			$obj->links->styles[]	= (string) $link;
		foreach( $xml->links->script as $link )
			$obj->links->scripts[]	= (string) $link;
		foreach( $xml->links->image as $link )
			$obj->links->images[]	= (string) $link;
		foreach( $xml->config as $pair )
			$obj->config[$pair->getAttribute( 'name' )]	= (string) $pair;
		foreach( $xml->sql as $sql )
			$obj->sql[$sql->getAttribute( 'on' )]	= (string) $sql;
		return $obj;
	}
}
?>
