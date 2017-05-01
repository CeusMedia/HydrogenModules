<?php

$wf		= (object) $words['index'];

$heads	= array(
	$wf->headTitle,
	$wf->headUsers,
	$wf->headAccess,
	$wf->headRegister
);

$rows	= array();
foreach( $roles as $nr => $role ){
	$labelRole	= $role->title;
	if( $hasRightToEdit ){
		$labelRole	= UI_HTML_Tag::create( 'a', $labelRole, array( 'href' => './manage/role/edit/'.$role->roleId ) );
	}
	$labelRole		= UI_HTML_Tag::create( 'span', $labelRole, array( 'class' => 'role-'.$role->roleId ) );
	if( strlen( $role->description ) )
		$labelRole	.= '<br/><blockquote>'.nl2br( $role->description ).'</blockquote>';
	$labelCount		= UI_HTML_Tag::create( 'span', count( $role->users ), array( 'class' => 'role count' ) );
	$labelAccess	= UI_HTML_Tag::create( 'span', $words['type-access'][$role->access], array( 'class' => 'role-access access'.$role->access ) );
	$labelRegister	= UI_HTML_Tag::create( 'span', $words['type-register'][$role->register], array( 'class' => 'role-register register'.$role->register ) );

	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $labelRole ),
		UI_HTML_Tag::create( 'td', $labelCount ),
		UI_HTML_Tag::create( 'td', $labelAccess ),
		UI_HTML_Tag::create( 'td', $labelRegister ),
	) );
}
$heads	= UI_HTML_Elements::TableHeads( $heads );
$table	= UI_HTML_Tag::create( 'table', array(
	UI_HTML_Elements::ColumnGroup( "45%", "10%", "25%", "20%" ),
	UI_HTML_Tag::create( 'thead', $heads ),
	UI_HTML_Tag::create( 'tbody', $rows ),
), array( 'class' => 'table not-table-condensed table-striped', 'id' => 'roles' ) );

$panelFilter	= '';

$buttonAdd	= '';
if( $hasRightToAdd ){
	$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	if( $env->getModules()->get( 'UI_Font_FontAwesome' ) )
		$iconAdd		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus' ) );
	$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$wf->buttonAdd, array(
		'href'	=> './manage/role/add',
		'class'	=> 'btn btn-success'
	) );
}

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/role/' ) );

return $textIndexTop.'
<style>
table tr td blockquote {
	margin: 0 0 0.1em 0.4em;
	}
</style>
<div id="site-role-index">
	<div class="row-fluid">
<!--		<div class="span3">
			'.$panelFilter.'
		</div>-->
		<div class="span12">
			<div class="content-panel">
				<h3>'.$wf->heading.' <small class="muted">('.count( $roles ).')</small></h3>
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
?>
