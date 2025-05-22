<?php
declare(strict_types=1);

use CeusMedia\Common\Net\HTTP\Header\Field as HeaderField;
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
			$data	= $this->collectData();
			$model	= new Model_Log_Request( $this->env );
			$model->add( $data );
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e );
		}
	}

	/**
	 *	@return		Entity_Log_Request
	 */
	protected function collectData(): Entity_Log_Request
	{
		$ip			= NULL;
		$sessionId	= NULL;
		$method		= 'CLI';
		$url		= NULL;
		$cookie		= NULL;
		$session	= NULL;
		$headers	= NULL;

		if( !CeusMedia\Common\Env::isCli() ){
			$ip			= getenv( 'REMOTE_ADDR' );
			$sessionId	= $this->env->getSession()->getSessionID();
			$method		= getenv( 'REQUEST_METHOD' );
			$url		= substr( getenv( 'REQUEST_URI' ), 0, 255 );
			if( $this->env->has( 'cookie' ) )
				$cookie		= $this->env->get( 'cookie' )->getAll();
			$session	= $this->env->get( 'session' )->getAll();
			$headers	= array_map( static function( HeaderField $field ){
				return $field->toString();
			}, $this->env->getRequest()->getHeaders()->getFields() );
		}

		$date	= DateTime::createFromFormat( 'U.u', (string) microtime( TRUE ) );

		return Entity_Log_Request::fromArray( [
			'ip'		=> $ip,
			'sessionId'	=> $sessionId,
			'method'	=> $method,
			'url'		=> $url,
			'request'	=> json_encode( $this->env->getRequest()->getAll() ),
			'session'	=> json_encode( $session ),
			'cookie'	=> json_encode( $cookie ),
			'headers'	=> json_encode( $headers ),
			'referer'	=> $_SERVER['HTTP_REFERER'] ?? '',
			'userAgent'	=> $_SERVER['HTTP_USER_AGENT'] ?? '',
			'timestamp'	=> $date->format( 'Y-m-d H:i:s.u' ),
		] );
	}
}
