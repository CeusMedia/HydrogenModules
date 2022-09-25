<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$table	= HtmlTag::create( 'div', 'Keine Importregeln definiert. <a href="./manage/form/import/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;hinzufügen</a>', array( 'class' => 'alert alert-info' ) );

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
		$link	= HtmlTag::create( 'a', $rule->title, array( 'href' => './manage/form/import/edit/'.$rule->formImportRuleId ) );
		$rows[]	= HtmlTag::create( 'tr', [
//			HtmlTag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statuses[$rule->status] ),
//			HtmlTag::create( 'td', $target->className ),
			HtmlTag::create( 'td', $rule->form->title ),
//			HtmlTag::create( 'td', $rule->usedAt ? $helper->setTimestamp( $rule->usedAt )->render() : '-' ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Titel', 'Zustand'/*, 'Implementierung'*/, 'Formular'/*, 'Verwendung'*/] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], array( 'class' => 'table table-striped table-fixed not-table-bordered' ) );
}

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', array( 'class' => 'btn btn-success', 'href' => './manage/form/import/add' ) );

return '<div class="content-panel">
	<h3>Importquellen</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
