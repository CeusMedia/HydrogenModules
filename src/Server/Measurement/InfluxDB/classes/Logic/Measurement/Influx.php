<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;
use InfluxDB2\Client as InfluxClient;
use InfluxDB2\WriteApi as InfluxWriteApi;
use InfluxDB2\Model\WritePrecision as InfluxClientWritePrecision;
use InfluxDB2\Point as InfluxPoint;

class Logic_Measurement_Influx extends SharedLogic
{
	protected ?InfluxClient $client		= NULL;
	protected ?InfluxWriteApi $writeApi	= NULL;
	protected string $bucket;
	protected string $org;

	public function __onInit(): void
	{
		parent::__onInit();

		/** @var ModuleDefinition $module */
		$module			= $this->env->getModules()->get( 'Server_Measurement_InfluxDB' );
		$moduleConfig	= $module->getConfigAsDictionary();
//		$moduleConfig	= $this->env->getConfig()->getAll( 'modules.info_contact.', TRUE );

		$this->bucket	= $moduleConfig->get( 'bucket' );
		$this->org		= $moduleConfig->get( 'org' );
		$connectionData	= [
			'url'		=> vsprintf( '%s://%s:%s', [
				'http',
				$moduleConfig->get( 'host', 'localhost' ),
				$moduleConfig->get( 'port', '8036' ),
			] ),
			'token'		=> $moduleConfig->get( 'token', '' ),
		];

		$this->client	= new InfluxClient( $connectionData );
	}

	public function write( $measurement, array $tags = [], array $fields = [] ): bool
	{
		if( NULL === $this->writeApi )
			$this->writeApi = $this->client->createWriteApi();

		$line	= $measurement;
		foreach( $tags as $key => $value )
			$line	.= ','.$key.'='.$value;

		$line	.= ' ';
		$list	= [];
		foreach( $fields as $key => $value )
			$list[]	= $key.'='.$value;
		$line	.= ' '.join( ',', $list );

		$this->writeApi->write( $line, InfluxClientWritePrecision::S, $this->bucket, $this->org);
		return TRUE;
	}
}