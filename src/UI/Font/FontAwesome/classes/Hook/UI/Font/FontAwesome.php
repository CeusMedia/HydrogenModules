<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Font_FontAwesome extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		if( !$this->env instanceof WebEnvironment )
			return;

		$config	= $this->env->getConfig();
		$mc		= $config->getAll( 'module.ui_font_fontawesome.', TRUE );
		if( !$config->get( 'module.ui_font.active' ) )
			return;
		if( !$config->get( 'module.ui_font_fontawesome.active' ) )
			return;

		$page	= $this->env->getPage();
		$page->addBodyClass( 'uses-FontAwesome' );

		$atLeastVersion5 = version_compare( $mc->get( 'version' ), '5.0.0', '>=' );
		$atLeastVersion5 ? $this->loadVersion5( $this->env ) : $this->loadVersion4( $this->env );
	}

	/**
	 *	@param		WebEnvironment	$env
	 *	@param		string			$style
	 *	@return		void
	 */
	protected function addV5CdnResource( WebEnvironment $env, string $style = 'all' ): void
	{
		$config			= $env->getConfig()->getAll( 'module.ui_font_fontawesome.', TRUE );
		$urlTemplateCss	= 'https://%s.fontawesome.com/releases/v%s/css/%s.css';
		$urlTemplateJs	= 'https://%s.fontawesome.com/releases/v%s/js/%s.js';
		if( $config->get( 'v5.mode' ) === 'css+font' ){
			$env->getPage()->addHead( HtmlTag::create( 'link', NULL, [
				'href'			=> vsprintf( $urlTemplateCss, [
					$config->get( 'v5.license' ) === 'pro' ? 'pro' : 'use',
					$config->get( 'version' ),
					$style,
				] ),
				'rel'			=> 'stylesheet',
				'crossorigin'	=> 'anonymous',
			] ) );
		}
		if( $config->get( 'v5.mode' ) === 'js+svg' ){
			$env->getPage()->addHead( HtmlTag::create( 'script', '', [
				'src'			=> vsprintf( $urlTemplateJs, [
					$config->get( 'v5.license' ) === 'pro' ? 'pro' : 'use',
					$config->get( 'version' ),
					$style,
				] ),
				'defer'			=> 'defer',
				'crossorigin'	=> 'anonymous',
			] ) );
		}
	}

	/**
	 *	@param		WebEnvironment		$env
	 *	@return		void
	 */
	protected function loadVersion4( WebEnvironment $env ): void
	{
		$mc		= $env->getConfig()->getAll( 'module.ui_font_fontawesome.', TRUE );
		if( '4.7.0' === $mc->get( 'version' ) ){
			if( $mc->get( 'v4.cdn' ) ){
				$url	= 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
				$env->getPage()->css->theme->addUrl( $url );
			}
			else{
				$url	= 'FontAwesome/4.7.0/font-awesome.min.css';
				$env->getPage()->css->common->addUrl( $url );
			}
		}
	}

	/**
	 *	@param		WebEnvironment		$env
	 *	@return		void
	 */
	protected function loadVersion5( WebEnvironment $env ): void
	{
		$mc			= $env->getConfig()->getAll( 'module.ui_font_fontawesome.', TRUE );
		$license	= $mc->get( 'v5.license' );
		$styles		= $mc->getAll( 'v5.'.$license.'.', TRUE );
		if( $styles->get( 'all' ) )
			$this->addV5CdnResource( $env, 'all' );
		else{
			if( $styles->get( 'solid' ) )
				$this->addV5CdnResource( $env, 'solid' );
			if( $styles->get( 'regular' ) )
				$this->addV5CdnResource( $env, 'regular' );
			if( $styles->get( 'light' ) && 'pro' === $license )
				$this->addV5CdnResource( $env, 'light' );
			if( $styles->get( 'brand' ) )
				$this->addV5CdnResource( $env, 'brand' );
			$this->addV5CdnResource( $env, 'fontawesome' );
		}

		if( 1 || $mc->get( 'v5.shim' ) ){
			$this->addV5CdnResource( $env, 'v4-shims' );
		}
	}
}
