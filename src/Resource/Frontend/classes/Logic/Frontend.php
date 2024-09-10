<?php

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

	/**
	 * @param		Environment		$env
	 * @return		static
	 */
	public static function getInstance( Environment $env ): static
	{
		if( NULL === self::$instance )
			self::$instance	= new static( $env );
		return self::$instance;
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
		$list	= [];
		foreach( $this->config->getAll( 'app.' ) as $key => $value ){
			if( !$keys || in_array( $key, $keys ) )
				$list[$key]	= $value;
		}
		return $list;
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
		return new RemoteEnvironment( [
			'configFile'	=> $this->path.'config/config.ini',
			'pathApp' 		=> $this->path,
			'parentEnv'		=> $this->env,
		] );
	}

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
			'configFile'	=> $path.'config/config.ini',
			'pathApp' 		=> $path,
			'parentEnv'		=> $parentEnv,
		] ) );
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

	public function getModuleConfigValue( string $moduleId, string $key, bool $strict = FALSE )
	{
		$values	= $this->getModuleConfigValues( $moduleId, [$key], TRUE, $strict );
		return array_pop( $values );
	}

	public function getModuleConfigValues( string $moduleId, array $keys = [], bool $useFasterUncachedSolution = TRUE, bool $strict = TRUE ): array
	{
		$fileName	= $this->getPath( 'modules' ).$moduleId.'.xml';
		$list		= [];
		if( !file_exists( $fileName ) ){
			if( $strict )
				throw new OutOfBoundsException( 'Invalid module ID: '.$moduleId );
			return $list;
		}
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
			$lines	= explode( "\n", FileReader::load( $fileName ) );
			foreach( $lines as $nr => $line ){
				if( str_contains( $line, '<config ' ) ){
					$key	= preg_replace( '@^.+name="(.+)".+$@U', '\\1', $line );
					if( !$key || ( $keys && !in_array( $key, $keys ) ) )
						continue;
					if( str_ends_with( $line, '/>' ) ){
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
			if( empty( $this->installedModules[$moduleId]->config ) ){
				$module	= HydrogenModuleReader::load( $fileName, $moduleId );
				$this->installedModules[$moduleId]->config	= $module;
			}
			foreach( $this->installedModules[$moduleId]->config->config as $configKey => $configData )
				if( !$keys || in_array( $configKey, $keys ) )
					$list[$configKey]	= (string) $configData->value;
		}
		return $list;
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

	public function getPath( ?string $key = NULL ): string
	{
		if( !$key )
			return $this->path;
		if( array_key_exists( $key, $this->paths ) )
			return $this->path.$this->paths[$key];
		throw new OutOfBoundsException( 'Invalid path key: '.$key );
	}

	/**
	 *	Returns frontend URI.
	 *	Alias for getUrl();
	 *	@access		public
	 *	@return		string|NULL		Frontend URL
	 *	@deprecated	use getUrl instead
	 *	@todo		to be removed in 0.9
	 */
	public function getUri(): ?string
	{
		return $this->getUrl();
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

	//  --  PROTECTED  --  //

	protected function __clone()
	{
	}

	protected function __onInit(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_frontend.', TRUE );
		$this->setPath( $moduleConfig->get( 'path' ) );
	}

	public function hasModule( string $moduleId ): bool
	{
		return isset( $this->installedModules[$moduleId] );
	}

	public function setPath( string $path ): void
	{
		if( !file_exists( $path ) )
			throw new DomainException( 'Invalid frontend path' );
		$this->path		= $path;
		$this->detectConfig();
		$this->detectModules();
		$this->detectBaseUrl();
	}

	//  --  PROTECTED  --  //

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
	 *	@throws		RuntimeException				if URL is not defined
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

	protected function detectModules(): void
	{
		$this->installedModules	= [];
		$index	= new DirectoryIterator( $this->getPath( 'modules' ) );
		foreach( $index as $entry ){
			if( preg_match( '@^(.+)(\.xml)$@', $entry->getFilename() ) ){
				$key	= preg_replace( '@^(.+)(\.xml)$@', '\\1', $entry->getFilename() );
				$this->installedModules[$key]	= (object) array(
					'id'			=> $key,
					'configFile'	=> $entry->getPathname(),
					'config'		=> NULL,
				);
			}
		}
		ksort( $this->installedModules );
	}
}
