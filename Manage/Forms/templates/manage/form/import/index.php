<?php

$table	= UI_HTML_Tag::create( 'div', 'Keine Importregeln definiert. <a href="./manage/form/import/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;hinzufügen</a>', array( 'class' => 'alert alert-info' ) );

$statuses	= [
	Model_Form_Import_Rule::STATUS_NEW		=> 'neu',
	Model_Form_Import_Rule::STATUS_TEST		=> 'Testmodus',
	Model_Form_Import_Rule::STATUS_ACTIVE	=> 'aktiviert',
	Model_Form_Import_Rule::STATUS_PAUSED	=> 'pausiert',
	Model_Form_Import_Rule::STATUS_DISABLED	=> 'deaktiviert',
];

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( 'vor %s' );
if( count( $rules ) ){
	$rows	= [];
	foreach( $rules as $rule ){
		$link	= UI_HTML_Tag::create( 'a', $rule->title, array( 'href' => './manage/form/import/edit/'.$rule->formImportRuleId ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', [
//			UI_HTML_Tag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $statuses[$rule->status] ),
//			UI_HTML_Tag::create( 'td', $target->className ),
			UI_HTML_Tag::create( 'td', $rule->form->title ),
//			UI_HTML_Tag::create( 'td', $rule->usedAt ? $helper->setTimestamp( $rule->usedAt )->render() : '-' ),
		] );
	}
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( ['Titel', 'Zustand'/*, 'Implementierung'*/, 'Formular'/*, 'Verwendung'*/] ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', [$thead, $tbody], array( 'class' => 'table table-striped table-fixed not-table-bordered' ) );
}

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'class' => 'btn btn-success', 'href' => './manage/form/import/add' ) );

return '<div class="content-panel">
	<h3>Importquellen</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
