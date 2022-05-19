<?php

$w			= (object) $words->index;
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';
$iconImport	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) ).'&nbsp;';
$iconExport	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) ).'&nbsp;';

$statusIcons	= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
);

$labelEmpty	= UI_HTML_Tag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$list		= UI_HTML_Tag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );

if( $readers ){
	$list	= [];
	foreach( $readers as $reader ){
		$attributes		= array( 'href' => './work/newsletter/reader/edit/'.$reader->newsletterReaderId );
		$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$reader->status] ) );
		$prefix			= UI_HTML_Tag::create( 'span', (string) $reader->prefix, array( 'class' => 'muted' ) );
		$label			= $reader->email;
		$fullname		= '<br/><small class="muted">'.trim( $prefix.' '.$reader->firstname.' '.$reader->surname ).'&nbsp;</small>';
		$link			= UI_HTML_Tag::create( 'a', $label, $attributes );
		$groups			= [];
		foreach( $reader->groups as $group )
			$groups[]		= $group->title;
	//	$groups			= UI_HTML_Tag::create( 'span', count( $groups ), array( 'class' => 'badge', 'title' => join( ', ', $groups ) ) );
		$groups			= join( ', ', $groups );
		$cellTitle		= UI_HTML_Tag::create( 'td', $link.$fullname, array( 'class' => 'autocut' ) );
		$cellStatus		= UI_HTML_Tag::create( 'td', $iconStatus.' '.$words->states[$reader->status] );
		$cellGroups		= UI_HTML_Tag::create( 'td', $groups );
		$cellRegistered	= UI_HTML_Tag::create( 'td', date( 'd.m.Y', $reader->registeredAt ) );
		$rowColor		= $reader->status == 1 ? 'success' : ( $reader->status == -1 ? 'error' : 'warning' );
		$cells			= array( $cellTitle, $cellGroups, $cellStatus, $cellRegistered );
		$attributes		= array( 'class' => $rowColor );
		$list[]			= UI_HTML_Tag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableHeads		= UI_HTML_Elements::TableHeads( array(
		$words->index->columnTitle,
		$words->index->columnGroups,
		$words->index->columnStatus,
		$words->index->columnRegister
	) );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '', '40%', '100px', '100px' ) );
	$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tableBody		= UI_HTML_Tag::create( 'tbody', $tableRows );
	$list	= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped table-fixed' ) );
}

$pagination		= new \CeusMedia\Bootstrap\PageControl( './work/newsletter/reader', $filterPage, ceil( $totalReaders / $filterLimit ) );

$buttonImport	= '';
if( $env->getAcl()->has( 'work/newsletter/reader', 'import' ) ){
	$buttonImport	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'a', $iconImport.'importieren&nbsp;<span class="caret"></span>', array(
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		) ),
		UI_HTML_Tag::create( 'ul', array(
			UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'a', 'aus Empfängerliste', array(
					'href'			=> '#modalImportList',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				) )
			) ),
			UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'a', 'aus CSV-Exportdatei', array(
					'href'			=> '#modalImportCsv',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				) )
			) ),
		), array( 'class' => 'dropdown-menu' ) ),
	), array( 'class' => 'btn-group' ) );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowImport' ) ){
	$buttonImport	= UI_HTML_Tag::create( 'button', $iconImport.'importieren&nbsp;<span class="caret"></span>', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert("Importieren von Abonnenten ist in dieser Demo-Installation nicht möglich.")',
	) );
}

$buttonExport	= '';
if( $env->getAcl()->has( 'work/newsletter/reader', 'export' ) ){
	$buttonExport	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'a', $iconExport.'exportieren&nbsp;<span class="caret"></span>', array(
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		) ),
		UI_HTML_Tag::create( 'ul', array(
			UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'a', 'in Empfängerliste', array(
					'href'	=> './work/newsletter/reader/export/list',
				) )
			) ),
			UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'a', 'in CSV-Exportdatei', array(
					'href'	=> './work/newsletter/reader/export/csv',
				) )
			) ),
		), array( 'class' => 'dropdown-menu' ) ),
	), array( 'class' => 'btn-group' ) );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowExport' ) ){
	$buttonExport	= UI_HTML_Tag::create( 'button', $iconExport.'exportieren&nbsp;<span class="caret"></span>', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert("Exportieren von Abonnenten ist in dieser Demo-Installation nicht möglich.")',
	) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'neuer Abonnent', array(
	'href'		=> './work/newsletter/reader/add',
	'class'		=> 'btn btn-success btn-small',
) );
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:maxItems', $totalReaders + 1 ) )
	$buttonAdd	= UI_HTML_Tag::create( 'button', $iconAdd.'neuer Leser', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Abonnenten sind in dieser Demo-Installation nicht möglich.")',
	) );

$filter		= $view->loadTemplateFile( 'work/newsletter/reader/index.list.filter.php' );

return '
<div class="content-panel">
	<h3>'.$w->heading.' <small class="muted">('.$found.'/'.$total.')</small></h3>
	<div class="content-panel-inner">
		'.$filter.'
		'.$list.'
		<div class="buttonbar">
			'.$pagination.'
			'.$buttonAdd.'
			'.$buttonImport.'
			'.$buttonExport.'
		</div>
	</div>
</div>';
