<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconSortable	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrows-v' ) );

$filterApp		= '';
if( count( $apps ) > 1 ){
	$optApp		= UI_HTML_Elements::Options( $apps, $app );
	$filterApp	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_app" class="mandatory">'.$words['filter']['labelApp'].'</label>
				<select name="app" id="input_app" class="span12" onchange="document.location.href=\'./manage/page/setApp/\'+this.value;">'.$optApp.'</select>
			</div>
		</div>';
}

//print_m( $tree );die;

$filterLanguage		= '';
if( count( $languages ) > 1 ){
	$optLanguage	= UI_HTML_Elements::Options( array_combine( $languages, $languages ), $language );
	$filterLanguage	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_language" class="mandatory">'.$words['filter']['labelLanguage'].'</label>
				<select name="page_language" id="input_page_language" class="span12" onchange="document.location.href=\'./manage/page/setLanguage/\'+this.value;">'.$optLanguage.'</select>
			</div>
		</div>';
}
else
	$filterLanguage		= UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'hidden', 'name' => 'language', 'value' => $language ) );


$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );
$filterScope	= '
<div class="row-fluid">
	<div class="span12">
		<label for="input_page_scope">'.$words['filter']['labelScope'].'</label>
		<select class="span12" name="page_scope" id="input_page_scope" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
	</div>
</div>';


$urlAdd		= './manage/page/add';
if( !empty( $pageId ) && isset( $page ) )
	$urlAdd	.= "/".( $page->parentId > 0 ? $page->parentId : $pageId );
else if( !empty( $parentId ) && isset( $page ) )
	$urlAdd	.= "/".$parentId;

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Seite', array(
	'href'		=> $urlAdd,
	'class'		=> 'btn btn-small btn-success',
) );

$buttonSortable	= UI_HTML_Tag::create( 'button', $iconSortable, array(
	'type'		=> 'button',
	'id'		=> 'toggle-sortable',
	'onclick'	=> 'ModuleManagePages.PageEditor.toggleSortable()',
	'class'		=> 'btn btn-small',
) );

$currentId	= !empty( $pageId ) ? $pageId : $parentId;
$listPages	= $view->renderTree( $tree, $currentId );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$filterApp.'
		'.$filterLanguage.'
		'.$filterScope.'
		'.$listPages.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$buttonSortable.'
		</div>
	</div>
</div>';
