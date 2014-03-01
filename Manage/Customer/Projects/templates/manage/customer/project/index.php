<?php

#print_m( $projects );
#die;

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId, 'project/'.$customerId );

$table		= '';
if( $projects ){
	$rows	= array();
	foreach( $projects as $project ){
		$url	= './manage/project/'.$project->projectId;
		$link	= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url ) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link )
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "100%" ); 
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel'
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

return '
<h3><span class="muted">Kunde</span> '.$customer->title.'</h3>
'.$tabs.'
'.$table.'

';