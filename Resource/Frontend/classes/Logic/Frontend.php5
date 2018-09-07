<?php
use CMF_Hydrogen_Environment_Resource_Module_Reader as HydrogenModuleReader;
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
	protected $url;

	protected function __clone(){}

	protected function __construct( CMF_Hydrogen_Environment $env, $path = NULL ){
		$this->env		= $env;
		$this->path		= $path;
		$moduleConfig	= $env->getConfig()->getAll( 'module.resource_frontend.', TRUE );
		if( !$this->path )
			$this->path		= $moduleConfig->get( 'path' );
		$this->detectConfig();
		$this->detectModules();
		$this->detectBaseUrl();
	}

	protected function detectConfig(){
		$configFile		= $this->path.'config/config.ini';
		if( !file_exists( $configFile ) )
			throw new RuntimeException( 'No Hydrogen application found in: '.$this->path );
		$this->config	= new ADT_List_Dictionary( parse_ini_file( $configFile ) );
		$this->paths	= array_merge( $this->paths, $this->config->getAll( 'path.', !TRUE ) );
		unset( $this->paths['scripts.lib'] );
	}

	/**
	 *	Tries to resolves frontend URL.
 	 *	@access		protected
 	 *	@return		void
 	 *	@throws		RuntimeException				if URL is not defined
	 */
	protected function detectBaseUrl(){
		if( $this->env->url )
			$this->url		= $this->env->url;
		else if( $this->getAppConfigValue( 'base.url' ) )
			$this->url	= $this->getAppConfigValue( 'base.url' );
		else if( $this->getAppConfigValue( 'baseHref' ) )											//  @todo remove in v1.0.0
			$this->url	= $this->getAppConfigValue( 'baseHref' );									//  @todo remove in v1.0.0
		else
			throw new RuntimeException( 'Frontend URL could not been detected' );
	}

	protected function detectModules(){
		$index	= new DirectoryIterator( $this->getPath( 'modules' ) );
		foreach( $index as $entry ){
			if( preg_match( '@^(.+)(\.xml)$@', $entry->getFilename() ) ){
				$key	= preg_replace( '@^(.+)(\.xml)$@', '\\1', $entry->getFilename() );
				$this->modules[$key]	= (object) array(
					'id'			=> $key,
					'configFile'	=> $entry->getPathname(),
					'config'		=> NULL,
				);
			}
		}
		ksort( $this->modules );
	}

	public function getAppConfigValue( $key ){
		return array_pop( $this->getAppConfigValues( array( $key ) ) );
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

	public function getDefaultLanguage(){
		return trim( $this->getConfigValue( 'locale.default' ) );
	}

	public function getLanguages(){
		$data		= $this->config->getAll( 'locale.', TRUE );
		$list		= array( trim( $data->get( 'default' ) ) );
		foreach( explode( ',', $data->get( 'allowed' ) ) as $locale ){
			if( !in_array( $locale, $list ) ){
				$list[]	= trim( $locale );
			}
		}
		return $list;
	}

	public function getModuleConfigValue( $moduleId, $key ){
		return array_pop( $this->getModuleConfigValues( $moduleId, array( $key ) ) );
	}

	public function getModuleConfigValues( $moduleId, $keys = array(), $useFasterUncachedSolution = TRUE ){
		$fileName	= $this->getPath( 'modules' ).$moduleId.'.xml';
		if( !file_exists( $fileName ) )
			throw new OutOfBoundsException( 'Invalid module ID: '.$moduleId );

		$list	= array();

		if( $useFasterUncachedSolution ){
			//  version 1
			//  description: get config pairs using regular expressions
			//  performance: fast, but maybe unstable
			//  use default: no
			//  benefits:    - speed (>5x faster than version 1)
			//               - minimal code usage
			//               - no valid XML needed
			//  downsides:   - maybe unstable (using regexp)
			//               - must handle empty nodes
			//               - not OOP
			$lines	= explode( "\n", FS_File_Reader::load( $fileName ) );
			foreach( $lines as $nr => $line ){
				if( preg_match( '@<config @', $line ) ){
					$key	= preg_replace( '@^.+name="(.+)".+$@U', '\\1', $line );
					if( !$key || ( $keys && !in_array( $key, $keys ) ) )
						continue;
					if( preg_match( '@/>$@', $line ) ){
						$list[$key]	= NULL;
						continue;
					}
					$list[$key]	= preg_replace( '@^.+>(.*)</.+$@', '\\1', $line );
				}
			}
		}
		else{
			//  version 2
			//  description: get module config object using XML parser
			//  performance: slow, but stable
			//  stability:   stable
			//  use default: yes
			//  benefits:    - stable (using DOM via framework class)
			//               - handle empty nodes automatically
			//               - use cache for each module (good for future methods)
			//               - modern (more OOP)
			//  downsides:   - >5x slower than version 1
			//               - more code to use
			//               - DOM use (needs to be valid XML)
			if( empty( $this->modules[$moduleId]->config ) ){
				$module	= HydrogenModuleReader::load( $fileName, $moduleId );
				$this->modules[$moduleId]->config	= $module;
			}
			$list	= array();
			foreach( $this->modules[$moduleId]->config->config as $configKey => $configData )
				if( !$keys || in_array( $configKey, $keys ) )
					$list[$configKey]	= (string) $configData->value;
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

	static public function getRemoteEnv( $parentEnv, $options = array() ){
		$path		= $parentEnv->getConfig()->get( 'module.resource_frontend.path' );
		return new CMF_Hydrogen_Environment_Remote( array(
			'configFile'	=> $path.'config/config.ini',
			'pathApp' 		=> $path,
			'parentEnv'		=> $parentEnv,
		) );
	}

	/**
	 *	Returns frontend URI.
	 *	Alias for getUrl();
	 *	@access		public
	 *	@return		string		Frontend URL
	 *	@deprecated	use getUrl instead
	 *	@toto		to be removed in 0.4
	 */
	public function getUri(){
		return $this->getUrl();
	}

	/**
	 *	Returns frontend URL.
	 *	@access		public
	 *	@return		string		Frontend URL
	 */
	public function getUrl(){
		return $this->url;
	}

	public function hasModule( $moduleId ){
		return isset( $this->modules[$moduleId] );
	}
}
?>
