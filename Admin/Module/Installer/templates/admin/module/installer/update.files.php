<?php
if( !count( $files ) )
	return "";

$states	= array(
	-3	=> 'missing',
	-2	=> 'inaccessible',
	-1	=> 'protected',
	0	=> 'new',
	1	=> 'installed',
	2	=> 'changed',
	3	=> 'linked',
	4	=> 'foreign',
	5	=> 'refered',
);

$list	= array();
foreach( $files as $file ){
	$actions	= array();
	$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'files[]',
		'value'		=> base64_encode( json_encode( $file ) ),
		'class'		=> 'file-check',
		'checked'	=> in_array( $file->status, array( 0, 1, 2 ) ) ? 'checked' : NULL,
	) );
	if( in_array( $file->status, array( 3, 5 ) ) )
		$checkbox	= '';
	else if( $file->status === 2 ){
		$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
		$actions[]	= UI_HTML_Tag::create( 'a', 'diff', array( 'href' => $url, 'class' => 'layer-html' ) );
	}
	else if( !file_exists( $file->pathSource ) )
		$file->status	= -3;
	else if( !is_readable( $file->pathSource ) )
		$file->status	= -2;
	else if( !is_writable( $file->pathSource ) )
		$file->status	= -1;

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
	'checked'		=> 'checked',
	'data-state'	=> 1
) );

$colgroup	= UI_HTML_Elements::ColumnGroup( "3%", "12%", "10%", "60%", "15%" );
$heads		= UI_HTML_Elements::TableHeads( array( $checkAll, 'Typ', 'Status', 'Datei', 'Aktion' ) );
$thead		= UI_HTML_Tag::create( 'thead', $heads );
$tbody		= UI_HTML_Tag::create( 'tbody', $list, array( 'id' => 'file-rows' ) );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table module-update-files' ) );

return '
<fieldset id="panel-module-update-files" class="panel-files">
	<legend>Dateien</legend>
	<label>
		<input type="checkbox" id="input_update_files_show_unchanged" checked="checked"/>
		Dateien ohne Veränderung ausblenden
	</label>
	<div>
		'.$table.'
		<div class="panel-if-empty"><small class="muted"></small></div>
	</div>
</fieldset>
<style>
#panel-module-update-files div {
    max-height: 320px;
    overflow: scroll;
    overflow-x: hidden;
    overflow-y: auto;
    }
#panel-module-update-files div table tr.hidden {
	display: none;
    }
#panel-module-update-files div .panel-if-empty {}
</style>
<script>
function onToggleUpdateFilesWithoutChanges(event){
	var isChecked = $(this).prop("checked");
	var table = $("#panel-module-update-files table");
	var rows = table.find("tbody tr");
	var rowsLinked = rows.filter(".status-linked,.status-refered");
	isChecked ? rowsLinked.addClass("hidden") : rowsLinked.removeClass("hidden");
	var rowsHidden = rows.filter(".hidden");
	var rowsVisible = rows.not(rowsHidden);
	rowsVisible.size() ? table.show() : table.hide();
}
$(document).ready(function(){
	$("#input_update_files_show_unchanged").bind("change", onToggleUpdateFilesWithoutChanges );
	$("#input_update_files_show_unchanged").trigger("change");
});
</script>';
?>
