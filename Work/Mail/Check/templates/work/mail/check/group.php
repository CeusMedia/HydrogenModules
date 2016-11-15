<?php

$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times-circle' ) );


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


$rows	= array();
foreach( $groups as $group ){

	$percentTested	= 0;
	$percentSuccess	= 0;
	if( $group->numbers->total ){
		$percentTested	= floor( $group->numbers->tested / $group->numbers->total * 1000 ) / 10;
		$percentSuccess	= floor( $group->numbers->positive / $group->numbers->total * 1000 ) / 10;
	}

	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'		=> './work/mail/check/removeGroup/'.$group->mailGroupId,
		'class'		=> 'btn btn-small btn-inverse',
		'onclick'	=> 'return confirm(\'Wirklich?\nDabei werden alle Adressen und Prüfungen gelöscht.\')',
	) );
	$buttons	= UI_HTML_Tag::create( 'div', $buttonRemove, array( 'class' => 'btn-group' ) );
	$link		= UI_HTML_Tag::create( 'a', $group->title, array(
		'href'	=> './work/mail/check/filter/reset?groupId='.$group->mailGroupId,
		'class'	=> '',
	) );
	$link		.= '&nbsp;'.UI_HTML_Tag::create( 'small', '('.$group->numbers->total.')', array( 'class' => 'muted' ) );
	$createdAt	= UI_HTML_Tag::create( 'small', date( 'd.m.Y', $group->createdAt ) );

	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-group-title' ) ),
		UI_HTML_Tag::create( 'td', $percentTested.'% ('.$group->numbers->tested.')', array( 'class' => 'cell-group-tested' ) ),
		UI_HTML_Tag::create( 'td', $percentSuccess.'% ('.$group->numbers->positive.')', array( 'class' => 'cell-group-success' ) ),
		UI_HTML_Tag::create( 'td', $createdAt, array( 'class' => 'cell-group-createdAt' ) ),
		UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-group-createdAt' ) ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( "", "15%", "15%", "15%", "15%" );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Name', 'Getested', 'Qualität', 'Erstellung' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

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
