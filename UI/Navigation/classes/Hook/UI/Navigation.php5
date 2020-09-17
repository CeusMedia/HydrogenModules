<?php
class Hook_UI_Navigation extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function setupSidebar( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.ui_navigation.', TRUE );
		$desktopRendererClass = $moduleConfig->get( 'render.desktop.class' );
		if( $desktopRendererClass === 'View_Helper_Navigation_Bootstrap_Sidebar' ){
			$pathJs	= $env->getConfig()->get( 'path.scripts' );
			$env->getPage()->js->addUrl( $pathJs.'module.ui.navigation.sidebar.js' );
			$env->getPage()->js->addScriptOnReady("ModuleUiNavigation.Sidebar.init();");
			$script	= '
			function _sidebarSetScrollTopBeforeReady(offset){
				var e = document.getElementById("nav-sidebar-list");
				e.style.overflowY = "auto";
				e.style.height = (window.innerHeight - e.offsetTop) + "px";
				if(offset > 0) e.scrollTop = offset;
			};';
			$env->getPage()->addHead( UI_HTML_Tag::create( 'script', $script ) );
		}
	}
}