<?php
declare(strict_types=1);

use CeusMedia\Common\Env as CommonEnv;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;
use InfluxDB2\Client as InfluxClient;
use InfluxDB2\WriteApi as InfluxWriteApi;
use InfluxDB2\Model\WritePrecision as InfluxClientWritePrecision;

class Logic_Measurement_Influx extends SharedLogic
{
	protected ?InfluxClient $client		= NULL;
	protected ?InfluxWriteApi $writeApi	= NULL;
	protected string $bucket;
	protected string $org;
	protected array $tags		= [];

	public function __onInit(): void
	{
		parent::__onInit();

		/** @var ModuleDefinition $module */
		$module			= $this->env->getModules()->get( 'Server_Measurement_InfluxDB' );
		$moduleConfig	= $module->getConfigAsDictionary();
//		$moduleConfig	= $this->env->getConfig()->getAll( 'modules.info_contact.', TRUE );

		$this->bucket	= $moduleConfig->get( 'bucket' );
		$this->org		= $moduleConfig->get( 'org' );
		$host			= $moduleConfig->get( 'host', 'localhost' );
		$port			= $moduleConfig->get( 'port', '8086' );
		$verifySsl		= $moduleConfig->get( 'verifySSL', TRUE );
		$protocol		= match( (int) $port ){
			443		=> 'https',
			8086	=> 'http',
		};
		$connectionData	= [
			'url'		=> vsprintf( '%s://%s:%s', [$protocol, $host, $port] ),
			'token'		=> $moduleConfig->get( 'token', '' ),
			'verifySSL'	=> $verifySsl,
		];

		$this->client	= new InfluxClient( $connectionData );
		$this->setDefaultTags();
	}

	public function write( $measurement, array $tags = [], array $fields = [] ): bool
	{
		if( NULL === $this->writeApi )
			$this->writeApi = $this->client->createWriteApi();

		$line	= $measurement;
		foreach( array_merge( $this->tags, $tags ) as $key => $value )
			if( '' !== trim( (string) ( $value ?? '' ) ) )
				$line	.= ','.$key.'='.$value;

		$line	.= ' ';
		$list	= [];
		foreach( $fields as $key => $value )
			$list[]	= $key.'='.$value;
		$line	.= ' '.join( ',', $list );

		$this->writeApi->write( $line, InfluxClientWritePrecision::S, $this->bucket, $this->org );
		return TRUE;
	}

	/**
	 *	Collects a map of default tags.
	 *	Contains:
	 *		- host: Host name used by web application
	 *		- name: App name
	 *		- version: App version, if set in config.ini
	 *		- hydrogen: Framework version
	 *		- mode: Environment run mode (live,stage,test,dev,unknown)
	 * 		- method: CLI,GET,POST,PUT,DELETE,...
	 *
	 *	This map is used to be set on logic construction.
	 *	@return		array<string,?string>
	 */
	protected function detectDefaultTags(): array
	{
		$tags	= [
			'host'		=> parse_url( $this->env->url, PHP_URL_HOST ),
			'name'		=> $this->env->getConfig()->get( 'app.name' ),
			'version'	=> $this->env->getConfig()->get( 'app.version' ),
			'hydrogen'	=> $this->env->version,
			'mode'		=> match( $this->env->getMode() ){
				Environment::MODE_UNKNOWN	=> 'unknown',
				Environment::MODE_DEV		=> 'dev',
				Environment::MODE_TEST		=> 'test',
				Environment::MODE_STAGE		=> 'stage',
				Environment::MODE_LIVE		=> 'live',
			},
		];
		if( CommonEnv::isCli() )
			$tags['method']	= 'CLI';
		else if( $this->env instanceof WebEnvironment )
			$tags['method']	= $this->env->getRequest()->getMethod()->get();
		return $tags;
	}

	/**
	 *	Sets several tags to be attached to every measurement package.
	 *	@param		bool		$reset		Flag: clear tags, default: yes
	 *	@return		void
	 */
	protected function setDefaultTags( bool $reset = TRUE ): void
	{
		$defaultTags = $this->detectDefaultTags();
		if( $reset )
			$this->tags	= $defaultTags;
		else
			foreach( $defaultTags as $key => $value )
				$this->tags[$key]	= $value;
	}
}
