<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Event extends View
{
	public function calendar()
	{
	}

	public function index()
	{
	}

	public function map()
	{
		$script     = 'if($(".UI_Map").length){applyMapMarkers(".UI_Map", ".map-point");}';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function view()
	{
	}

	protected function __onInit()
	{
		$pathJs     = $this->env->getConfig()->get( 'path.scripts' );
		$this->env->getPage()->js->addUrl( $pathJs.'module.info.event.js' );
		$this->env->getPage()->addThemeStyle( 'module.info.event.css' );
	}
}
