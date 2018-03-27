<?php

$helper			= new View_Helper_TimePhraser( $env );
$iconOpenFolder	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconUp			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-up' ) );
$iconDown		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-down' ) );
$rows			= array( 'folders' => array(), 'files' => array() );

foreach( $files as $file ){
	$timePhrase		= sprintf( $words['index']['timePhrase'], $helper->convert( $file->uploadedAt ) );
	$size			= Alg_UnitFormater::formatBytes( $file->size );
	$urlView		= './info/file/deliver/'.$file->downloadFileId;
	$urlDownload	= './info/file/download/'.$file->downloadFileId;
	$urlRemove		= './info/file/remove/'.$file->downloadFileId;
	$class			= 'type type-'.pathinfo( $file->title, PATHINFO_EXTENSION );
	$downloads		= $file->nrDownloads > 2 ? ', '.$file->nrDownloads.' Downloads' : '';
	$underline		= $size.', '.$timePhrase/*.$downloads*/;
	$underline		= UI_HTML_Tag::create( 'small', $underline, array( 'class' => "muted" ) );
	$label			= $file->title;
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;

	$url			= in_array( 'view', $rights ) ? $urlView : $urlDownload;
	$label			= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'name' ) );
	$buttonView	= "";
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
	$actions		= UI_HTML_Tag::create( 'div', $buttonView.$buttonDownload.$buttonRemove, array( 'class' => 'btn-group pull-right' ) );
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
	if( $folder->nrFiles && $folder->nrFolders )
		$info	= UI_HTML_Tag::create( 'small', $folder->nrFiles.' Dateien und '.$folder->nrFolders.' Unterordner', array( 'class' => 'muted' ) );
	else if( $folder->nrFiles )
		$info	= UI_HTML_Tag::create( 'small', $folder->nrFiles.' Dateien', array( 'class' => 'muted' ) );
	else if( $folder->nrFolders )
		$info	= UI_HTML_Tag::create( 'small', $folder->nrFolders.' Unterordner', array( 'class' => 'muted' ) );
	else
		$info	= '';
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


if( $rows ){
	$colgroup	= UI_HTML_Elements::ColumnGroup( "85%", "15%" );
	$heads		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', $words['index'][( $search ? 'headFiles' : 'headFilesAndFolders' )] ),
		UI_HTML_Tag::create( 'th', $words['index']['headActions'], array( 'class' => 'pull-right' ) ),
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed' ) );
	$panelList	= '
	<div class="content-panel">
		<div class="content-panel-inner">
			'.$table.'
		</div>
	</div>';
}
else
	$panelList	= '<br/><div class="alert alert-info"><em class="not-muted">'.$words['index']['empty'].'</em></div>';



$panels		= array();
if( 1 )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.search.php' );
if( !in_array( 'upload', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.info.php' );
if( in_array( 'upload', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.upload.php' );
if( in_array( 'addFolder', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.folder.php' );
if( in_array( 'scan', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'info/file/index.scan.php' );

$way		= '';
$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : array( '' );
$iconHome	= new \CeusMedia\Bootstrap\Icon( 'fa fa-fw fa-home', !$folderPath );
$buttonHome	= new \CeusMedia\Bootstrap\LinkButton( './info/file/index', $iconHome );
if( !$folderPath && !$search )
	$buttonHome	= new \CeusMedia\Bootstrap\Button( $iconHome, 'btn-inverse', NULL, TRUE );
$buttons	= array( $buttonHome );
foreach( $steps as $nr => $stepFolder ){
	$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
	$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
	$url		= './info/file/index/'.$stepFolder->downloadFolderId;
	$icon		= new \CeusMedia\Bootstrap\Icon( 'fa fa-fw fa-folder-open', $isCurrent );
	$class		= $isCurrent ? 'btn-inverse' : NULL;
	$buttons[]	= new \CeusMedia\Bootstrap\LinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
}
$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
$position->setClass( 'position-bar' );


extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

return $textIndexTop.'
<!--<h3>Dateien</h3>-->
<div>'.$position.'</div><br/>
<div class="row-fluid">
	<div class="span9">
		'.$panelList.'
	</div>
	<div class="span3">
		'.join( '<hr/>', $panels ).'
	</div>
</div>
'.$textIndexBottom;
?>
