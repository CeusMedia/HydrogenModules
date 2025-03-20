<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Request extends Hook
{
	public function onAppDispatch(): void
	{
		/** @var ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;

		try{
			$data	= $this->collectData();
			if( $module->config['delay']->value ){
				$fileName	= $module->config['file']->value;
				$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
				error_log( json_encode( $data ).PHP_EOL, 3, $filePath );
			}
			else{
				$model	= new Model_Log_Request( $this->env );
				$model->add( $data );
			}
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
		}
	}

	/**
	 *	@return Entity_Log_Request
	 */
	protected function collectData(): Entity_Log_Request
	{
		return Entity_Log_Request::fromArray( [
			'url'		=> getenv( 'REQUEST_URI' ),
			'method'	=> getenv( 'REQUEST_METHOD' ),
			'ip'		=> getenv( 'REMOTE_ADDR' ),
			'path'		=> $this->env->getRequest()->getPath(),
			'request'	=> json_encode( $this->env->getRequest()->getAll() ),
			'session'	=>  json_encode( $this->env->getSession()->getAll() ),
			'cookie'	=>  json_encode( $this->env->has( 'cookie' ) ? $this->env->getCookie()->getAll() : [] ),
			'headers'	=> $this->env->getRequest()->getHeaders()->render(),
			'timestamp'	=> date( 'Y-m-d H:i:s' ),
		] );
	}
}