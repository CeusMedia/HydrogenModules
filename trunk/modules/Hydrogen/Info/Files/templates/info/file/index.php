<?php

$helper			= new View_Helper_TimePhraser( $env );
$iconOpenFolder	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down icon-white' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconUp			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-up' ) );
$iconDown		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );
$rows			= array( 'folders' => array(), 'files' => array() );

foreach( $files as $file ){
	$timePhrase		= sprintf( $words['index']['timePhrase'], $helper->convert( $file->uploadedAt ) );
	$size			= Alg_UnitFormater::formatBytes( filesize( $pathBase.$folderPath.$file->title ) );
	$urlDownload	= './info/file/download/'.$file->downloadFileId;
	$urlRemove		= './info/file/remove/'.$file->downloadFileId;
	$class			= 'type type-'.pathinfo( $file->title, PATHINFO_EXTENSION );
	$underline		= $size.', '.$timePhrase.', '.$file->nrDownloads.' Downloads';
	$underline		= UI_HTML_Tag::create( 'small', $underline, array( 'class' => "muted" ) );
	$label			= $file->title;
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;
	$label			= UI_HTML_Tag::create( 'a', $label, array( 'href' => $urlDownload, 'class' => 'name' ) );
	$buttonDownload	= "";
	$buttonRemove	= "";
	if( in_array( 'download', $rights ) ){
		$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload, array(
			'href'	=> $urlDownload,
			'class'	=> 'btn not-btn-small btn-primary',
			'title'	=> $words['index']['buttonDownload']
		) );
	}
	if( in_array( 'remove', $rights ) ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> $urlRemove,
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
	$rows['files'][$file->title]		= $row;
}
ksort( $rows['files'] );

foreach( $folders as $folder ){
	$url	= './info/file/index/'.$folder->downloadFolderId;
	$label	= $folder->title.'<br/>';
	$info	= UI_HTML_Tag::create( 'small', $folder->nrFiles.' Dateien und '.$folder->nrFolders.' Unterordner', array( 'class' => 'muted' ) );
	$label	= UI_HTML_Tag::create( 'a', $label.$info, array( 'class' => 'name', 'href' => $url ) );

	$buttons	= array();
	$buttons[]	= UI_HTML_Tag::create( 'a', $iconOpenFolder, array(
		'href'	=> $url,
		'class'	=> 'btn not-btn-small btn-info',
		'title'	=> $words['index']['buttonOpenFolder']
	) );
	if( in_array( 'ajaxRenameFolder', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'button', $iconEdit, array(
			'onclick'	=> 'InfoFile.changeFolderName('.$folder->downloadFolderId.', \''.$folder->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonRename'],
		) );
	}
	if( in_array( 'rankTopic', $rights ) && count( $folders ) > 1 ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUp, array(
			'href'	=> './info/file/rankFolder/'.$folder->downloadFolderId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconDown, array(
			'href'	=> './info/file/rankFolder/'.$folder->downloadFolderId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
	}
	if( in_array( 'removeFolder', $rights ) && !$folder->nrFiles && !$folder->nrFolders ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/removeFolder/'.$folder->downloadFolderId,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		) );
	}
	$actions	= UI_HTML_Tag::create( 'div', join( $buttons ), array( 'class' => 'btn-group pull-right' ) );
//	$actions	= UI_HTML_Tag::create( 'div', $buttonOpen.'&nbsp'.$buttonRemove, array( 'class' => 'pull-right' ) );
	$cells		= array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'folder' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$row	= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => 'info folder' ) );
	$rows['folders'][$folder->title]	= $row;
}
//ksort( $rows['folders'] );
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

$panels		= array();
if( 0 )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.info.php' );
if( in_array( 'upload', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.upload.php' );
if( in_array( 'addFolder', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.folder.php' );
if( in_array( 'scan', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.scan.php' );

$way		= '';
$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : array( '' );
$iconHome	= new CMM_Bootstrap_Icon( 'home', !$folderPath );
$buttonHome	= new CMM_Bootstrap_LinkButton( './info/file/index', $iconHome );
if( !$folderPath )
	$buttonHome	= new CMM_Bootstrap_Button( $iconHome, 'btn-inverse', NULL, TRUE );
$buttons	= array( $buttonHome );
foreach( $steps as $nr => $stepFolder ){
	$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
	$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
	$url		= './info/file/index/'.$stepFolder->downloadFolderId;
	$icon		= new CMM_Bootstrap_Icon( 'folder-open', $isCurrent );
	$class		= $isCurrent ? 'btn-inverse' : NULL;
	$buttons[]	= new CMM_Bootstrap_LinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
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
		'.join( '<hr/>', $panels ).'
	</div>
</div>
'.$textIndexBottom;
?>