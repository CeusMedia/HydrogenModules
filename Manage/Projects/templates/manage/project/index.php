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
			</ul>
			<div class="buttonbar">
				'.$buttonFilter.'
				'.$buttonReset.'
			</div>
		</fieldset>
	</form>
';


$rows	= array();
foreach( $projects as $project ){
	$cells		= array();
	$url		= './manage/project/edit/'.$project->projectId;
	$link		= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url ) );
	$users		= count( $project->users );
	$cells[]	= UI_HTML_Tag::create( 'td', $link );
	$cells[]	= UI_HTML_Tag::create( 'td', $users );
	$cells[]	= UI_HTML_Tag::create( 'td', $words['states'][$project->status] );
	$cells[]	= UI_HTML_Tag::create( 'td', date( 'j.m.Y H:i', $project->createdAt ) );
	$cells[]	= UI_HTML_Tag::create( 'td', $project->modifiedAt ? date( 'j.m.Y H:i', $project->modifiedAt ) : '-' );
	$rows[]		= UI_HTML_Tag::create( 'tr', join( $cells ), array( 'class' => 'project status'.$project->status ) );
}
$heads		= UI_HTML_Elements::TableHeads( array( 'Projekt', 'Teilnehmer', 'Status', 'erstellt', 'geändert' ) );
$colgroup	= UI_HTML_Elements::ColumnGroup( array( '40%', '15%', '15%', '15%', '15%' ) );
$list		= UI_HTML_Tag::create( 'table', $colgroup.$heads.join( $rows ) );

$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/project/add', $words['index']['buttonAdd'], 'button add' );
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
