<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Layer extends Hook
{
	/**
	 *	@return		void
	 *	@todo		implement module main switch
	 */
	public function onPageApplyModules(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.js_layer.', TRUE );
		if( !$config->get( 'active' ) )
			return;

		$configSpeed		= $config->getAll( 'speed.', TRUE );
		$configButtonImage	= $config->getAll( 'button.image.', TRUE );

		$script	= '
Layer.init();
Layer.speedShow = '.$configSpeed->get( 'show' ).';
Layer.speedHide = '.$configSpeed->get( 'hide' ).';
Layer.buttonImageDownload = '.json_encode( $configButtonImage->get( 'download' ) ).';
Layer.buttonImageInfo = '.json_encode( $configButtonImage->get( 'info' ) ).';
Layer.labelButtonPrev = '.json_encode( $configButtonImage->get( 'prev.label' ) ).';
Layer.labelButtonNext = '.json_encode( $configButtonImage->get( 'next.label' ) ).';
Layer.labelButtonInfo = '.json_encode( $configButtonImage->get( 'info.label' ) ).';
Layer.labelButtonLoad = '.json_encode( $configButtonImage->get( 'download.label' ) ).';
';
		$this->context->js->addScriptOnReady( $script );
	}
}
