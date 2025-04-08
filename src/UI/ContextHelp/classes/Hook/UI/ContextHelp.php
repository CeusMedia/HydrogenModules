<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;
use PHPHtmlParser\Dom as DomParser;

class Hook_UI_ContextHelp extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onPageApplyModules(): void
	{
		$payload = ['path' => 'app.contexthelp'];
		$this->env->getCaptain()->callHook( 'ContextHelp', 'register', $this->context, $payload );
		$this->context->js->addSCriptOnReady( 'ContextHelp.prepare()' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onRegisterContextHelp(): void
	{
		if( !$this->env instanceof WebEnvironment )
			return;
		$data	= $this->getPayload() ?? [];
		if( isset( $data['path'] ) ){
			$this->registerFile( $this->env, $data['path'] );
			return;
		}
		$controller	= $this->env->getRequest()->get( '__controller' );
		$action		= $this->env->getRequest()->get( '__action' );
		$this->registerFile( $this->env, $controller.'/contexthelp' );
		$this->registerFile( $this->env, $controller.'/'.$action.'/contexthelp' );
	}

	/**
	 *	@param		WebEnvironment	$env
	 *	@param		string			$filePath
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function registerFile( WebEnvironment $env, string $filePath ): void
	{
		$filePath	= 'html/'.$filePath.'.html';
		$view		= new View( $env );
		if( $view->hasContentFile( $filePath ) ){
			$list	= [];
			try{
//				$html	= $view->loadContentFile( $filePath );
				$dom	= new DomParser();
				$dom->loadFromFile( $filePath );
				$sections	= $dom->find( 'div.context-help' );
				foreach( $sections as $section ){
					$list[]	= [
						$section->getAttribute('id'),
						$section->getAttribute('data-selector'),
						$section->innerHtml
					];
				}
				if( [] !== $list ){
					$script	= 'ContextHelp.loadHelp('.json_encode( $list ).');';
					$env->getPage()->js->addScript( $script );
				}
			}
			catch( Throwable $e ){
				$this->env->getLog()->logException( $e );
				return;
			}
		}
	}
}