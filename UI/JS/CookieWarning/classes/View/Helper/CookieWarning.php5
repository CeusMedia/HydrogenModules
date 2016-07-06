<?php
class View_Helper_CookieWarning{

	static public function ___onPageBuild( $env, $context, $module, $data ){
		$config		= $env->getConfig();
		$options	= $config->getAll( 'module.ui_js_cookiewarning.', TRUE );
		if( $options->get( 'enabled' ) ){
			$cookie	= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
			if( !$cookie->has( 'acceptCookies' ) ){
				$words		= $env->getLanguage()->getWords( 'cookiewarning' );
				$text		= $words['warning']['label'];
				$buttons	= array();
				$buttons[]	= UI_HTML_Tag::create( 'button', $words['warning']['buttonAccept'], array(
					'class'		=> 'btn btn-mini not-btn-primary',
					'onclick'	=> 'acceptCookies();',
				) );
				if( $options->get( 'readMorePagePath' ) ){
					$buttons[]	= UI_HTML_Tag::create( 'a', $words['warning']['buttonReadMore'], array(
						'href'	=> './'.$options->get( 'readMorePagePath' ),
						'class'		=> 'btn btn-mini btn-info',
					) );
				}
				$buttons	= join( '&nbsp;', $buttons );
				$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );
				$content	= UI_HTML_Tag::create( 'div', $text.' &nbsp;&nbsp;'.$buttons, array(
					'id'	=> 'cookie-warning-inner'
				) );
				$bar		= UI_HTML_Tag::create( 'div', $content, array(
					'class'	=> $options->get( 'absolute' ) ? 'absolute '.$options->get( 'absolute.position' ) : 'static',
					'id'	=> 'cookie-warning',
				) );
				$script		= 'function acceptCookies(){Cookies.set("acceptCookies",true); $("#cookie-warning").slideUp()};';
				$env->getPage()->js->addScript( $script );
				$data->content	= $bar.$data->content;
			}
			else{
				if( $env->getRequest()->has( 'removeAcceptCookies' ) )
					$env->getPage()->js->addScriptOnReady( 'Cookies.remove("acceptCookies");' );
			}
			$env->getPage()->addThemeStyle( 'module.ui.js.cookiewarning.css' );
		}
	}
}
?>
