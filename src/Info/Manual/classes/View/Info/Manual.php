<?php

use CeusMedia\HydrogenFramework\View;
use CeusMedia\Markdown\Renderer\Html as MarkdownToHtmlRenderer;

class View_Info_Manual extends View
{
	public function add()
	{
	}

	public function category()
	{
	}

	public function edit()
	{
	}

	public function import()
	{
	}

	public function index()
	{
	}

	public function page()
	{
		$renderer	= $this->getData( 'renderer' );

		$script	= '
InfoManual.UI.Page.renderer = "'.$renderer.'";
InfoManual.UI.Page.init("#content-container", "#content-index");';
		$this->env->getPage()->js->addScriptOnReady( $script );

		if( $renderer === "server-inline" ){
			$content	= $this->getData( 'content' );
			$helper		= new View_Helper_Markdown( $this->env );
			$helper->setRenderer( MarkdownToHtmlRenderer::RENDERER_MICHELF );
			$this->addData( 'content', $helper->transform( $content ) );
		}
	}

	public function urlencode( string $name ): string
	{
		return urlencode( $name );
		return str_replace( "%2F", "/", rawurldecode( $name ) );
	}

	protected function __onInit()
	{
		$page	= $this->env->getPage();
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );

		$page->css->theme->addUrl( 'module.info.manual.css' );
		$page->js->addUrl( $pathJs.'Info.Manual.js' );
		$page->js->addScriptOnReady( 'InfoManual.UI.Filter.init();' );
	}
}
