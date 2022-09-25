<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$filter	= $view->loadTemplateFile( 'manage/user/index.filter.php' );
$list	= $view->loadTemplateFile( 'manage/user/index.list.php' );

$heading	= '';
if( !empty( $words['index']['heading'] ) )
	$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/user/' ) );

return $textIndexTop.'
<div>
	'.$heading.'
	<div class="bs2-row-fluid bs3-row bs4-row">
		<div class="bs2-span3 bs3-col-md-3 bs4-col-md-3">
			'.$filter.'
		</div>
		<div class="bs2-span9 bs3-col-md-9 bs4-col-md-9">
			'.$list.'
		</div>
	</div>
</div>
'.$textIndexBottom;


?>
