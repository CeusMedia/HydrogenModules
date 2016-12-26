<?php

$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );

$urlAdd		= './manage/page/add';
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
if( !empty( $pageId ) && isset( $page ) )
	$urlAdd	.= "/".( $page->parentId > 0 ? $page->parentId : $pageId );
else if( !empty( $parentId ) && isset( $page ) )
	$urlAdd	.= "/".$parentId;

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Seite', array(
	'href'		=> $urlAdd,
	'class'		=> 'btn btn-small btn-success',
) );

$currentId	= !empty( $pageId ) ? $pageId : $parentId;

$listPages	= $view->renderTree( $tree, $currentId );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<select class="span12" name="scope" id="input_scope" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
