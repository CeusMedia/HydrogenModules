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

$w		= (object) $words['index'];

foreach( $files as $item ){
	$timePhrase		= sprintf( $w->timePhrase, $helper->convert( $item->uploadedAt ) );
	$size			= Alg_UnitFormater::formatBytes( $item->size );
	$urlView		= './info/file/deliver/'.$item->downloadFileId;
	$urlDownload	= './info/file/download/'.$item->downloadFileId;
	$urlRemove		= './info/file/remove/'.$item->downloadFileId;
	$class			= 'type type-'.pathinfo( $item->title, PATHINFO_EXTENSION );
	$downloads		= $item->nrDownloads > 2 ? ', '.$item->nrDownloads.' Downloads' : '';
	$underline		= $size.', '.$timePhrase/*.$downloads*/;
	$underline		= UI_HTML_Tag::create( 'small', $underline, array( 'class' => "muted" ) );
	$label			= $item->title;
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;

	$url			= in_array( 'view', $rights ) ? $urlView : $urlDownload;
	$label			= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'name' ) );
	$buttons		= [];
	if( in_array( 'download', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconDownload, array(
			'href'	=> $urlDownload,
			'class'	=> 'btn not-btn-small btn-primary',
			'title'	=> $w->buttonDownload
		) );
	}
	if( in_array( 'editFile', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'	=> './info/file/editFile/'.$item->downloadFileId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $w->buttonEdit,
		) );
	}
	if( in_array( 'remove', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> $urlRemove,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $w->buttonRemove
		) );
	}
	$actions		= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
//	$actions		= UI_HTML_Tag::create( 'div', $buttonDownload.'&nbsp;'.$buttonRemove, array( 'class' => 'pull-right' ) );
	$cells			= array(
		UI_HTML_Tag::create( 'td', $label/*$link*/, array( 'class' => 'file' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$row			= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
	$rows['files'][$item->title]		= $row;
}
ksort( $rows['files'] );

foreach( $folders as $item ){
	$url	= './info/file/index/'.$item->downloadFolderId;
	$label	= $item->title.'<br/>';
	if( $item->nrFiles && $item->nrFolders )
		$info	= UI_HTML_Tag::create( 'small', $item->nrFiles.' Dateien und '.$item->nrFolders.' Unterordner', array( 'class' => 'muted' ) );
	else if( $item->nrFiles )
		$info	= UI_HTML_Tag::create( 'small', $item->nrFiles.' Dateien', array( 'class' => 'muted' ) );
	else if( $item->nrFolders )
		$info	= UI_HTML_Tag::create( 'small', $item->nrFolders.' Unterordner', array( 'class' => 'muted' ) );
	else
		$info	= '<small class="muted">leer</small>';
	$label	= UI_HTML_Tag::create( 'a', $label.$info, array( 'class' => 'name', 'href' => $url ) );

	$buttons	= [];
	$buttons[]	= UI_HTML_Tag::create( 'a', $iconOpenFolder, array(
		'href'	=> $url,
		'class'	=> 'btn not-btn-small btn-info',
		'title'	=> $w->buttonOpenFolder
	) );
	if( 0 && in_array( 'ajaxRenameFolder', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'button', $iconEdit, array(
			'onclick'	=> 'InfoFile.changeFolderName('.$item->downloadFolderId.', \''.$item->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $w->buttonRename,
		) );
	}
	if( in_array( 'rankTopic', $rights ) && count( $folders ) > 1 ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUp, array(
			'href'	=> './info/file/rankFolder/'.$item->downloadFolderId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $w->buttonUp,
		) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconDown, array(
			'href'	=> './info/file/rankFolder/'.$item->downloadFolderId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $w->buttonUp,
		) );
	}
	if( in_array( 'editFolder', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'	=> './info/file/editFolder/'.$item->downloadFolderId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $w->buttonEdit,
		) );
	}
	if( in_array( 'removeFolder', $rights ) && !$item->nrFiles && !$item->nrFolders ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/file/removeFolder/'.$item->downloadFolderId,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $w->buttonRemove
		) );
	}
	$actions	= UI_HTML_Tag::create( 'div', join( $buttons ), array( 'class' => 'btn-group pull-right' ) );
//	$actions	= UI_HTML_Tag::create( 'div', $buttonOpen.'&nbsp'.$buttonRemove, array( 'class' => 'pull-right' ) );
	$cells		= array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'folder' ) ),
		UI_HTML_Tag::create( 'td', $actions ),
	);
	$row	= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => 'info folder' ) );
	$rows['folders'][$item->title]	= $row;
}
//ksort( $rows['folders'] );
$rows	= $rows['folders'] + $rows['files'];


$table	= '<br/><div class="alert alert-info"><em class="not-muted">'.$w->empty.'</em></div>';
if( $rows ){
	$colgroup	= UI_HTML_Elements::ColumnGroup( "85%", "15%" );
	$heads		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'th', $search ? $w->headFiles : $w->headFilesAndFolders ),
		UI_HTML_Tag::create( 'th', $w->headActions, array( 'class' => 'pull-right' ) ),
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

$linkUp		= '';
if( !$search && $folderId && $folder->downloadFolderId > 0 ){
	$linkUp	= UI_HTML_Tag::create( 'a', $iconCancel.' '.$w->buttonBack, array(
		'href'		=> './info/file/index/'.$folder->parentId,
		'class'		=> 'btn btn-small',
	) );
}

$panels		= [];
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

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

return $textIndexTop.'
<!--<h3>Dateien</h3>-->
<div>'.View_Info_File::renderPosition( $env, $folderId, $search ).'</div><br/>
<div class="row-fluid">
	<div class="span9">
		'.$panelList.'
			'.$linkUp.'
	</div>
	<div class="span3">
		'.join( /*'<hr/>', */$panels ).'
	</div>
</div>
'.$textIndexBottom;
