<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Page $view */
/** @var array $words */
/** @var array $apps */
/** @var array $sources */
/** @var array $languages */
/** @var string $app */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconSortable	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrows-v'] );

$filterApp		= '';
if( count( $apps ) > 1 ){
	$optApp		= HtmlElements::Options( $apps, $app );
	$filterApp	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_app" class="mandatory">'.$words['filter']['labelApp'].'</label>
				<select name="app" id="input_app" class="span12" onchange="document.location.href=\'./manage/page/setApp/\'+this.value;">'.$optApp.'</select>
			</div>
		</div>';
}

$filterSource	= HtmlTag::create( 'input', NULL, ['type' => 'hidden', 'name' => 'source', 'value' => $source] );
if( count( $sources ) > 1 ){
	$sourceMap = [];
	$sourceLabels	= [
		'Database'	=> 'Database Pages',
		'Config'	=> 'Config Pages',
		'Modules'	=> 'Module Pages'
	];
	array_map(static function( $key ) use ( $sourceLabels, &$sourceMap ){
		$sourceMap[$key]    = $sourceLabels[$key];
	}, $sources );
	$optSource	= HtmlElements::Options( $sourceMap, $source );
	$filterSource	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_source" class="mandatory">Quelle './*$words['filter']['labelSource'].*/'</label>
				<select name="source" id="input_source" class="span12" onchange="document.location.href=\'./manage/page/setSource/\'+this.value;">'.$optSource.'</select>
			</div>
		</div>';
}


//print_m( $tree );die;

$filterLanguage		= HtmlTag::create( 'input', NULL, ['type' => 'hidden', 'name' => 'language', 'value' => $language] );
if( count( $languages ) > 1 ){
	$optLanguage	= HtmlElements::Options( array_combine( $languages, $languages ), $language );
	$filterLanguage	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_language" class="mandatory">'.$words['filter']['labelLanguage'].'</label>
				<select name="page_language" id="input_page_language" class="span12" onchange="document.location.href=\'./manage/page/setLanguage/\'+this.value;">'.$optLanguage.'</select>
			</div>
		</div>';
}

$optScope	= [];
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= HtmlElements::Options( $optScope, $scope );
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

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Seite', [
	'href'		=> $urlAdd,
	'class'		=> 'btn btn-small btn-success',
] );

$buttonSortable	= HtmlTag::create( 'button', $iconSortable, array(
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
		'.$filterSource.'
		'.$filterLanguage.'
		'.$filterScope.'
		'.$listPages.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$buttonSortable.'
		</div>
	</div>
</div>';
