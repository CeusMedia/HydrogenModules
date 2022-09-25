<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';
$iconInstall	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus-circle' ) ).'&nbsp;';

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

$labelEmpty	= HtmlTag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$list		= HtmlTag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );

if( $templates ){
	$list	= [];
	foreach( $templates as $template ){
		$attributes	= array( 'href' => './work/newsletter/template/edit/'.$template->newsletterTemplateId );
		$link	= HtmlTag::create( 'a', $template->title, $attributes );
		$iconStatus		= HtmlTag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$template->status] ) );
		$cellLink		= HtmlTag::create( 'td', $link, array( 'class' => 'autocut' ) );
		$theme			= HtmlTag::create( 'small', $w->noTheme, array( 'class' => 'muted' ) );
		if( $template->themeId && $template->theme )
		 	$theme		= $template->theme->title.' '.HtmlTag::create( 'small', '(Version '.$template->theme->version.')', array( 'class' => 'muted' ) );
		$cellTheme		= HtmlTag::create( 'td', $theme );
		$cellStatus		= HtmlTag::create( 'td', $iconStatus.' '.$words->states[$template->status] );
		$cellCreated	= HtmlTag::create( 'td', date( 'd.m.Y', $template->createdAt ) );
		$cellModified	= HtmlTag::create( 'td', $template->modifiedAt ? date( 'd.m.Y', $template->modifiedAt ) : '-' );
		$rowColor		= $statusColors[$template->status];
		$cells			= array( $cellLink, $cellTheme, $cellStatus, $cellCreated, $cellModified );
		$attributes		= array( 'class' => $rowColor );
		$list[]			= HtmlTag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '', '', '140px', '100px', '100px' ) );
	$tableHeads		= UI_HTML_Elements::TableHeads( array( $w->columnTitle, $w->columnTheme, $w->columnStatus, $w->columnCreatedAt, $w->columnModifiedAt ) );
	$tableHead		= HtmlTag::create( 'thead', $tableHeads );
	$tableBody		= HtmlTag::create( 'tbody', $tableRows );
	$list			= HtmlTag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped table-fixed' ) );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.$w->linkAdd, array(
	'href'	=> './work/newsletter/template/add',
	'class'	=> 'btn not-btn-small btn-success'
) );
$buttonInstall	= HtmlTag::create( 'a', $iconInstall.$w->linkInstall, array(
	'href'	=> './work/newsletter/template/install',
	'class'	=> 'btn not-btn-small'
) );
if( $limiter && $limiter->denies( 'Work.Newsletter.Template:maxItems', count( $templates ) + 1 ) ){
	$buttonAdd	= HtmlTag::create( 'button', $iconAdd.$w->link_add, array(
		'type'		=> 'button',
		'class'		=> 'btn not-btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Templates sind in dieser Demo-Installation nicht möglich.")',
	) );
	$buttonInstall	= HtmlTag::create( 'a', $iconInstall.$w->linkInstall, array(
		'type'		=> 'button',
		'class'		=> 'btn not-btn-small disabled',
		'onclick'	=> 'alert("Weitere Templates sind in dieser Demo-Installation nicht möglich.")',
	) );
}

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				$list,
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) ),
		HtmlTag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
