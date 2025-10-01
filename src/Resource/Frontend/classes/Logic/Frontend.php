<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection ALL */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Reader as HydrogenModuleReader;
use CeusMedia\HydrogenFramework\Logic;

/**
 *	@todo		remove singleton to have several frontend logics for different environments
 */
/** @phpstan-consistent-constructor */
class Logic_Frontend extends Logic
{
	protected static ?Logic_Frontend $instance	= NULL;

	protected Dictionary $config;
	protected Environment $env;
	protected array $installedModules			= [];
	protected array $paths						= [];
	protected array $defaultPaths				= [
		'config'	=> 'config/',
		'modules'	=> 'config/modules/',
		'contents'	=> 'contents/',
		'images'	=> 'contents/images/',
		'scripts'	=> 'javascripts/',
		'themes'	=> 'themes/',
		'logs'		=> 'logs/',
		'locales'	=> 'locales/',
		'templates'	=> 'templates/',
	];
	protected ?string $path						= NULL;
	protected ?string $url						= NULL;
	protected ?string $uri						= NULL;

	/**
	 *	@param		Environment		$parentEnv
	 *	@param		array			$options
	 *	@return		RemoteEnvironment
	 *	@throws		EnvironmentException
	 */
	public static function getRemoteEnv( Environment $parentEnv, array $options = [] ): RemoteEnvironment
	{
		$path	= $parentEnv->getConfig()->get( 'module.resource_frontend.path' );
		return new RemoteEnvironment( array_merge( $options, [
//			'configFile'	=> 'config/config.ini',
			'pathApp' 		=> $path,
			'uri' 			=> $path,
			'parentEnv'		=> $parentEnv,
		] ) );
	}

	public function getAppConfigValue( string $key )
	{
		$values	= $this->getAppConfigValues( [$key] );
		return end( $values );
	}

	public function getAppConfigValues( array $keys = [] ): array
	{
		if( is_string( $keys ) && strlen( trim( $keys ) ) )
			$keys	= [$keys];
		return array_filter( $this->config->getAll( 'app.' ), function( $key ) use ( $keys ){
			return !$keys || in_array( $key, $keys, TRUE );
		}, ARRAY_FILTER_USE_KEY );
	}

	public function getConfigValue( string $key )
	{
		return $this->config->get( $key );
	}

	public function getDefaultLanguage(): string
	{
		return trim( $this->getConfigValue( 'locale.default' ) );
	}

	/**
	 *		@return		RemoteEnvironment
	 *		@throws		EnvironmentException
	 */
	public function getEnv(): RemoteEnvironment
	{
		$uri	= $this->path;
		if( !str_starts_with( $uri, '/' ) && !str_starts_with( $uri, 'http' ) ){
			$uri	= realpath( $this->env->uri.'/'.$this->path ).'/';
		}
		return new RemoteEnvironment( [
			'configFile'	=> 'config.ini',
			'pathApp' 		=> $this->path,
			'uri'			=> $uri,
			'parentEnv'		=> $this->env,
		] );
	}

