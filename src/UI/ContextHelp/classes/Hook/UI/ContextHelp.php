<?php

use CeusMedia\HydrogenFramework\Environment\Web as Environment;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFramework\View;
use PHPHtmlParser\Dom as DomParser;

class Hook_UI_ContextHelp extends Hook
{
	public function onPageApplyModules(): void
	{
		$payload = ['path' => 'app.contexthelp'];
		$this->env->getCaptain()->callHook( 'ContextHelp', 'register', $this->context, $payload );
		$this->context->js->addSCriptOnReady( 'ContextHelp.prepare()' );
	}

	public function onRegisterContextHelp(): void
	{
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

	public function registerFile( Environment $env, string $filePath ): void
	{
		$filePath	= 'html/'.$filePath.'.html';
		$view		= new View( $env );
		if( $view->hasContentFile( $filePath ) ){
			$list	= [];
			$html	= $view->loadContentFile( $filePath );
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
			if( $list ){
				$script	= 'ContextHelp.loadHelp('.json_encode( $list ).');';
				$env->getPage()->js->addScript( $script );
			}
		}
	}
}