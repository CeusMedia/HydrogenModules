<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var Messenger $messenger */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $content */

$page	= $env->getPage();

$navbarFixed	= TRUE;

/*  --  LANGUAGE SELECTOR  --  */
$languageSelector	= '';
if( $env->getModules()->has( 'UI_LanguageSelector' ) ){
	$helper				= new View_Helper_LanguageSelector( $env );
	$languageSelector	= $helper->render();
}

/*  --  NAVIGATION  --  */
$menus	= [
	'navMain'	=> '',
	'navHeader'	=> '',
	'navTop'	=> '',
	'navFooter'	=> '',
];
$env->getCaptain()->callHook('Navigation', 'renderMenus', $this, $menus);

/*  --  USER MESSAGES  --  */
if( $env->getModules()->has( 'UI_Helper_Messenger_Bootstrap' ) )
	$messages	= View_Helper_Messenger_Bootstrap::renderStatic( $env );
else
	$messages	= $messenger->buildMessages();

$hints	= class_exists( 'View_Helper_Hint' ) ? View_Helper_Hint::render( 'Tipp: ' ) : '';


/*  --  TITLE & BRAND  --  */
$brand		= preg_replace( "/\(.*\)/", "", $words['main']['title'] );
$page->setTitle( $brand );
if( !empty( $words['main']['brand'] ) )
	$brand	= $words['main']['brand'];

$brand		= HtmlTag::create( 'a', $brand, ['href' => './', 'class' => 'brand'] );
if( $view->hasContentFile( 'html/app.brand.html' ) )
	if( $brandHtml = $view->loadContentFile( 'html/app.brand.html' ) )			//  render brand, words from main.ini are assigned
		$brand		= $brandHtml;


/*  --  STATIC HEADER / FOOTER  --  */
$data	= [
	'words'		=> $words,
	'navFooter'	=> $menus['navFooter'],
	'navHeader'	=> $menus['navHeader'],
	'navTop'	=> $menus['navTop'],
];
$header		= '';
$footer		= '';
if( $view->hasContentFile( 'html/app.header.html' ) )
	$header = $view->loadContentFile( 'html/app.header.html', $data );		//  render header, words from main.ini are assigned
if( $view->hasContentFile( 'html/app.footer.html' ) )
	$footer = $view->loadContentFile( 'html/app.footer.html', $data );		//  render footer, words from main.ini are assigned


/*  --  MAIN STRUCTURE  --  */
$body	= '
<div id="layout-container">
	<div class="nav navbar '.( $navbarFixed ? 'navbar-fixed-top' : '' ).'">
		<div class="navbar-inner">
			<div class="container">
				'.$brand.'
				'.$menus['navMain'].'
				'.$languageSelector.'
			</div>
		</div>
	</div>
	<div class="container" style="margin-top: 50px">
		<div id="layout-messenger">'.$messages.'</div>
		<div id="layout-content">
			'.$hints.'
			'.$content.'
			<div id="auth-auto-logout-timer"></div>
		</div>
	</div>
</div>
<style>
#auth-auto-logout-timer:empty{
	display: none;
	}
#auth-auto-logout-timer:before{
	content: "Auto-Logout in ";
	}
#auth-auto-logout-timer{
	border-top: 2px solid red;
	margin-top: 1em;
	padding-top: 0.5em;
	}
</style>

';

return $page
	->addBody( $header.$body.$footer )
	->build();
