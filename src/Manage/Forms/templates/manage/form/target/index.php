<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var object $target */
/** @var array<object> $targets List of transfer targets */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$table	= HtmlTag::create( 'div', $w->noEntries.' <a href="./manage/form/target/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;'.$w->buttonAdd.'</a>', ['class' => 'alert alert-info'] );

$statusHelper	= View_Helper_StatusBadge::create()
	->setType( View_Helper_StatusBadge::TYPE_LABEL )
	->setStatusMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> 1,
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> 0,
	] )
	->setLabelMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> $words['statuses'][1],
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> $words['statuses'][0],
	] );

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( $w->timePhrase );
if( count( $targets ) ){
	$rows	= [];
	foreach( $targets as $target ){
		$link	= HtmlTag::create( 'a', $target->title, ['href' => './manage/form/target/edit/'.$target->formTransferTargetId] );
		$rows[]	= HtmlTag::create( 'tr', [
//			HtmlTag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statusHelper->setStatus( $target->status ) ),
//			HtmlTag::create( 'td', $target->className ),
			HtmlTag::create( 'td', $target->rules ),
			HtmlTag::create( 'td', $target->transfers ),
			HtmlTag::create( 'td', $target->fails ),
			HtmlTag::create( 'td', $target->usedAt ? $helper->setTimestamp( $target->usedAt )->render() : '-' ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '10%', '10%', '10%', '10%', '10%'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle, $w->headStatus, $w->headForms, $w->headTransfers, $w->headFails, $w->headUsedAt] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped table-fixed not-table-bordered'] );
}

$iconAdd	= new Icon( 'plus' );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, ['class' => 'btn btn-success', 'href' => './manage/form/target/add'] );

return '<div class="content-panel">
	<h3><span class="muted">'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
