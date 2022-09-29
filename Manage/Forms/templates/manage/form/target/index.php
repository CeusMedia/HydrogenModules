<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= HtmlTag::create( 'div', 'Keine Transferziele definiert. <a href="./manage/form/target/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;hinzufügen</a>', ['class' => 'alert alert-info'] );

$statuses	= [
	0	=> 'inaktiv',
	1	=> 'aktiv',
];

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( 'vor %s' );
if( count( $targets ) ){
	$rows	= [];
	foreach( $targets as $target ){
		$link	= HtmlTag::create( 'a', $target->title, ['href' => './manage/form/target/edit/'.$target->formTransferTargetId] );
		$rows[]	= HtmlTag::create( 'tr', [
//			HtmlTag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statuses[$target->status] ),
//			HtmlTag::create( 'td', $target->className ),
			HtmlTag::create( 'td', $target->transfers ),
			HtmlTag::create( 'td', $target->fails ),
			HtmlTag::create( 'td', $target->usedAt ? $helper->setTimestamp( $target->usedAt )->render() : '-' ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Titel', 'Zustand'/*, 'Implementierung'*/, 'Transfers', 'Fails', 'Verwendung'] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-striped table-fixed not-table-bordered'] );
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
