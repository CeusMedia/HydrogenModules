<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_System_Exception extends Hook
{
	/**
	 *	@return		void
	 *	@throws		DataMissingException	if given payload is carrying an exception (by key 'exception')
	 *	@throws		ReflectionException
	 */
	public function onAppException(): void
	{
		$env		= $this->env;
		$env->getCaptain()->callHook( 'Env', 'logException', $this->context, $this->payload );

		$request	= $env->getRequest();
		if( 'system/exception' === $request->get( '__controller' ) )
			return;

		$moduleConfig	= $env->getConfig()->getAll( 'module.server_system_exception.', TRUE );
		if( !$moduleConfig->get( 'module.server_system_exception.active' ) )
			return;

		$exception	= $this->payload['exception'] ?? new stdClass();
		if( !( $exception instanceof Throwable ) )
			throw DataMissingException::create( 'No exception given' )
				->setDescription( 'Given payload array is missing value of key \'exception\'' )
				->setSuggestion( 'Provide payload with key \'exception\' holding an exception instance' );

		$this->handleExceptionOnAjaxRequest( $exception );
		$this->handleException( $exception, $moduleConfig );
	}

	/**
	 * @return		string
	 * @throws		NotSupportedException		if set environment is neither a web not a console environment
	 */
	protected function getExceptionUrlFromRequest(): string
	{
		$env		= $this->env;
		$request	= $env->getRequest();
		if( $env instanceof WebEnvironment )
			return $request->getUrl();
		if( $env instanceof ConsoleEnvironment ){
			global $argv;
			return join( ' ', $argv );
		}
		throw NotSupportedException::create( 'Environment is neither Web not Console environment' )
			->setDescription( 'On retrieving request URL, no suitable strategy is available for the given environment' )
			->setSuggestion( 'Provide an environment instance inheriting from web or console environment' );
	}

	/**
	 *	@param		Throwable		$exception
	 *	@param		Dictionary		$moduleConfig
	 *	@return		void
	 */
	protected function handleException( Throwable $exception, Dictionary $moduleConfig ): void
	{
		switch( $moduleConfig->get( 'mode' ) ?: 'info' ){
			case 'info':
				$this->storeExceptionInSession( $exception );
				static::restart( $this->env, 'system/exception', 500 );
				break;
			case 'dev':
			case 'strict':
			default:
				HtmlExceptionPage::display( $exception );
				exit;
		}
	}

	/**
	 *	@param		Throwable		$throwable
	 *	@return		void
	 */
	protected function handleExceptionOnAjaxRequest( Throwable $throwable ): void
	{
		if( !( $this->env instanceof WebEnvironment ) )
			return;
		if( !$this->env->getRequest()->isAjax() )
			return;

		try{
			$body		= json_encode( [
				'status'	=> 'exception',
				'message'	=> $throwable->getMessage(),
				'code'		=> $throwable->getCode(),
				'file'		=> $throwable->getFile(),
				'line'		=> $throwable->getLine(),
				'trace'		=> $throwable->getTraceAsString(),
			], JSON_THROW_ON_ERROR );
		}
		catch( JsonException $e ){
			$this->env->getLog()->log( 'exception', $e->getMessage(), $this );
			return;
		}
		$this->env->getResponse()
			->setStatus( 500 )
			->setBody( $body )
			->send();
	}

	/**
	 *	Stores exception is session by keys
	 *	- exceptionRequest
	 *	- exceptionUrl
	 *	- exception
	 *		- message
	 *		- code
	 *		- file
	 *		- line
	 *		- trace
	 *	@param		Throwable		$exception
	 *	@return		void
	 */
	protected function storeExceptionInSession( Throwable $exception ): void
	{
		$session	= $this->env->getSession();
		$session->set( 'exception', serialize( (object) [
			'message'	=> $exception->getMessage(),
			'code'		=> $exception->getCode(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
			'trace'		=> $exception->getTraceAsString(),
		] ) );
		$session->set( 'exceptionRequest', $this->env->getRequest() );
		$session->set( 'exceptionUrl', $this->getExceptionUrlFromRequest() );
	}
}
