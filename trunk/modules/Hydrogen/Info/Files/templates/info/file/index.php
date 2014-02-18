<?php

$helper			= new View_Helper_TimePhraser( $env );
$iconOpenFolder	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down icon-white' ) );
$rows			= array( 'folders' => array(), 'files' => array() );

foreach( $files as $file ){
	$timePhrase		= sprintf( $words['index']['timePhrase'], $helper->convert( $file->timestamp ) );
	$size			= Alg_UnitFormater::formatBytes( filesize( $pathBase.$file->pathName ) );
	$class			= 'type type-'.pathinfo( $file->fileName, PATHINFO_EXTENSION );
	$underline		= $size.', '.$timePhrase.', '.$file->downloads.' Downloads';
	$underline		= UI_HTML_Tag::create( 'small', $underline, array( 'class' => "muted" ) );
	$label			= $file->fileName;
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;
	$label			= UI_HTML_Tag::create( 'span', $label, array( 'class' => 'name' ) );
	$buttonDownload	= "";
	$buttonRemove	= "";
	if( in_array( 'download', $rights ) ){
		$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload, array(
			'href'	=> './info/file/download/'.base64_encode( $file->pathName ),
			'class'	=> 'btn not-btn-small btn-primary',
			'title'	=> $words['index']['buttonDownload']
		) );
	}
	if( in_array( 'remove', $rights ) ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/remove/'.base64_encode( $file->pathName ),
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		) );
	}
	$buttons		= $buttonDownload.'&nbsp;'.$buttonRemove;
	$actions		= UI_HTML_Tag::create( 'div', $buttonDownload.$buttonRemove, array( 'class' => 'btn-group pull-right' ) );
//	$actions		= UI_HTML_Tag::create( 'div', $buttonDownload.'&nbsp;'.$buttonRemove, array( 'class' => 'pull-right' ) );
	$cells			= array(
		UI_HTML_Tag::create( 'td', $label/*$link*/, array( 'class' => 'file' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$row			= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
	$rows['files'][$file->fileName]		= $row;
}
ksort( $rows['files'] );

foreach( $folders as $folder ){
	$url	= './info/file/index/'.base64_encode( $folder->pathName );
	$label	= $folder->folderName;
	$label	= UI_HTML_Tag::create( 'a', $label, array( 'class' => 'name', 'href' => $url ) );

	$buttonOpen	= UI_HTML_Tag::create( 'a', $iconOpenFolder, array(
		'href'	=> './info/file/index/'.base64_encode( $folder->pathName ),
		'class'	=> 'btn not-btn-small btn-info',
		'title'	=> $words['index']['buttonOpenFolder']
	) );
	$buttonRemove	= "";
	if( !$folder->files && in_array( 'removeFolder', $rights ) ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/removeFolder/'.base64_encode( $folder->pathName ),
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		) );
	}
	$actions	= UI_HTML_Tag::create( 'div', $buttonOpen.$buttonRemove, array( 'class' => 'btn-group pull-right' ) );
//	$actions	= UI_HTML_Tag::create( 'div', $buttonOpen.'&nbsp'.$buttonRemove, array( 'class' => 'pull-right' ) );
	$cells		= array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'folder' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$row	= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => 'info folder' ) );
	$rows['folders'][$folder->pathName]		= $row;
}
ksort( $rows['folders'] );
$rows	= $rows['folders'] + $rows['files'];

$table	= '<br/><p><em><small class="muted">'.$words['index']['empty'].'</small></em></p>';

if( $rows ){
	$colgroup	= UI_HTML_Elements::ColumnGroup( "85%", "15%" );
	$heads		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', $words['index']['headFile'] ),
		UI_HTML_Tag::create( 'th', $words['index']['headActions'], array( 'class' => 'pull-right' ) ),
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed' ) );
}

$panelUpload	= $view->loadTemplateFile( 'info/file/index.upload.php' );
$panelAddFolder	= $view->loadTemplateFile( 'info/file/index.folder.php' );
$panelInfo		= $view->loadTemplateFile( 'info/file/index.info.php' );

$way		= '';
$parts		= $path ? explode( "/", '/'.trim( $path, " /\t" ) ) : array( '' );
$iconHome	= new CMM_Bootstrap_Icon( 'home', !$path );
$buttonHome	= new CMM_Bootstrap_LinkButton( './info/file/index', $iconHome );
if( !$path )
	$buttonHome	= new CMM_Bootstrap_Button( $iconHome, 'btn-inverse', NULL, TRUE );
$buttons	= array( $buttonHome );
foreach( $parts as $nr => $part ){
	if( strlen( $part ) ){
		$way		.= strlen( $part ) ? $part.'/' : '';
		$isCurrent	= ( $path === $way );
		$isCurrent	= count( $parts ) === $nr + 1;
		$url		= './info/file/index/'.base64_encode( $way );
		$icon		= new CMM_Bootstrap_Icon( 'folder-open', $isCurrent );
		$class		= $isCurrent ? 'btn-inverse' : NULL;
		$buttons[]	= new CMM_Bootstrap_LinkButton( $url, $part, $class, $icon, $isCurrent );
	}
}
$position	= new CMM_Bootstrap_ButtonGroup( $buttons );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

return $textIndexTop.'
<!--<h3>Dateien</h3>-->
<div>'.$position.'</div>
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
