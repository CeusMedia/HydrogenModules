<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-times-circle'] );


$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>Add</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/addGroup" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_mailColumn">Name der Hauptspalte <small class="muted">(E-Mail)</small></label>
					<input type="text" name="mailColumn" id="input_mailColumn" class="span12" placeholder="E-Mail"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_columns">weitere Spalten <small class="muted">(z.B. Name)</small></label>
					<textarea name="columns" id="input_columns" class="span12" rows="4"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;add</button>
			</div>
		</form>
	</div>
</div>';


$rows	= [];
foreach( $groups as $group ){

	$percentTested	= 0;
	$percentSuccess	= 0;
	if( $group->numbers->total ){
		$percentTested	= floor( $group->numbers->tested / $group->numbers->total * 1000 ) / 10;
		$percentSuccess	= floor( $group->numbers->positive / $group->numbers->total * 1000 ) / 10;
	}

	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'		=> './work/mail/check/removeGroup/'.$group->mailGroupId,
		'class'		=> 'btn btn-small btn-inverse',
		'onclick'	=> 'return confirm(\'Wirklich?\nDabei werden alle Adressen und Prüfungen gelöscht.\')',
	) );
	$buttons	= HtmlTag::create( 'div', $buttonRemove, ['class' => 'btn-group'] );
	$link		= HtmlTag::create( 'a', $group->title, [
		'href'	=> './work/mail/check/filter/reset?groupId='.$group->mailGroupId,
		'class'	=> '',
	] );
	$link		.= '&nbsp;'.HtmlTag::create( 'small', '('.$group->numbers->total.')', ['class' => 'muted'] );
	$createdAt	= HtmlTag::create( 'small', date( 'd.m.Y', $group->createdAt ) );

	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link, ['class' => 'cell-group-title'] ),
		HtmlTag::create( 'td', $percentTested.'% ('.$group->numbers->tested.')', ['class' => 'cell-group-tested'] ),
		HtmlTag::create( 'td', $percentSuccess.'% ('.$group->numbers->positive.')', ['class' => 'cell-group-success'] ),
		HtmlTag::create( 'td', $createdAt, ['class' => 'cell-group-createdAt'] ),
		HtmlTag::create( 'td', $buttons, ['class' => 'cell-group-createdAt'] ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( "", "15%", "15%", "15%", "15%" );
$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Name', 'Getested', 'Qualität', 'Erstellung'] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );

$panelList	= '
<div class="content-panel content-panel-list content-panel-table">
	<h3>Gruppen</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$tabs	= View_Work_Mail_Check::renderTabs( $env, 'group' );

return $tabs.'
<div class="row-fluid">
	<div class="span9">
		'.$panelList.'
	</div>
	<div class="span3">
		'.$panelAdd.'
	</div>
</div>';
