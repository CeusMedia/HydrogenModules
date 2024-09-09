<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_MetaTags_Viewport extends Hook
{
	/**
	 */
	public function onPageApplyModules(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_metatags_viewport.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;
		$options		= [];
		foreach( $moduleConfig->getAll() as $key => $value )
			if( strlen( trim( $value ) ) )
				if( $key !== 'active' )
					$options[]	= $key.'='.htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$this->context->addMetaTag( 'name', 'viewport', join( ', ', $options ) );
	}
}
