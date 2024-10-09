<?php

use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_CookieWarning extends Hook
{
	public static function onPageBuild( Environment $env, $context, $module, array & $payload )
	{
		$config		= $env->getConfig();
		$options	= $config->getAll( 'module.ui_js_cookiewarning.', TRUE );
		if( $options->get( 'active' ) && !$env->getRequest()->has( 'acceptCookies' ) ){
			$cookie	= new HttpCookie( parse_url( $env->url, PHP_URL_PATH ) );
			if( !$cookie->has( 'acceptCookies' ) ){
				$words		= $env->getLanguage()->getWords( 'cookiewarning' );
				$text		= $words['warning']['label'];
				$buttons	= [];
				$buttons[]	= HtmlTag::create( 'button', $words['warning']['buttonAccept'], [
					'class'		=> 'btn btn-mini not-btn-primary',
					'onclick'	=> 'acceptCookies();',
				] );
				if( $options->get( 'readMorePagePath' ) ){
					$buttons[]	= HtmlTag::create( 'a', $words['warning']['buttonReadMore'], [
						'href'	=> './'.$options->get( 'readMorePagePath' ),
						'class'	=> 'btn btn-mini btn-info',
					] );
				}
				$buttons	= join( '&nbsp;', $buttons );
				$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] );
				$content	= HtmlTag::create( 'div', $text.' &nbsp;&nbsp;'.$buttons, [
					'id'	=> 'cookie-warning-inner'
				] );
				$bar		= HtmlTag::create( 'div', $content, [
					'class'	=> $options->get( 'absolute' ) ? 'absolute '.$options->get( 'absolute.position' ) : 'static',
					'id'	=> 'cookie-warning',
				] );
				$script		= 'function acceptCookies(){Cookies.set("acceptCookies",true); $("#cookie-warning").slideUp()};';
				$env->getPage()->js->addScript( $script );
				$payload['content']	= $bar.$payload['content'];
			}
			else{
				if( $env->getRequest()->has( 'removeAcceptCookies' ) )
					$env->getPage()->js->addScriptOnReady( 'Cookies.remove("acceptCookies");' );
			}
			$env->getPage()->addThemeStyle( 'module.ui.js.cookiewarning.css' );
		}
	}
}
