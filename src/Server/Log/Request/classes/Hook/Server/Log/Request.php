<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Request extends Hook
{
	/**
	 *	@return		void
	 */
	public function onEnvInit(): void
	{
		/** @var ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;

		try{
			$logic	= Logic_Server_Log_Request::getInstance( $this->env );
			$logic->logCurrentRequest();
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
		}
	}

	public function onAfterAppSentResponse(): void
	{
		/** @var ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;

		try{
			Logic_Server_Log_Request::getInstance( $this->env )->logCurrentResponse(
				$this->payload['status'],
				$this->payload['mimeType'],
				$this->payload['content']
			);
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
		}
	}
}
