<?php

$navbarFixed	= TRUE;

/*  --  LANGUAGE SELECTOR  --  */
$languageSelector	= '';
if( $env->getModules()->has( 'UI_LanguageSelector' ) ){
	$helper				= new View_Helper_LanguageSelector( $env );
	$languageSelector	= $helper->render();
}

/*  --  NAVIGATION  --  */
if( $env->getModules()->has( 'UI_Navigation' ) ){
	$helper		= new View_Helper_Navigation( $env );
	$navMain	= $helper->render();
}
else if( class_exists( 'View_Helper_Navigation' ) ){							//  fallback: outdated local renderer
	$path		= $this->env->getRequest()->get( '__path' );
	$helperNav	= new View_Helper_Navigation();
	$helperNav->setEnv( $this->env );
	$helperNav->setCurrent( $path ? $path : 'index' );
	$navMain	= $helperNav->render();
}
else{
	if( file_exists( 'config/pages.json' ) ){									//  fallback: pages but no renderer
		$isAuthenticated	= (bool) $env->getSession()->get( 'userId' );
		if( $env->getModules()->has( 'Resource_Authentication' ) ){
			$auth				= Logic_Authentication::getInstance( $env );
			$isAuthenticated	= $auth->isAuthenticated();
		}
		$links	= array();
		try{
			$scopes	= FS_File_JSON_Reader::load( 'config/pages.json' );
			foreach( $scopes->main as $pageId => $page ){
				if( isset( $page->disabled ) && $page->disabled !== "no" )
					continue;
				if( isset( $page->pages ) && $page->pages )
					continue;
				$free		= !isset( $page->access );
				$public		= !$free && $page->access == "public";
				$outside	= !$free && !$isAuthenticated && $page->access == "outside";
				$inside		= !$free && $isAuthenticated && $page->access == "inside";
				$acl		= !$free && $page->access == "acl" && $env->getAcl()->has( $page->path );
				$page->visible	= $free || $public || $outside || $inside || $acl;
				if( $page->visible )
					$links[$page->path]	= $page->label;
			}
		}
		catch( Exception $e ){
			$messenger->noteFailure( 'Config file "pages.json" cannot be parsed: '.$e->getMessage().'.' );
		}
	}
	else if( isset( $words['links'] ) && $words['links'] ){						//  fallback: links from main words section, all public
		$links	= $words['links'];
	}
	$controller	= $this->env->getRequest()->get( 'controller' );
	$current	= CMF_Hydrogen_View_Helper_Navigation_SingleList::getCurrentKey( $links, $controller );

	$list	= array();
	foreach( $links as $key => $value ){
		$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => './'.$key ) );
		$class	= $key == $current ? "active" : NULL;
		$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
	}
	$navMain	= UI_HTML_Tag::create( 'ul', $list, array( "class" => "nav" ) );
}

/*  --  USER MESSAGES  --  */
if( $env->getModules()->has( 'UI_Helper_Messenger_Bootstrap' ) )
	$messages	= View_Helper_Messenger_Bootstrap::renderStatic( $env );
else
	$messages	= $messenger->buildMessages();

$hints	= class_exists( 'View_Helper_Hint' ) ? View_Helper_Hint::render( 'Tipp: ' ) : '';


/*  --  BRAND  --  */
$brand		= preg_replace( "/\(.*\)/", "", $words['main']['title'] );
if( !empty( $words['main']['brand'] ) )
	$brand	= $words['main']['brand'];
$brand		= UI_HTML_Tag::create( 'a', $brand, array( 'href' => './', 'class' => 'brand' ) );
if( $view->hasContentFile( 'html/app.brand.html' ) )
	if( $brandHtml = $view->loadContentFile( 'html/app.brand.html' ) )			//  render brand, words from main.ini are assigned
		$brand		= $brandHtml;


/*  --  STATIC HEADER  --  */
$header		= '';
if( $view->hasContentFile( 'html/app.header.html' ) )
	if( $headerHtml = $view->loadContentFile( 'html/app.header.html' ) )		//  render header, words from main.ini are assigned
		$header		= $headerHtml;

/*  --  STATIC FOOTER  --  */
$footer		= '';
if( $view->hasContentFile( 'html/app.footer.html' ) )
	if( $footerHtml = $view->loadContentFile( 'html/app.footer.html' ) )		//  render footer, words from main.ini are assigned
		$footer		= $footerHtml;


/*  --  MAIN STRUCTURE  --  */
$body	= '
<div id="layout-container">
	'.$header.'
	<div class="nav navbar '.( $navbarFixed ? 'navbar-fixed-top' : '' ).'">
		<div class="navbar-inner">
			<div class="container">
				'.$brand.'
				'.$navMain.'
				'.$languageSelector.'
			</div>
		</div>
	</div>
	<div class="container" style="margin-top: 50px">
		<div id="layout-messenger">'.$messages.'</div>
		<div id="layout-content">
			'.$hints.'
			'.$content.'
		</div>
	</div>
</div>
'.$footer.'
';

$page	= $env->getPage();
$page->addBody( $body );
$page->setTitle( trim( str_replace( "&nbsp;", " ", strip_tags( $brand ) ) ) );
return $page->build();
?>
