<?php

$w		= (object) $words['index'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';

$statusIcons		= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
);

$labelEmpty	= UI_HTML_Tag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$list		= UI_HTML_Tag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );

if( $groups ){
	$list	= array();
	foreach( $groups as $group ){
		$attributes		= array(
			'href'	=> './work/newsletter/group/edit/'.$group->newsletterGroupId,
			'title'	=> $group->title,
		);

		$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$group->status] ) );
		$link			= UI_HTML_Tag::create( 'a', $group->title, $attributes );
		$label			= $link.'&nbsp;<small class="muted">('.count( $group->readers ).' Leser)</small>';
	//	$label			= UI_HTML_Tag::create( 'span', $label, array( 'class' => '' ) );
	#	$groups	= array();
	#	foreach( $reader->groups as $group )
	#		$groups[]		= $group->title;
	#	$groups			= UI_HTML_Tag::create( 'span', count( $groups ), array( 'class' => 'badge', 'title' => join( ', ', $groups ) ) );
		$cellLink		= UI_HTML_Tag::create( 'td', $label, array( 'class' => 'autocut cell-group-title' ) );
		$cellType		= UI_HTML_Tag::create( 'td', $words['types'][$group->type] );
		$cellStatus		= UI_HTML_Tag::create( 'td', $iconStatus.'&nbsp;'.$words['states'][$group->status] );
	#	$cellGroups		= UI_HTML_Tag::create( 'td', $groups );
		$cellCreated	= UI_HTML_Tag::create( 'td', date( 'd.m.Y', $group->createdAt ) );
		$cellModified	= UI_HTML_Tag::create( 'td', $group->modifiedAt ? date( 'd.m.Y', $group->modifiedAt ) : '-' );
		$rowColor		= $group->status == 1 ? 'success' : ( $group->status == -1 ? 'error' : 'warning' );
		$cells			= array( $cellLink, $cellType, $cellStatus, $cellCreated, $cellModified );
		$attributes		= array( 'class' => $rowColor );
		$list[]	= UI_HTML_Tag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableHeads		= UI_HTML_Elements::TableHeads( array(
		$words['index']['columnTitle'],
		$words['index']['columnType'],
		$words['index']['columnStatus'],
		$words['index']['columnCreated'],
		$words['index']['columnModified']
	) );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '', '150px', '120px', '100px', '100px' ) );
	$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tableBody		= UI_HTML_Tag::create( 'tbody', $tableRows );
	$list			= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.$w->link_add, array(
	'href'	=> "./work/newsletter/group/add",
	'class'	=> "btn btn-small btn-success",
) );
if( $limiter && $limiter->denies( 'Work.Newsletter.Group:maxItems', $totalGroups + 1 ) )
	$buttonAdd	= UI_HTML_Tag::create( 'button', $iconAdd.$w->link_add, array(
		'class'		=> 'btn btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Kategorien sind in dieser Demo-Installation nicht möglich.")',
	) );

$filter		= $view->loadTemplateFile( 'work/newsletter/group/index.list.filter.php' );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$filter.'
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
