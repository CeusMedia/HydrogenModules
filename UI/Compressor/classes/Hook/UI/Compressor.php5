<?php
class Hook_UI_Compressor extends CMF_Hydrogen_Hook
{
	public static function onApplyModules( CMF_Hydrogen_Environment $env, $module, $context, $payload = array() )
	{
		$config		= (object) $env->getConfig()->getAll( 'module.ui_compressor.' );
		$pathCache  = $env->getConfig()->get( 'path.cache' );
		$page		= $env->getPage();

		$page->js->setPrefix( $config->jsPrefix );
		$page->js->setSuffix( $config->jsSuffix );
		$page->js->setCachePath( $config->jsCachePath ? $config->jsCachePath : $pathCache );
		$page->js->setCompression( $config->jsMinify );

		$page->css->primer->setPrefix( $config->cssPrefix.'primer.' );
		$page->css->primer->setSuffix( $config->cssSuffix );
		$page->css->primer->setCachePath( $config->cssCachePath ? $config->cssCachePath : $pathCache );
		$page->css->primer->setCompression( $config->cssMinify );

		$page->css->common->setPrefix( $config->cssPrefix.'common.' );
		$page->css->common->setSuffix( $config->cssSuffix );
		$page->css->common->setCachePath( $config->cssCachePath ? $config->cssCachePath : $pathCache );
		$page->css->common->setCompression( $config->cssMinify );

		$page->css->theme->setPrefix( $config->cssPrefix.$env->getConfig()->get( 'layout.theme' ).'.' );
		$page->css->theme->setSuffix( $config->cssSuffix );
		$page->css->theme->setCachePath( $config->cssCachePath ? $config->cssCachePath : $pathCache );
		$page->css->theme->setCompression( $config->cssMinify );

		$page->setPackaging( FALSE, $config->cssMinify );
	}
}
