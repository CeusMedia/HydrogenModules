<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<Entity_Form_Import_Rule> $rules List of import rules */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$table	= HtmlTag::create( 'div', $w->noEntries.' <a href="./manage/form/import/add" class="btn btn-success btn-mini"><i class="fa fa-fw fa-plus"></i>&nbsp;'.$w->buttonAdd.'</a>', ['class' => 'alert alert-info'] );

$statuses	= $words['statuses'];

$helper	= new View_Helper_TimePhraser( $env );
$helper->setTemplate( $w->timePhrase );
if( count( $rules ) ){
	$rows	= [];
	foreach( $rules as $rule ){
		$link	= HtmlTag::create( 'a', $rule->title, ['href' => './manage/form/import/edit/'.$rule->formImportRuleId] );
		$rows[]	= HtmlTag::create( 'tr', [
//			HtmlTag::create( 'td', print_m($target, NULL, NULL, TRUE ) ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $statuses[$rule->status] ),
//			HtmlTag::create( 'td', $target->className ),
			HtmlTag::create( 'td', $rule->form->title ),
//			HtmlTag::create( 'td', $rule->usedAt ? $helper->setTimestamp( $rule->usedAt )->render() : '-' ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle, $w->headStatus/*, 'Implementierung'*/, $w->headForm/*, 'Verwendung'*/] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-striped table-fixed not-table-bordered'] );
}

$iconAdd	= new Icon( 'plus' );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, ['class' => 'btn btn-success', 'href' => './manage/form/import/add'] );

return '<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