	public function getLanguages(): array
	{
		$data		= $this->config->getAll( 'locale.', TRUE );
		$list		= [trim( $data->get( 'default' ) )];
		foreach( explode( ',', $data->get( 'allowed' ) ) as $locale ){
			if( !in_array( $locale, $list ) ){
				$list[]	= trim( $locale );
			}
		}
		return $list;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$key
	 *	@param		bool		$strict
	 *	@return		int|float|string|NULL
	 *	@throws		OutOfBoundsException	if config key is not existing in module configuration
	 */
	public function getModuleConfigValue( string $moduleId, string $key, bool $strict = FALSE )
	{
		$values	= $this->getModuleConfigValues( $moduleId, [$key], TRUE, $strict );
		if( isset( $values[$key] ) )
			return $values[$key];
		if( !$strict )
			return NULL;
		return new OutOfBoundsException( 'Config key is invalid' );
	}

	/**
	 *	...
	 *	@param		string		$moduleId		Module ID
	 *	@param		array		$keys			List of config keys, empty list for all
	 *	@param		bool		$useFasterUncachedSolution
	 *	@param		bool		$strict
	 *	@return		array
	 */
	public function getModuleConfigValues( string $moduleId, array $keys = [], bool $useFasterUncachedSolution = false, bool $strict = TRUE ): array
	{
		if( $useFasterUncachedSolution )
			return $this->getModuleConfigValuesUsingXmlFileStrategy( $moduleId, $keys, $strict );
		return $this->getModuleConfigValuesUsingModuleDefinitionStrategy( $moduleId, $keys, $strict );
	}

	/**
	 *	@param		bool		$asDictionary
	 *	@return		Dictionary|string[]
	 */
	public function getModules( bool $asDictionary = FALSE ): array|Dictionary
	{
		if( $asDictionary )
			return new Dictionary( $this->installedModules );
		return array_keys( $this->installedModules );
	}

	/**
	 *	Returns (relative) path to frontend application.
	 *
	 *	Returns a configured (relative) path (within frontend application), identified by path key.
	 *	So, by default, path of 'images' will return '{FRONTEND_PATH}/contents/images/.
	 *	Path values can be defined in base config file.
	 *	Otherwise, default paths will be used.
	 *
	 * 	Returns
	 *	@param		string|NULL		$key
	 *	@return		string
	 */
	public function getPath( ?string $key = NULL ): string
	{
		if( !$key )
			return $this->path;
		if( array_key_exists( $key, $this->paths ) )
			return $this->path.$this->paths[$key];
		throw new OutOfBoundsException( 'Invalid path key: '.$key );
	}

	/**
	 *	Returns frontend URI, meaning an absolute file system path.
	 *
	 *	@access		public
	 *	@return		string|NULL		Frontend URL
	 */
	public function getUri(): ?string
	{
		return $this->uri;
	}

	/**
	 *	Returns frontend URL.
	 *	@access		public
	 *	@return		string|NULL		Frontend URL
	 */
	public function getUrl(): ?string
	{
		return $this->url;
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		bool
	 */
	public function hasModule( string $moduleId ): bool
	{
		return array_key_exists( $moduleId, $this->installedModules );
	}

	/**
	 *	@param		string		$path
	 *	@return		void
	 */
	public function setPath( string $path ): void
	{
		if( !file_exists( $path ) )
			throw new DomainException( 'Invalid frontend path' );
		$this->path		= $path;
		$this->uri		= realpath( $path ).'/';
		$this->detectConfig();
		$this->detectModules();
		$this->detectBaseUrl();
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __clone()
	{
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_frontend.', TRUE );
		$this->setPath( $moduleConfig->get( 'path' ) );
	}

	/**
	 *	@return		void
	 */
	protected function detectConfig(): void
	{
		$configFile		= $this->path.'config/config.ini';
		if( !file_exists( $configFile ) )
			throw new RuntimeException( 'No Hydrogen application found in: '.$this->path );
		$this->config	= new Dictionary( parse_ini_file( $configFile ) );
		$this->paths	= array_merge( $this->defaultPaths, $this->config->getAll( 'path.', !TRUE ) );
		unset( $this->paths['scripts.lib'] );
	}

	/**
	 *	Tries to resolve frontend URL.
	 *	@access		protected
	 *	@return		void
	 *	@throws		RuntimeException		if URL is not defined
	 */
	protected function detectBaseUrl(): void
	{
		if( $this->path === './' && $this->env->url )
			$this->url		= $this->env->url;
		else if( $this->getAppConfigValue( 'base.url' ) )
			$this->url	= $this->getAppConfigValue( 'base.url' );
		else if( $this->getAppConfigValue( 'baseHref' ) )											//  @todo remove in v1.0.0
			$this->url	= $this->getAppConfigValue( 'baseHref' );									//  @todo remove in v1.0.0
		else
			throw new RuntimeException( 'Frontend URL could not been detected' );
	}

	/**
	 *	@return		void
	 */
	protected function detectModules(): void
	{
		$this->installedModules	= [];
		$index	= new DirectoryIterator( $this->getPath( 'modules' ) );
		foreach( $index as $entry ){
			if( preg_match( '@^(.+)(\.xml)$@', $entry->getFilename() ) ){
				$key	= preg_replace( '@^(.+)(\.xml)$@', '\\1', $entry->getFilename() );
				$this->installedModules[$key]	= (object) [
					'id'			=> $key,
					'configFile'	=> $entry->getPathname(),
					'config'		=> NULL,
				];
			}
		}
		ksort( $this->installedModules );
	}

	/**
	 *	Get module config object using XML parser.
	 *	Strategy (2) for getModuleConfigValues.
	 *
	 *	Performance: slow, but stable
	 *	Stability:   stable
	 *	Use default: yes
	 *	Benefits:
	 *    - stable (using DOM via framework class)
	 *	  - handle empty nodes automatically
	 *	  - use cache for each module (good for future methods)
	 *	  - modern (more OOP)
	 *	Downsides:
	 *	  - >5x slower than version 1
	 *	  - more code to use
	 *	  - DOM use (needs to be valid XML)
	 *
	 *	@param		string		$moduleId
	 *	@param		array		$keys
	 *	@param		bool		$strict
	 *	@return		array
	 *	@throws		OutOfBoundsException		if module ID is invalid, no such module found
	 */
	protected function getModuleConfigValuesUsingModuleDefinitionStrategy( string $moduleId, array $keys = [], bool $strict = TRUE ): array
	{
		$fileName	= $this->getPath( 'modules' ).$moduleId.'.xml';
		if( !file_exists( $fileName ) ){
			if( !$strict )
				throw new OutOfBoundsException( 'Invalid module ID: '.$moduleId );
			return [];
		}
		if( empty( $this->installedModules[$moduleId]->config ) ){
			$module	= HydrogenModuleReader::load( $fileName, $moduleId );
			$this->installedModules[$moduleId]->config	= $module;
		}
		$list		= [];
		foreach( $this->installedModules[$moduleId]->config->config as $configKey => $configData )
			if( [] === $keys || in_array( $configKey, $keys, TRUE ) )
				$list[$configKey]	= (string) $configData->value;
		return $list;
	}

	/**
	 *	Get config pairs using regular expressions.
	 *	Strategy (1) for getModuleConfigValues.
	 *
	 *	Performance: fast, but maybe unstable
	 *	Use default: no
	 *	Benefits:
	 *	  - speed (>5x faster than version 1)
	 *	  - minimal code usage
	 *	  - no valid XML needed
	 *	Downsides:
	 *	  - maybe unstable (using regexp)
	 *	  - must handle empty nodes
	 *	  - not OOP
	 *
	 *	@param		string		$moduleId
	 *	@param		array		$keys
	 *	@param		bool		$strict
	 *	@return		array
	 *	@throws		RuntimeException		if module is not existing
	 */
	protected function getModuleConfigValuesUsingXmlFileStrategy( string $moduleId, array $keys = [], bool $strict = TRUE ): array
	{
		$list		= [];
		$fileName	= $this->getPath( 'modules' ).$moduleId.'.xml';
		$lines		= explode( "\n", FileReader::load( $fileName ) );
		foreach( $lines as $nr => $line ){
			if( !str_contains( $line, '<config ' ) )
				continue;
			$key	= preg_replace( '@^.+name="(.+)".+$@U', '\\1', $line );
			if( [] !== $key && !in_array( $key, $keys, TRUE ) )
				continue;
			if( str_ends_with( $line, '/>' ) ){
				$list[$key]	= NULL;
				continue;
			}
			$list[$key]	= preg_replace( '@^.+>(.*)</.+$@', '\\1', $line );
		}
		return $list;
	}
}
