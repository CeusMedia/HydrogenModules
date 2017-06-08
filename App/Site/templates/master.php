<?php

$languageSelector	= '';
if( $env->getModules()->has( 'UI_LanguageSelector' ) ){
	$helper				= new View_Helper_LanguageSelector( $env );
	$languageSelector	= $helper->render();
}

$pathCDN	= "http://cdn.int1a.net/";

/*  --  NAVIGATION  --  */
if( $env->getModules()->has( 'UI_Navigation' ) ){
	$helper		= new View_Helper_Navigation( $env );
	$navMain	= $helper->render();
}
else if( class_exists( 'View_Helper_Navigation' ) ){
	$path		= $this->env->getRequest()->get( '__path' );
	$helperNav	= new View_Helper_Navigation();
	$helperNav->setEnv( $this->env );
	$helperNav->setCurrent( $path ? $path : 'index' );
	$navMain	= $helperNav->render();
}
else{
	$links	= array(
		''		=> "Start",
	);

	if( file_exists( 'config/pages.json' ) ){
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

$brand	= preg_replace( "/\(.*\)/", "", $words['main']['title'] );
if( !empty( $words['main']['brand'] ) )
	$brand	= $words['main']['brand'];
$brand	= UI_HTML_Tag::create( 'a', $brand, array( 'href' => './', 'class' => 'brand' ) );

/*  --  MAIN STRUCTURE  --  */
$body	= '
<div id="layout-container">
	<div class="nav navbar navbar-fixed-top">
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
[footer]
';

$env->getPage()->addBody( $body );
$html	= $env->getPage()->build();
$html	= preg_replace( "/\[(header|footer)\]/", "", $html );
return $html;
?>
