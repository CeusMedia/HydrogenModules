<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Event extends View
{
	public function calendar(): void
	{
	}

	public function index(): void
	{
	}

	public function map(): void
	{
		$script		= 'if($(".UI_Map").length){applyMapMarkers(".UI_Map", ".map-point");}';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addModuleFile( 'module.info.event.js' );
		$this->env->getPage()->addThemeStyle( 'module.info.event.css' );
	}
}
