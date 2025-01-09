<?php

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class Controller_Ajax_Manage_Content_Style extends AjaxController
{
	protected HttpRequest $request;
	protected string $pathCss;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function saveContent(): void
	{
		$file		= $this->request->get( 'file' );
		$content	= $this->request->get( 'content' );

		if( !file_exists( $this->pathCss.$file ) )
			$this->respondError( 404, 'File not existing', 404 );

		try{
			$result	= FileWriter::save( $this->pathCss.$file, $content );
			$this->respondData( $result );
		}
		catch( Exception $e ){
			$this->respondError( 500, $e->getMessage(), 500 );
		}
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			/** @var ModuleDefinition $module */
			$module	= $this->env->getModules()->get( 'Resource_Frontend' );
			if( $module->isActive && './' !== ( $module->config['path']->value ?? '' ) ){
				$frontend		= Logic_Frontend::getInstance( $this->env );
				$basePath		= $frontend->getPath( 'themes' );
				$theme			= $frontend->getConfigValue( 'layout.theme' );
				$this->pathCss	= $basePath.$theme.'/css/';
				$this->uriCss	= $frontend->getUri().$basePath.$theme.'/css/';
				return;
			}
		}

		$basePath			= $this->env->getPath( 'themes' );
		$theme				= $this->env->getConfig()->get( 'layout.theme' );
		$this->pathCss		= $basePath.$theme.'/css/';
		$this->uriCss		= $this->env->uri.$basePath.$theme.'/css/';
	}
}
