<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_MetaTags_MobileWebApp extends Hook
{
	public function onPageApplyModules(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_metatags_mobilewebapp.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$this->context->addMetaTag( 'name', 'mobile-web-app-capable', 'yes' );
		$this->context->addMetaTag( 'name', 'apple-mobile-web-app-capable', 'yes' );

/*		$options	= [];
		foreach( $moduleConfig->getAll() as $key => $value )
			if( strlen( trim( $value ) ) && $key !== 'active' )
				$options[]	= $key.'='.htmlentities( $value, ENT_QUOTES, 'UTF-8' );
		$context->addMetaTag( 'name', '...', join( ', ', $options ) );*/
	}
}
