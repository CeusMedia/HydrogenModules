<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	@todo		after all Hydra instance are updated
 *	@todo		- remove manual rendering
 *	@todo		- remove style at bottom
 *	@todo		- remove script at bottom
 */
if( class_exists( 'View_Helper_Module_Files' ) ){
	$helper	= new View_Helper_Module_Files( $this->env );
	$table	= $helper->render( $files, $words );
}
else{
	if( !count( $files ) )
		return "";

	$states	= [
		-3	=> 'missing',
		-2	=> 'inaccessible',
		-1	=> 'protected',
		0	=> 'new',
		1	=> 'installed',
		2	=> 'changed',
		3	=> 'linked',
		4	=> 'foreign',
		5	=> 'refered',
	];

	$list	= [];
	foreach( $files as $file ){
		$actions	= [];
		$checkbox	= HtmlTag::create( 'input', NULL, array(
			'type'		=> 'checkbox',
			'name'		=> 'files[]',
			'value'		=> base64_encode( json_encode( $file ) ),
			'class'		=> 'file-check',
			'checked'	=> in_array( $file->status, [0, 1, 2] ) ? 'checked' : NULL,
		) );
		if( in_array( $file->status, [3, 5] ) )
			$checkbox	= '';
		else if( $file->status === 2 ){
			$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
			$actions[]	= HtmlTag::create( 'a', 'diff', ['href' => $url, 'class' => 'layer-html'] );
		}
		else if( !file_exists( $file->pathSource ) )
			$file->status	= -3;
		else if( !is_readable( $file->pathSource ) )
			$file->status	= -2;
		else if( !is_writable( $file->pathSource ) )
			$file->status	= -1;

		$statusLabel	= $words['update-file-states'][$file->status];
		$statusDesc		= $words['update-file-state-description'][$file->status];
		$status	= HtmlTag::create( 'acronym', $statusLabel, ['title' => $statusDesc] );
		$cells	= array(
			HtmlTag::create( 'td', $checkbox, ['class' => 'cell-check'] ),
			HtmlTag::create( 'td', $words['file-types'][$file->typeKey], ['class' => 'cell-type'] ),
			HtmlTag::create( 'td', $status, ['class' => 'cell-state'] ),
			HtmlTag::create( 'td', $file->name, ['class' => 'cell-name'] ),
			HtmlTag::create( 'td', join( " ", $actions ), ['class' => 'cell-actions'] ),
		);
		$list[]	= HtmlTag::create( 'tr', $cells, [
			'class'	=> 'status-'.$states[$file->status],
			'data-file-source'	=> $file->pathSource,
			'data-file-local'	=> $file->pathLocal
		] );
	}

	$checkAll	= HtmlTag::create( 'input', NULL, array(
		'type'			=> 'checkbox',
		'onchange'		=> 'AdminModuleUpdater.switchAllFiles()',
		'id'			=> 'btn_switch_files',
		'checked'		=> 'checked',
		'data-state'	=> 1
	) );

	$colgroup	= HtmlElements::ColumnGroup( "3%", "12%", "10%", "60%", "15%" );
	$heads		= HtmlElements::TableHeads( [$checkAll, 'Typ', 'Status', 'Datei', 'Aktion'] );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $list, ['id' => 'file-rows'] );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table module-update-files'] );
}

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
$(document).ready(function(){
	if(typeof typeof AdminModuleInstaller.toggleUpdateFilesWithoutChanges === "undefined"){
		function onToggleUpdateFilesWithoutChanges(event){
			var isChecked = $(this).prop("checked");
			var table = $("#panel-module-update-files table");
			var rows = table.find("tbody tr");
			var rowsLinked = rows.filter(".status-linked,.status-refered");
			isChecked ? rowsLinked.addClass("hidden") : rowsLinked.removeClass("hidden");
			var rowsHidden = rows.filter(".hidden");
			var rowsVisible = rows.not(rowsHidden);
			rowsVisible.length ? table.show() : table.hide();
		}
		$("#input_update_files_show_unchanged").on("change", onToggleUpdateFilesWithoutChanges );
		$("#input_update_files_show_unchanged").trigger("change");
	}
});
</script>';
?>
