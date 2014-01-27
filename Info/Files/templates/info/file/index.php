<?php
extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

$helper			= new View_Helper_TimePhraser( $env );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down icon-white' ) );
$rows			= array();
$orderPrefix	= 'z_';

foreach( $files as $file ){
	$timePhrase		= sprintf( $words['index']['timePhrase'], $helper->convert( $file->timestamp ) );
	$size			= Alg_UnitFormater::formatBytes( filesize( $path.$file->name ) );
	$class			= 'type-'.pathinfo( $file->name, PATHINFO_EXTENSION );
	$underline		= $size.', '.$timePhrase.', '.$file->downloads.' Downloads';
	$underline		= UI_HTML_Tag::create( 'small', $underline, array( 'class' => "muted" ) );
	$label			= basename( $file->name );
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;
	$prefix			= str_repeat( '&nbsp;&nbsp;&nbsp;', substr_count( $file->name, '/' ) * 3 );
	$label			= $prefix.UI_HTML_Tag::create( 'span', $label, array( 'class' => 'name' ) );
	$buttonDownload	= "";
	$buttonRemove	= "";
	if( in_array( 'download', $rights ) ){
		$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload, array(
			'href'	=> './info/file/download/'.base64_encode( $file->name ),
			'class'	=> 'btn not-btn-small btn-primary',
			'title'	=> $words['index']['buttonDownload']
		) );
	}
	if( in_array( 'remove', $rights ) ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/remove/'.base64_encode( $file->name ),
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		) );
	}
	$buttons		= $buttonDownload.'&nbsp;'.$buttonRemove;
	$actions		= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'pull-right' ) );
	$cells			= array(
		UI_HTML_Tag::create( 'td', $label/*$link*/, array( 'class' => 'file' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$key			= str_repeat( $orderPrefix, substr_count( $file->name, '/' ) ).strtolower( $file->name );
	$rows[$key]		= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
}

foreach( $folders as $folder ){
	$prefix	= str_repeat( '&nbsp;&nbsp;&nbsp;', substr_count( $folder->name, '/' ) * 3 );
	$label	= str_replace( '/', ' / ', $folder->name );
	$label	= $prefix.UI_HTML_Tag::create( 'span', $label, array( 'class' => 'name' ) );

	$buttonRemove	= "";
	if( !$folder->files && in_array( 'removeFolder', $rights ) ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/removeFolder/'.base64_encode( $folder->name ),
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		) );
	}
	$actions	= UI_HTML_Tag::create( 'div', $buttonRemove, array( 'class' => 'btn-group pull-right' ) );
	$cells		= array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'folder' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$levels			= substr_count( $folder->name, '/' ) + 1;
	$key			= str_repeat( $orderPrefix, $levels ).strtolower( $folder->name );
	$rows[$key]		= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => 'info folder' ) );
}
ksort( $rows );
$colgroup	= UI_HTML_Elements::ColumnGroup( "85%", "15%" );
$heads		= UI_HTML_Elements::TableHeads( array( 
	$words['index']['headFile'],
	$words['index']['headActions']
) );
$thead		= UI_HTML_Tag::create( 'thead', $heads );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed' ) );

$optFolder	= array( '.' => '- hier -' );
foreach( $folders as $folder )
	$optFolder[$folder->name]	= $folder->name;
$optFolder	= UI_HTML_Elements::Options( $optFolder );

$panelUpload	= $view->loadTemplateFile( 'info/file/index.upload.php', array( 'optFolder' => $optFolder ) );
$panelAddFolder	= $view->loadTemplateFile( 'info/file/index.folder.php', array( 'optFolder' => $optFolder ) );
$panelInfo		= $view->loadTemplateFile( 'info/file/index.info.php' );

return $textIndexTop.'
<h3>Dateien</h3>
<div class="row-fluid">
	<div class="span9">
		'.$table.'
	</div>
	<div class="span3">
		'.$panelInfo.'
		<br/>
		'.$panelUpload.'
		<br/>
		'.$panelAddFolder.'
	</div>
</div>
'.$textIndexBottom;
?>