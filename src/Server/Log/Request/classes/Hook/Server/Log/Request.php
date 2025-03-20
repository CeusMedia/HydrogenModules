<?php
declare(strict_types=1);

use CeusMedia\Common\Net\HTTP\Header\Section as HeaderSection;
use CeusMedia\Common\Net\HTTP\Header\Field as HeaderField;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Request extends Hook
{
	public function onEnvInit(): void
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
		$cookie	= [];
		if( $this->env->has( 'cookie' ) )
			$cookie	= $this->env->getCookie()->getAll();
		$headers	= [];
		/** @var HeaderSection $section */
		foreach( $this->env->getRequest()->getHeaders() as $section )
			/** @var HeaderField $field */
			foreach( $section as $field )
				$headers[] = $field->toString();
		return Entity_Log_Request::fromArray( [
			'ip'		=> getenv( 'REMOTE_ADDR' ),
			'method'	=> getenv( 'REQUEST_METHOD' ),
			'url'		=> substr( getenv( 'REQUEST_URI' ), 0, 255 ),
			'request'	=> json_encode( $this->env->getRequest()->getAll() ),
			'session'	=> json_encode( $this->env->getSession()->getAll() ),
			'cookie'	=> json_encode( $cookie ),
			'headers'	=> json_encode( $headers ),
			'timestamp'	=> date( 'Y-m-d H:i:s.u' ),
		] );
	}
}