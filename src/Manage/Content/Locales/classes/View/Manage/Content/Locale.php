<?php
/**
 *	Content View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\View;

/**
 *	Content View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */
class View_Manage_Content_Locale extends View
{
	public function index()
	{
	}

	public function edit()
	{
	}

	protected function __onInit(): void
	{
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$page	= $this->env->getPage();
		$page->addCommonStyle( 'module.manage.content.locale.css' );
		$page->js->addUrl( $pathJs.'module.manage.content.locale.js' );
		$page->js->addScriptOnReady( 'ModuleManageContentLocale.init();' );
	}
}
