<?php

#print_m( $files );
#die;

$states	= array(
	0	=> 'new',
	1	=> 'installed',
	2	=> 'linked',
	3	=> 'foreign',
	4	=> 'changed',
);

$list	= array();
foreach( $files as $file ){
	$actions	= array();
	$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'files[]',
		'value'		=> base64_encode( json_encode( $file ) ),
		'class'		=> 'file-check',
		'checked'	=> in_array( $file->status, array( 0, 1, 4 ) ) ? 'checked' : NULL,
	) );
	if( $file->status === 2 )
		$checkbox	= '';
	if( $file->status === 4 ){
		$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
		$actions[]	= UI_HTML_Tag::create( 'a', 'diff', array( 'href' => $url, 'class' => 'layer-html' ) );
	}

	$statusLabel	= $words['update-file-states'][$file->status];
	$statusDesc		= $words['update-file-state-description'][$file->status];
	$status	= UI_HTML_Tag::create( 'acronym', $statusLabel, array( 'title' => $statusDesc ) );
	$cells	= array(
		UI_HTML_Tag::create( 'td', $checkbox, array( 'class' => 'cell-check' ) ),
		UI_HTML_Tag::create( 'td', $words['file-types'][$file->typeKey], array( 'class' => 'cell-type' ) ),
		UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-state' ) ),
		UI_HTML_Tag::create( 'td', $file->name, array( 'class' => 'cell-name' ) ),
		UI_HTML_Tag::create( 'td', join( " ", $actions ), array( 'class' => 'cell-actions' ) ),
	);
	$list[]	= UI_HTML_Tag::create( 'tr', $cells, array(
		'class'	=> 'status-'.$states[$file->status],
		'data-file-source'	=> $file->pathSource,
		'data-file-local'	=> $file->pathLocal
	) );
}
$checkAll	= UI_HTML_Tag::create( 'input', NULL, array(
	'type'			=> 'checkbox',
	'onchange'		=> 'AdminModuleUpdater.switchAllFiles()',
	'id'			=> 'btn_switch_files',
	'data-state'	=> 0
) );

$colgroup	= UI_HTML_Elements::ColumnGroup( "3%", "12%", "10%", "60%", "15%" );
$heads		= UI_HTML_Elements::TableHeads( array( $checkAll, 'Typ', 'Status', 'Datei', 'Aktion' ) );
$thead		= UI_HTML_Tag::create( 'thead', $heads );
$tbody		= UI_HTML_Tag::create( 'tbody', $list, array( 'id' => 'file-rows' ) );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );

return '
<fieldset>
	<legend>Dateien</legend>
	'.$table.'
</fieldset>
';
?>