<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var object $target */
/** @var array<object> $targets List of transfer targets */

$table	= HtmlTag::create( 'div', 'Keine Transferziele definiert. <a href="./manage/form/target/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;hinzufügen</a>', ['class' => 'alert alert-info'] );

$statusHelper	= View_Helper_StatusBadge::create()
	->setStatusMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> 1,
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> 0,
	] )
	->setLabelMap( [
		View_Helper_StatusBadge::STATUS_POSITIVE	=> 'aktiv',
		View_Helper_StatusBadge::STATUS_NEGATIVE	=> 'inaktiv',
	] );

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( 'vor %s' );
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
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Titel', 'Zustand', 'Formulare', 'Transfers', 'Fails', 'Verwendung'] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped table-fixed not-table-bordered'] );
}

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', ['class' => 'btn btn-success', 'href' => './manage/form/target/add'] );

return '<div class="content-panel">
	<h3><span class="muted">Transferziele</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
