<?php
declare(strict_types=1);

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class Job_Server_Log_Request extends Job_Abstract
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@todo		implement export and removal
	 */
	public function archive(): void
	{
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;

		$age		= new DateInterval( $this->parameters->get( '--age', 'P1M' ) );
		$logic		= Logic_Server_Log_Request::getInstance( $this->env );
		$count		= $logic->countRequestsInInterval( NULL, $age );
		$this->out( 'Found '.$count.' entries for archive.' );
		// @todo implement export and removal
	}

	public function import(): void
	{
		/** @var ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;
		if( 'file' !== $module->config['saveTo']->value )
			return;
		$count	= Logic_Server_Log_Request::getInstance( $this->env )->importFromFileToDatabase();
		$this->out( 'Imported '.$count.' log entries.' );
	}
}
