<?php

use CeusMedia\Common\UI\HTML\Exception\View as ExceptionView;
use CeusMedia\Common\UI\HTML\PageFrame as HtmlPage;
use CeusMedia\Common\UI\HTML\JQuery as JQuery;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\TemplateEngine\Template as Template;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Exception $e */

//  --  Basic HTML Page Setup  --  //

$page	= new HtmlPage();
$page->setDocType( 'HTML_5' );
$page->addJavaScript( '//cdn.ceusmedia.de/js/jquery/1.10.2.min.js' );
$page->addJavaScript( '//cdn.ceusmedia.de/js/jquery/cmExceptionView/0.2.js' );
$page->addStylesheet( '//cdn.ceusmedia.de/js/jquery/cmExceptionView/0.2.css' );
$page->addStylesheet( '//cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( '//cdn.ceusmedia.de/css/bootstrap.min.css' );
$page->addStylesheet( 'themes/common/css/layout.css' );

$options	= ['foldTraces' => TRUE];
$script		= JQuery::buildPluginCall( 'cmExceptionView', 'dl.exception', $options );
$page->addHead( HtmlTag::create( 'script', $script ) );

try{
	$header		= Template::renderFile( 'locales/de/html/app.header.html', ['theme' => 'custom'] );
	$footer		= Template::renderFile( 'locales/de/html/app.footer.html', ['theme' => 'custom', 'time' => 1] );
}
catch( Throwable $innerException ){
	$env->getLog()->logException( $innerException );
	$header	= '';
	$footer	= '';
}
$view		= ExceptionView::render( $e );

if( file_exists( 'config/config.ini' ) && $config = @parse_ini_file( 'config/config.ini' ) ){
	$page->setBaseHref( $config['app.base.url'] );
	$page->setTitle( 'Fehler – '.$config['app.name'] );
}

//  --  Custom Content  --  //

$template	= '<h2>Error</h2>';
if( file_exists( 'templates/error.custom.php' ) )
	$template	= include_once 'templates/error.custom.php';

//  --  Content  --  //

$template	= '
<div id="error-page">
	'.$header.'
	<div id="layout-container">
		<div class="container" style="margin-top: 20px">
			<div id="layout-content">
				'.$template.'
				<div class="exception-view">
					'.$view.'
				</div>
			</div>
		</div>
	</div>
	'.$footer.'
</div>';
$page->addBody( $template );

print( $page->build( ['style' => 'margin: 1em'] ) );
exit;
