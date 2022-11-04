<?php

use CeusMedia\HydrogenFramework\View;

class View_Info extends View
{
	public function index()
	{
		$site		= $this->getData( 'site' );
		$types		= explode( ',', $this->env->getConfig()->get( 'module.info.types' ) );
		foreach( $types as $type ){
			switch( strtolower( trim( $type ) ) ){
				case 'md':
				case 'markdown':
					if( $this->env->getModules()->has( 'UI_Markdown' ) ){
						$fileKey	= 'html/info/'.$site.".md";
						if( $this->hasContentFile( $fileKey ) ){
							$content	= $this->loadContentFile( $fileKey );
							$content	= View_Helper_Markdown::transformStatic( $this->env, $content );
							return $this->renderContent( $content );
						}
					}
					break;
				case 'html':
				default:
					$fileKey	= 'html/info/'.$site.".html";
					if( $this->hasContentFile( $fileKey ) )
						return $this->renderContent( $this->loadContentFile( $fileKey ) );
					break;
			}
		}
		$this->env->getResponse()->setStatus( 404 );
		return $this->loadContentFile( 'html/info/404.html' );										//  load content from file
	}

	protected function __onInit()
	{
		$this->env->page->addThemeStyle( 'module.info.css' );
	}
}
