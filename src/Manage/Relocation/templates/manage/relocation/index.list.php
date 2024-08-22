<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var View_Manage_Relocation $view */
/** @var array<object> $relocations */
/** @var int $page */
/** @var int $total */
/** @var int $limit */
/** @var int $count */

$w			= (object) $words['index-list'];

$pagination	= new PageControl( './manage/relocation', $page, ceil( $count / $limit ) );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconGo		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
$iconExport	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );

$table	= '<div class="muted"><em><small class="muted">'.$w->noEntries.'</small></em></div>';

if( $relocations ){
	$helper		= new View_Helper_TimePhraser( $env );
	$rows		= [];
	foreach( $relocations as $relocation ){
		$class	= "warning";
		if( $relocation->status > 0 )
			$class	= "success";
		else if( $relocation->status < 0 )
			$class	= "error";
		$uri	= "./manage/relocation/edit/".$relocation->relocationId;
		$link	= HtmlTag::create( 'a', $relocation->title, ['href' => $uri] );
		$usedAt		= $helper->convert( $relocation->usedAt, TRUE, $w->prefixTimePhraser, $w->suffixTimePhraser );
		$buttonEdit	= HtmlTag::create( 'a', $iconEdit, [
			'href'	=> $uri,
			'class'	=> 'btn btn-small',
			'title'	=> $w->buttonEdit,
		] );
		$buttonGo	= HtmlTag::create( 'a', $iconGo, [
			'href'	=> $relocation->url,
			'class'	=> 'btn btn-small',
			'title'	=> $w->buttonGo,
		] );
		$buttons	= HtmlTag::create( 'div', [$buttonEdit, $buttonGo], ['class' => 'btn-group'] );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $relocation->relocationId ),
			HtmlTag::create( 'td', $link.'<br/><small>'.$relocation->url.'</small>', ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $relocation->views ),
			HtmlTag::create( 'td', $usedAt ),
			HtmlTag::create( 'td', $buttons ),
		), [
			'data-status'	=> $relocation->status,
			'data-url'		=> $relocation->url,
			'class'			=> $class
		] );
	}
	$columns	= HtmlElements::ColumnGroup( "50px", "", "80px", "120px", "100px" );
	$heads	= HtmlElements::TableHeads( [$w->headId, $w->headTitle, $w->headViews, $w->headUsedAt, $w->headActions] );
	$thead	= HtmlTag::create( 'thead', $heads );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', $columns.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', [
	'href'	=> './manage/relocation/add',
	'class'	=> 'btn btn-success'
] );

$buttonExport	= HtmlTag::create( 'a', $iconExport.'&nbsp;exportieren', [
	'href'	=> './manage/relocation/export',
	'class'	=> 'btn not-btn-info'
] );

$abbrCount	= HtmlTag::create( 'abbr', $count, ['title' => $w->titleCount] );
$abbrTotal	= HtmlTag::create( 'abbr', $total, ['title' => $w->titleTotal] );

return '
		<div class="content-panel">
			<h3>Einträge <small>('.$abbrCount.'/'.$abbrTotal.')</small></h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="btn-toolbar">
					'.$pagination.'
					'.$buttonAdd.'
					'.$buttonExport.'
				</div>
			</div>
		</div>
';
