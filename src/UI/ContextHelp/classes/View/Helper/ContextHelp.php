<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Helper_ContextHelp
{
	public static function ___onRegisterContextHelp( Environment $env, $context, $module, $data = [] ){
		if( isset( $data['path'] ) ){
			self::registerFile( $env, $data['path'] );
			return;
		}
		$controller	= $env->getRequest()->get( '__controller' );
		$action		= $env->getRequest()->get( '__action' );
		self::registerFile( $env, $controller.'/contexthelp' );
		self::registerFile( $env, $controller.'/'.$action.'/contexthelp' );
	}

	static public function registerFile( Environment $env, $filePath ){
		$filePath	= 'html/'.$filePath.'.html';
		$view		= new View( $env );
		if( $view->hasContentFile( $filePath ) ){
			$list	= [];
			$html	= $view->loadContentFile( $filePath );
			$dom	= new \PHPHtmlParser\Dom();
			$dom->load( $html );
			$sections	= $dom->find( 'div.context-help' );
			foreach( $sections as $section ){
				$list[]	= array(
					$section->getAttribute('id'),
					$section->getAttribute('data-selector'),
					$section->innerHtml
				);
			}
			if( $list ){
				$script	= 'ContextHelp.loadHelp('.json_encode( $list ).');';
				$env->getPage()->js->addScript( $script );
			}
		}
	}
}
