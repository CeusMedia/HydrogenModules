<?php
$page	= $env->getPage();

$navbarFixed	= TRUE;
$navMain		= '';
$navHeader		= '';
$navTop			= '';
$navFooter		= '';


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
	$navHeader	= $helper->render( 'header' );
	$navTop		= $helper->render( 'top' );
	$navFooter	= $helper->render( 'footer' );
}
else if( class_exists( 'View_Helper_Navigation' ) ){							//  fallback: outdated local renderer
	$path		= $this->env->getRequest()->get( '__path' );
	$helperNav	= new View_Helper_Navigation();
	$helperNav->setEnv( $this->env );
	$helperNav->setCurrent( $path ? $path : 'index' );
	$navMain	= $helperNav->render();
}
else{
	$links	= array();
	if( file_exists( 'config/pages.json' ) ){									//  fallback: pages but no renderer
		$isAuthenticated	= (bool) $env->getSession()->get( 'auth_user_id' );
		if( $env->getModules()->has( 'Resource_Authentication' ) ){
			$auth				= Logic_Authentication::getInstance( $env );
			$isAuthenticated	= $auth->isAuthenticated();
		}
		try{
			$scopes	= FS_File_JSON_Reader::load( 'config/pages.json' );
			foreach( $scopes->main as $mainPageId => $mainPage ){
				if( isset( $mainPage->disabled ) && $mainPage->disabled !== "no" )
					continue;
				if( isset( $mainPage->pages ) && $mainPage->pages )
					continue;
				$free		= !isset( $mainPage->access );
				$public		= !$free && $mainPage->access == "public";
				$outside	= !$free && !$isAuthenticated && $mainPage->access == "outside";
				$inside		= !$free && $isAuthenticated && $mainPage->access == "inside";
				$acl		= !$free && $mainPage->access == "acl" && $env->getAcl()->has( $mainPage->path );
				$mainPage->visible	= $free || $public || $outside || $inside || $acl;
				if( $mainPage->visible )
					$links[$mainPage->path]	= $mainPage->label;
			}
		}
		catch( Exception $e ){
			$messenger->noteFailure( 'Config file "pages.json" cannot be parsed: '.$e->getMessage().'.' );
		}
	}
	else if( isset( $words['links'] ) && $words['links'] ){						//  fallback: links from main words section, all public
		$links	= $words['links'];
	}
	$controller	= $this->env->getRequest()->get( '__controller' );
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


/*  --  TITLE & BRAND  --  */
$brand		= preg_replace( "/\(.*\)/", "", $words['main']['title'] );
$page->setTitle( $brand );
if( !empty( $words['main']['brand'] ) )
	$brand	= $words['main']['brand'];

$brand		= UI_HTML_Tag::create( 'a', $brand, array( 'href' => './', 'class' => 'brand' ) );
if( $view->hasContentFile( 'html/app.brand.html' ) )
	if( $brandHtml = $view->loadContentFile( 'html/app.brand.html' ) )			//  render brand, words from main.ini are assigned
		$brand		= $brandHtml;


/*  --  STATIC HEADER / FOOTER  --  */
$data	= array(
	'words'		=> $words,
	'navFooter'	=> $navFooter,
	'navHeader'	=> $navHeader,
	'navTop'	=> $navTop,
);
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
</div>';

$page->addBody( $header.$body.$footer );
return $page->build();
?>
