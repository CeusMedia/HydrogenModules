<?php

$filterStatus	= $session->get( 'filter_manage_project_status' );
if( !is_array( $filterStatus ) )
	$filterStatus	= array();

$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> in_array( $key, $filterStatus ) ? 'selected' : NULL
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

$optOrder	= array();
foreach( $words['filter-orders'] as $key => $value )
	$optOrder[$key]	= $value;
$optOrder	= UI_HTML_Elements::Options( $optOrder, $filterOrder );

$iconUp     = UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown   = UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );

$disabled   = $filterDirection == 'ASC';
$buttonUp   = UI_HTML_Elements::LinkButton( './manage/project/filter/?direction=ASC', $iconUp, 'tiny', NULL, $disabled );
$buttonDown = UI_HTML_Elements::LinkButton( './manage/project/filter/?direction=DESC', $iconDown, 'tiny', NULL, !$disabled );

$buttonFilter	= UI_HTML_Elements::Button( 'filter', $words['filter']['buttonFilter'], 'button filter add' );
$buttonReset	= UI_HTML_Elements::LinkButton( './manage/project/filter/reset', $words['filter']['buttonReset'], 'button filter reset remove' );

$panelFilter	= '
	<form name="" action="./manage/project/filter" method="post">
		<fieldset>
			<legend class="icon filter">'.$words['filter']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="input_status">Status</label><br/>
					<select name="status[]" multiple id="input_status" class="max row-6">'.$optStatus.'</select>
				</li>
				<li>
					<label for="input_status">Sortierung</label><br/>
					<div class="column-left-60">
						<select name="order" id="input_order" class="max" onchange="this.form.submit()">'.$optOrder.'</select>
					</div>
					<div class="column-left-40">
						'.$buttonUp.$buttonDown.'
					</div>
				</li>
			</ul>
			<div class="buttonbar">
				'.$buttonFilter.'
				'.$buttonReset.'
			</div>
		</fieldset>
	</form>
';

$indicator	= new UI_HTML_Indicator();



$rows	= array();
foreach( $projects as $project ){
	$cells		= array();
	$url		= './manage/project/edit/'.$project->projectId;
	$link		= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url ) );
	$users		= array();
	foreach( $project->users as $projectUser )
		$users[]	= $projectUser->username;
	$descLines	= explode( "\n", trim( $project->description ) );
	$desc		= trim( array_shift( $descLines ) );
	$desc		= Alg_Text_Trimmer::trim( $desc, 50 );
	if( count( $descLines ) )
		$desc	.= '&nbsp;<small><em>(...)</em></small>';
	$graph		= $indicator->build( $project->status + 4, 9, 80 );
	$status		= $words['states'][$project->status];
	$cells[]	= UI_HTML_Tag::create( 'td', '<small>'.$status.'</small><br/>'.$graph );
	$cells[]	= UI_HTML_Tag::create( 'td', $link.'<br/>'.$desc );
	$cells[]	= UI_HTML_Tag::create( 'td', join( ', ', $users ) );
#	$cells[]	= UI_HTML_Tag::create( 'td', $status, array( 'class' => 'project status'.$project->status ) );
	$cells[]	= UI_HTML_Tag::create( 'td', date( 'j.m.Y H:i', $project->createdAt ) );
	$cells[]	= UI_HTML_Tag::create( 'td', $project->modifiedAt ? date( 'j.m.Y H:i', $project->modifiedAt ) : '-' );
	$rows[]		= UI_HTML_Tag::create( 'tr', join( $cells ), array( 'class' => count( $rows ) % 2 ? 'even' : 'odd' ) );
}
$heads		= UI_HTML_Elements::TableHeads( array( 'Status', 'Projekt', 'Teilnehmer', 'erstellt', 'geändert' ) );
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '10%', '35%', '25%', '15%', '15%' ) );
$list		= UI_HTML_Tag::create( 'table', $colgroup.$heads.join( $rows ) );

$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $words['index']['buttonAdd'], 'button add' );
if( !$canAdd )
	$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $words['index']['buttonAdd'], 'button add disabled', NULL, TRUE );
$panelList	= '
<fieldset>
	<legend class="icon list projects">Projekte</legend>
	'.$list.'
	'.$buttonAdd.'
</fieldset>';


return '
<div class="column-left-20">
	'.$panelFilter.'
</div>	
<div class="column-right-80">
	'.$panelList.'
</div>
<div class="column-clear"></div>
';

?>
