<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Navigation extends Hook
{
	/** @var array<string,string> $scopes */
	protected array $scopes	= [
		'navMain'	=> 'main',
		'navHeader'	=> 'header',
		'navTop'	=> 'top',
		'navFooter'	=> 'footer',
	];

	/**
	 * Renders all navigation menus.
	 * @return void
	 */
	public function onRenderNavigationMenus(): void
	{
		$helper		= new View_Helper_Navigation( $this->env );
		foreach( $this->scopes as $payloadKey => $scope )
			if( '' === ( $this->payload[$payloadKey] ?? '' ) )
				$this->payload[$payloadKey]		= $helper->render( $scope );
	}

	/**
	 *	...
	 *	@return		void
	 */
	public function setupSidebar(): void
	{
		/** @var WebEnvironment $env */
		$env	= $this->env;

		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$desktopRendererClass = $moduleConfig->get( 'render.desktop.class', '' );
		if( 'View_Helper_Navigation_Bootstrap_Sidebar' !== $desktopRendererClass )
			return;

		$pathJs	= $env->getConfig()->get( 'path.scripts' );
		$script	= '
		function _sidebarSetScrollTopBeforeReady(offset){
			let e = document.getElementById("nav-sidebar-list");
			e.style.overflowY = "auto";
			e.style.height = (window.innerHeight - e.offsetTop) + "px";
			if(offset > 0) e.scrollTop = offset;
		};';
		$env->getPage()
			->addHead( HtmlTag::create( 'script', $script ) )
			->js
				->addUrl( $pathJs.'module.ui.navigation.sidebar.js' )
				->addScriptOnReady('ModuleUiNavigation.Sidebar.init();');

	}
}
