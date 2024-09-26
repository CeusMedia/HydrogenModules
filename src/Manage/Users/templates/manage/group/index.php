<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Group $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var array<object> $groups */
/** @var bool $hasRightToAdd */
/** @var bool $hasRightToEdit */

$wf		= (object) $words['index'];

$heads	= [
	$wf->headTitle,
	$wf->headUsers,
	$wf->headAccess,
	$wf->headRegister
];

$rows	= [];
foreach( $groups as $nr => $group ){
	$labelGroup	= $group->title;
	if( $hasRightToEdit ){
		$labelGroup	= HtmlTag::create( 'a', $labelGroup, ['href' => './manage/group/edit/'.$group->groupId] );
	}
	$labelGroup		= HtmlTag::create( 'span', $labelGroup, ['class' => 'group-'.$group->groupId] );
	if( strlen( $group->description ) )
		$labelGroup	.= '<br/><blockquote>'.nl2br( $group->description ).'</blockquote>';
	$labelCount		= HtmlTag::create( 'span', count( $group->users ), ['class' => 'group count'] );
	$labelAccess	= HtmlTag::create( 'span', $words['types'][$group->type], ['class' => 'group-type type'.$group->type] );

	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $labelGroup ),
		HtmlTag::create( 'td', $labelCount ),
		HtmlTag::create( 'td', $labelAccess ),
	] );
}
$heads	= HtmlElements::TableHeads( $heads );
$table	= HtmlTag::create( 'table', [
	HtmlElements::ColumnGroup( "45%", "10%", "25%" ),
	HtmlTag::create( 'thead', $heads ),
	HtmlTag::create( 'tbody', $rows ),
], ['class' => 'table not-table-condensed table-striped', 'id' => 'groups'] );

$panelFilter	= '';

$buttonAdd	= '';
if( $hasRightToAdd ){
	$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
	if( $env->getModules()->get( 'UI_Font_FontAwesome' ) )
		$iconAdd		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );
	$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$wf->buttonAdd, [
		'href'	=> './manage/group/add',
		'class'	=> 'btn btn-success'
	] );
}

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/group/' ) );

return $textIndexTop.'
<style>
table tr td blockquote {
	margin: 0 0 0.1em 0.4em;
	}
</style>
<div id="site-group-index">
	<div class="row-fluid">
<!--		<div class="span3">
			'.$panelFilter.'
		</div>-->
		<div class="span12">
			<div class="content-panel">
				<h3>'.$wf->heading.' <small class="muted">('.count( $groups ).')</small></h3>
				<div class="content-panel-inner">
					'.$table.'
					<div class="buttonbar">
						<div class="btn-toolbar">
							'.$buttonAdd.'
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>'.$textIndexBottom;
