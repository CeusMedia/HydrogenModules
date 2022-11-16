<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

//$panelAdd		= $view->loadTemplateFile( 'admin/mail/attachment/add.php' );
//$panelUpload	= $view->loadTemplateFile( 'admin/mail/attachment/upload.php' );
$panelFilter	= $view->loadTemplateFile( 'admin/mail/attachment/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'admin/mail/attachment/index.list.php' );

$w		= (object) $words['index'];

[$textTop, $textBottom] = array_values( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/attachment/' ) );

$tabs	= View_Admin_Mail_Attachment::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
'.$textBottom;
