<?php
class Logic_Frontend{

	static protected $instance;

	protected $config;
	protected $env;
	protected $modules	= array();
	protected $path;
	protected $paths	= array(
		'config'	=> 'config/',
		'modules'	=> 'config/modules/',
		'contents'	=> 'contents/',
		'images'	=> 'contents/images/',
		'scripts'	=> 'javascripts/',
		'themes'	=> 'themes/',
		'logs'		=> 'logs/',
		'locales'	=> 'locales/',
		'templates'	=> 'templates/',
	);

	protected function __clone(){}

	protected function __construct( $env, $path = NULL ){
		$this->env		= $env;
		$this->path		= $path;
		if( !$path )
			$this->path		= $env->getConfig()->get( 'module.resource_frontend.path' );
		$this->detectConfig();
		$this->detectModules();
		$this->detectBaseUri();
	}

	protected function detectConfig(){
		$configFile		= $this->path."config/config.ini";
		if( !file_exists( $configFile ) )
			throw new RuntimeException( 'No Hydrogen application found in: '.$this->path );
		$this->config	= new ADT_List_Dictionary( parse_ini_file( $configFile ) );
		$this->paths	= array_merge( $this->paths, $this->config->getAll( 'path.', !TRUE ) );
		unset( $this->paths['scripts.lib'] );
	}

	protected function detectBaseUri(){
		if( $this->config->get( 'app.baseHref' ) )
			$this->uri	= $this->config->get( 'app.baseHref' );
		else{
			$path	= dirname( getEnv( 'SCRIPT_NAME' ) ).'/'.$this->path;
			while( preg_match( "@/\.\./@", $path ) ){
				$parts	= explode( "/", $path );
				foreach( $parts as $nr => $part ){
					if( $part === ".." ){
						unset( $parts[$nr] );
						unset( $parts[$nr-1] );
						break;
					}
				}
				$path	= join( "/", $parts );
			}
			while( preg_match( "@/\./@", $path ) )
				$path	= preg_replace( "@/\./@", "/", $path );
			$path		= rtrim( $path, "\//" )."/";
			$this->uri	= "http://".getEnv( 'HTTP_HOST' ).$path;
		}
	}

	protected function detectModules(){
		$index	= new DirectoryIterator( $this->getPath( 'modules' ) );
		foreach( $index as $entry ){
			if( preg_match( "@^(.+)(\.xml)$@", $entry->getFilename() ) ){
				$key	= preg_replace( "@^(.+)(\.xml)$@", "\\1", $entry->getFilename() );
				$this->modules[$key]	= $entry->getPathname();
			}
		}
		ksort( $this->modules );
	}

	public function getAppConfigValues( $keys = array() ){
		if( is_string( $keys ) && strlen( trim( $keys ) ) )
			$keys	= array( $keys );
		$list	= array();
		foreach( $this->config->getAll( 'app.' ) as $key => $value ){
			if( !$keys || in_array( $key, $keys ) )
				$list[$key]	= $value;
		}
		return $list;
	}

	public function getConfigValue( $key ){
		return $this->config->get( $key );
	}

	static public function getInstance( $env, $path = NULL ){
		if( !self::$instance )
			self::$instance	= new self( $env, $path );
		return self::$instance;
	}

	public function getLanguages(){
		$data		= $this->config->getAll( 'locale.', TRUE );
		$list		= array( trim( $data->get( 'default' ) ) );
		foreach( explode( ",", $data->get( 'allowed' ) ) as $locale ){
			if( !in_array( $locale, $list ) ){
				$list[]	= trim( $locale );
			}
		}
		return $list;
	}

	public function getModuleConfigValue( $moduleId, $key ){
		$fileName	= $this->getPath( 'modules' ).$moduleId.".xml";
		if( !file_exists( $fileName ) )
			throw new OutOfBoundsException( 'Invalid module ID: '.$moduleId );
		$list	= array();
		$lines	= explode( "\n", File_Reader::load( $fileName ) );
		foreach( $lines as $nr => $line ){
			if( preg_match( "@<config @", $line ) ){
				$lineKey	= preg_replace( "@^.+name=\"(.+)\".+$@U", "\\1", $line );
				if( $key == $lineKey )
					return preg_replace( "@^.+>(.*)</.+$@", "\\1", $line );
			}
		}
		return NULL;
	}

	public function getModuleConfigValues( $moduleId, $keys = array() ){
		$fileName	= $this->getPath( 'modules' ).$moduleId.".xml";
		if( !file_exists( $fileName ) )
			throw new OutOfBoundsException( 'Invalid module ID: '.$moduleId );
		$list	= array();
		$lines	= explode( "\n", File_Reader::load( $fileName ) );
		foreach( $lines as $nr => $line ){
			if( preg_match( "@<config @", $line ) ){
//				print_m( $line );
				$key	= preg_replace( "@^.+name=\"(.+)\".+$@U", "\\1", $line );
				if( !$keys || in_array( $key, $keys ) )
					$list[$key]	= preg_replace( "@^.+>(.*)</.+$@", "\\1", $line );
			}
		}
		return $list;
	}

	public function getModules(){
		return array_keys( $this->modules );
	}

	public function getPath( $key = NULL ){
		if( !$key )
			return $this->path;
		if( array_key_exists( $key, $this->paths ) )
			return $this->path.$this->paths[$key];
		throw new OutOfBoundsException( 'Invalid path key: '.$key );
	}

	public function getUri(){
		return $this->uri;
	}

	public function hasModule( $moduleId ){
		$fileName	= $this->getPath( 'modules' ).$moduleId.".xml";
		return file_exists( $fileName );
	}
}
?>
