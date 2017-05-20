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
	$size			= Alg_UnitFormater::formatBytes( filesize( $pathBase.$folderPath.$file->title ) );
	$urlView		= './manage/download/view/'.$file->downloadFileId;
	$urlDownload	= './manage/download/download/'.$file->downloadFileId;
	$urlRemove		= './manage/download/remove/'.$file->downloadFileId;
	$class			= 'type type-'.pathinfo( $file->title, PATHINFO_EXTENSION );
	$underline		= $size.', '.$timePhrase.', '.$file->nrDownloads.' Downloads';
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
	$url	= './manage/download/index/'.$folder->downloadFolderId;
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
			'onclick'	=> 'ModuleManageDownloads.changeFolderName('.$folder->downloadFolderId.', \''.$folder->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonRename'],
		) );
	}
	if( in_array( 'rankTopic', $rights ) && count( $folders ) > 1 ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUp, array(
			'href'	=> './manage/download/rankFolder/'.$folder->downloadFolderId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconDown, array(
			'href'	=> './manage/download/rankFolder/'.$folder->downloadFolderId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
	}
	if( in_array( 'removeFolder', $rights ) && !$folder->nrFiles && !$folder->nrFolders ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './manage/download/removeFolder/'.$folder->downloadFolderId,
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
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';


$panels		= array();
if( 0 )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.info.php' );
if( in_array( 'upload', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.upload.php' );
if( in_array( 'addFolder', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.folder.php' );
if( in_array( 'scan', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.scan.php' );

$way		= '';
$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : array( '' );
$iconHome	= new \CeusMedia\Bootstrap\Icon( 'home', !$folderPath );
$buttonHome	= new \CeusMedia\Bootstrap\LinkButton( './manage/download/index', $iconHome );
if( !$folderPath )
	$buttonHome	= new \CeusMedia\Bootstrap\Button( $iconHome, 'btn-inverse', NULL, TRUE );
$buttons	= array( $buttonHome );
foreach( $steps as $nr => $stepFolder ){
	$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
	$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
	$url		= './manage/download/index/'.$stepFolder->downloadFolderId;
	$icon		= new \CeusMedia\Bootstrap\Icon( 'folder-open', $isCurrent );
	$class		= $isCurrent ? 'btn-inverse' : NULL;
	$buttons[]	= new \CeusMedia\Bootstrap\LinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
}
$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
$position->setClass( 'position-bar' );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/download/' ) );

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
