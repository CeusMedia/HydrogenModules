<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';
$iconInstall	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus-circle' ) ).'&nbsp;';

$statusIcons	= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
	2		=> 'globe',
);
$statusColors	= array(
	-1		=> "error",
	0		=> "warning",
	1		=> "success",
	2		=> "info",
);

$w			= (object) $words->index_list;

$labelEmpty	= UI_HTML_Tag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$list		= UI_HTML_Tag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );

if( $templates ){
	$list	= [];
	foreach( $templates as $template ){
		$attributes	= array( 'href' => './work/newsletter/template/edit/'.$template->newsletterTemplateId );
		$link	= UI_HTML_Tag::create( 'a', $template->title, $attributes );
		$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$template->status] ) );
		$cellLink		= UI_HTML_Tag::create( 'td', $link, array( 'class' => 'autocut' ) );
		$theme			= UI_HTML_Tag::create( 'small', $w->noTheme, array( 'class' => 'muted' ) );
		if( $template->themeId && $template->theme )
		 	$theme		= $template->theme->title.' '.UI_HTML_Tag::create( 'small', '(Version '.$template->theme->version.')', array( 'class' => 'muted' ) );
		$cellTheme		= UI_HTML_Tag::create( 'td', $theme );
		$cellStatus		= UI_HTML_Tag::create( 'td', $iconStatus.' '.$words->states[$template->status] );
		$cellCreated	= UI_HTML_Tag::create( 'td', date( 'd.m.Y', $template->createdAt ) );
		$cellModified	= UI_HTML_Tag::create( 'td', $template->modifiedAt ? date( 'd.m.Y', $template->modifiedAt ) : '-' );
		$rowColor		= $statusColors[$template->status];
		$cells			= array( $cellLink, $cellTheme, $cellStatus, $cellCreated, $cellModified );
		$attributes		= array( 'class' => $rowColor );
		$list[]			= UI_HTML_Tag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '', '', '140px', '100px', '100px' ) );
	$tableHeads		= UI_HTML_Elements::TableHeads( array( $w->columnTitle, $w->columnTheme, $w->columnStatus, $w->columnCreatedAt, $w->columnModifiedAt ) );
	$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tableBody		= UI_HTML_Tag::create( 'tbody', $tableRows );
	$list			= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.$w->linkAdd, array(
	'href'	=> './work/newsletter/template/add',
	'class'	=> 'btn not-btn-small btn-success'
) );
$buttonInstall	= UI_HTML_Tag::create( 'a', $iconInstall.$w->linkInstall, array(
	'href'	=> './work/newsletter/template/install',
	'class'	=> 'btn not-btn-small'
) );
if( $limiter && $limiter->denies( 'Work.Newsletter.Template:maxItems', count( $templates ) + 1 ) ){
	$buttonAdd	= UI_HTML_Tag::create( 'button', $iconAdd.$w->link_add, array(
		'type'		=> 'button',
		'class'		=> 'btn not-btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Templates sind in dieser Demo-Installation nicht möglich.")',
	) );
	$buttonInstall	= UI_HTML_Tag::create( 'a', $iconInstall.$w->linkInstall, array(
		'type'		=> 'button',
		'class'		=> 'btn not-btn-small disabled',
		'onclick'	=> 'alert("Weitere Templates sind in dieser Demo-Installation nicht möglich.")',
	) );
}

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				$list,
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) ),
		UI_HTML_Tag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
