<?php
/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Response\Sender as HttpResponseSender;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 */
class Controller_System_Load extends Controller
{
	protected Dictionary $config;
	protected int $cpuCores;
	protected Dictionary $moduleConfig;

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.server_system_load.', TRUE );			//  shortcut module configuration
		$this->cpuCores		= (int) $this->moduleConfig->get( 'cores' );							//  get number of cpu cores from module config
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'cpuCores', $this->cpuCores );
	}
}
