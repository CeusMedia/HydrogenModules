<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array<object> $readers */
/** @var ?string $filterLimit */
/** @var ?string $filterPage */
/** @var int $total */
/** @var int $found */
/** @var int $totalReaders */
/** @var ?Logic_Limiter $limiter */


$w			= (object) $words->index;
$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
$iconImport	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] ).'&nbsp;';
$iconExport	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] ).'&nbsp;';

$statusIcons	= [
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
];

$labelEmpty	= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );
$list		= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );

if( $readers ){
	$list	= [];
	foreach( $readers as $reader ){
		$attributes		= ['href' => './work/newsletter/reader/edit/'.$reader->newsletterReaderId];
		$iconStatus		= HtmlTag::create( 'i', "", ['class' => 'fa fa-fw fa-'.$statusIcons[$reader->status]] );
		$prefix			= HtmlTag::create( 'span', (string) $reader->prefix, ['class' => 'muted'] );
		$label			= $reader->email;
		$fullname		= '<br/><small class="muted">'.trim( $prefix.' '.$reader->firstname.' '.$reader->surname ).'&nbsp;</small>';
		$link			= HtmlTag::create( 'a', $label, $attributes );
		$groups			= [];
		foreach( $reader->groups as $group )
			$groups[]		= $group->title;
	//	$groups			= HtmlTag::create( 'span', count( $groups ), ['class' => 'badge', 'title' => join( ', ', $groups] ) );
		$groups			= join( ', ', $groups );
		$cellTitle		= HtmlTag::create( 'td', $link.$fullname, ['class' => 'autocut'] );
		$cellStatus		= HtmlTag::create( 'td', $iconStatus.' '.$words->states[$reader->status] );
		$cellGroups		= HtmlTag::create( 'td', $groups );
		$cellRegistered	= HtmlTag::create( 'td', date( 'd.m.Y', $reader->registeredAt ) );
		$rowColor		= $reader->status == 1 ? 'success' : ( $reader->status == -1 ? 'error' : 'warning' );
		$cells			= [$cellTitle, $cellGroups, $cellStatus, $cellRegistered];
		$attributes		= ['class' => $rowColor];
		$list[]			= HtmlTag::create( 'tr', $cells, $attributes );
	}
	$tableRows		= join( $list );
	$tableHeads		= HtmlElements::TableHeads( [
		$words->index->columnTitle,
		$words->index->columnGroups,
		$words->index->columnStatus,
		$words->index->columnRegister
	] );
	$tableColumns	= HtmlElements::ColumnGroup( ['', '40%', '100px', '100px'] );
	$tableHead		= HtmlTag::create( 'thead', $tableHeads );
	$tableBody		= HtmlTag::create( 'tbody', $tableRows );
	$list	= HtmlTag::create( 'table', $tableColumns.$tableHead.$tableBody, ['class' => 'table table-condensed table-hover table-striped table-fixed'] );
}

$pagination		= new PageControl( './work/newsletter/reader', $filterPage, ceil( $totalReaders / $filterLimit ) );

$buttonImport	= '';
if( $env->getAcl()->has( 'work/newsletter/reader', 'import' ) ){
	$buttonImport	= HtmlTag::create( 'div', [
		HtmlTag::create( 'a', $iconImport.'importieren&nbsp;<span class="caret"></span>', [
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		] ),
		HtmlTag::create( 'ul', [
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', 'aus Empfängerliste', [
					'href'			=> '#modalImportList',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				] )
			] ),
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', 'aus CSV-Exportdatei', [
					'href'			=> '#modalImportCsv',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				] )
			] ),
		], ['class' => 'dropdown-menu'] ),
	], ['class' => 'btn-group'] );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowImport' ) ){
	$buttonImport	= HtmlTag::create( 'button', $iconImport.'importieren&nbsp;<span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert("Importieren von Abonnenten ist in dieser Demo-Installation nicht möglich.")',
	] );
}

$buttonExport	= '';
if( $env->getAcl()->has( 'work/newsletter/reader', 'export' ) ){
	$buttonExport	= HtmlTag::create( 'div', [
		HtmlTag::create( 'a', $iconExport.'exportieren&nbsp;<span class="caret"></span>', [
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		] ),
		HtmlTag::create( 'ul', [
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', 'in Empfängerliste', [
					'href'	=> './work/newsletter/reader/export/list',
				] )
			] ),
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', 'in CSV-Exportdatei', [
					'href'	=> './work/newsletter/reader/export/csv',
				] )
			] ),
		], ['class' => 'dropdown-menu'] ),
	], ['class' => 'btn-group'] );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowExport' ) ){
	$buttonExport	= HtmlTag::create( 'button', $iconExport.'exportieren&nbsp;<span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert("Exportieren von Abonnenten ist in dieser Demo-Installation nicht möglich.")',
	] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'neuer Abonnent', [
	'href'		=> './work/newsletter/reader/add',
	'class'		=> 'btn btn-success btn-small',
] );
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:maxItems', $totalReaders + 1 ) )
	$buttonAdd	= HtmlTag::create( 'button', $iconAdd.'neuer Leser', [
		'type'		=> 'button',
		'class'		=> 'btn btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Abonnenten sind in dieser Demo-Installation nicht möglich.")',
	] );

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
