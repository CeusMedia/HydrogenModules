<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Bootstrap_Datepicker extends Hook
{
	public function onPageApplyModules(): void
	{
		$config			= $this->env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_bootstrap_datepicker.', TRUE );
		if( $moduleConfig->get( 'active' ) ){
			$page		= $this->env->getPage();
			$minified	= $moduleConfig->get( 'minified' );
			$pathJs		= $config->get( 'path.scripts' );
			$pathCss	= $config->get( 'path.themes' ).'/'. $config->get( 'theme.custom' ).'/css/';
			$bsVersion	= 2;

			if( $minified ){
				$css	= $bsVersion > 2 ? 'bootstrap-datepicker3.min.css' : 'bootstrap-datepicker.min.css';
				$page->css->theme->addUrl( $pathCss.$css );
				$page->js->addUrl( $pathJs.'bootstrap-datepicker.min.js' );
			}
			else{
				$css	= $bsVersion > 2 ? 'bootstrap-datepicker3.css' : 'bootstrap-datepicker.css';
				$page->css->theme->addUrl( $pathCss.$css );
				$page->js->addUrl( $pathJs.'bootstrap-datepicker.js' );
			}

			$language	= $this->env->getLanguage()->getLanguage();
			if( $language != "en" ){
				$page->js->addUrl( $pathJs.'locales/bootstrap-datepicker.'.$language.'.min.js' );
			}
			$this->context->addBodyClass( 'uses-bootstrap-datepicker' );

			if( $moduleConfig->get( 'auto' ) ){
				$script	= 'jQuery("'.$moduleConfig->get( 'auto.selector' ).'").datepicker({language: "'.$language.'"});';
				$page->js->addScriptOnReady( $script );
			}
		}

	}
}