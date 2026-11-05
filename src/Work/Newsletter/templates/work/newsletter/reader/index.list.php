<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array<Entity_Newsletter_Reader> $readers */
/** @var ?string $filterLimit */
/** @var ?string $filterPage */
/** @var int $total */
/** @var int $found */
/** @var int $totalReaders */
/** @var ?Logic_Limiter $limiter */
/** @var bool $canExport */
/** @var bool $canImport */


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
		$cellTitle		= HtmlTag::create( 'td', $link.$fullname, ['class' => 'autocut'] );
		$cellStatus		= HtmlTag::create( 'td', $iconStatus.' '.$words->states[$reader->status] );
		$cellGroups		= HtmlTag::create( 'td', join( ', ', $groups ) );
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
if( $canImport ){
	$buttonImport	= HtmlTag::create( 'div', [
		HtmlTag::create( 'a', $iconImport.$w->buttonImport.'&nbsp;<span class="caret"></span>', [
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		] ),
		HtmlTag::create( 'ul', [
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', $w->linkImportList, [
					'href'			=> '#modalImportList',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				] )
			] ),
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', $w->linkImportCsv, [
					'href'			=> '#modalImportCsv',
					'role'			=> 'button',
					'data-toggle'	=> 'modal',
				] )
			] ),
		], ['class' => 'dropdown-menu'] ),
	], ['class' => 'btn-group'] );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowImport' ) ){
	$buttonImport	= HtmlTag::create( 'button', $iconImport.$w->buttonImport.'&nbsp;<span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert('.htmlspecialchars( json_encode( $w->alertImportDisabled ), ENT_QUOTES, 'UTF-8' ).')',
	] );
}

$buttonExport	= '';
if( $canExport ){
	$buttonExport	= HtmlTag::create( 'div', [
		HtmlTag::create( 'a', $iconExport.$w->buttonExport.'&nbsp;<span class="caret"></span>', [
			'href'			=> '#',
			'class'			=> 'btn btn-small dropdown-toggle',
			'data-toggle'	=> 'dropdown'
		] ),
		HtmlTag::create( 'ul', [
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', $w->linkExportList, [
					'href'	=> './work/newsletter/reader/export/list',
				] )
			] ),
			HtmlTag::create( 'li', [
				HtmlTag::create( 'a', $w->linkExportCsv, [
					'href'	=> './work/newsletter/reader/export/csv',
				] )
			] ),
		], ['class' => 'dropdown-menu'] ),
	], ['class' => 'btn-group'] );
}
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:allowExport' ) ){
	$buttonExport	= HtmlTag::create( 'button', $iconExport.$w->buttonExport.'&nbsp;<span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn btn-small disabled',
		'onclick'	=> 'alert('.htmlspecialchars( json_encode( $w->alertExportDisabled ), ENT_QUOTES, 'UTF-8' ).')',
	] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.$w->link_add, [
	'href'		=> './work/newsletter/reader/add',
	'class'		=> 'btn btn-success btn-small',
] );
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:maxItems', $totalReaders + 1 ) )
	$buttonAdd	= HtmlTag::create( 'button', $iconAdd.$w->link_add, [
		'type'		=> 'button',
		'class'		=> 'btn btn-small btn-success disabled',
		'onclick'	=> 'alert('.htmlspecialchars( json_encode( $w->alertLimitReached ), ENT_QUOTES, 'UTF-8' ).')',
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
