<?php

use CeusMedia\Bootstrap\Button;
use CeusMedia\Bootstrap\Button\Group as ButtonGroup;
use CeusMedia\Bootstrap\Button\Link as LinkButton;
use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array<object> $folders */
/** @var array<object> $files */
/** @var array<int|string,string> $words */
/** @var array<string> $rights */
/** @var array<int,object> $steps */
/** @var string $pathBase */
/** @var string $folderPath */
/** @var int|string $folderId */

$helper			= new View_Helper_TimePhraser( $env );
$iconOpenFolder	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconUp			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-up'] );
$iconDown		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-down'] );
$rows			= ['folders' => [], 'files' => []];

foreach( $files as $file ){
	$timePhrase		= sprintf( $words['index']['timePhrase'], $helper->convert( $file->uploadedAt ) );
	$size			= UnitFormater::formatBytes( filesize( $pathBase.$folderPath.$file->title ) );
	$urlView		= './manage/download/view/'.$file->downloadFileId;
	$urlDownload	= './manage/download/download/'.$file->downloadFileId;
	$urlRemove		= './manage/download/remove/'.$file->downloadFileId;
	$class			= 'type type-'.pathinfo( $file->title, PATHINFO_EXTENSION );
	$underline		= $size.', '.$timePhrase.', '.$file->nrDownloads.' Downloads';
	$underline		= HtmlTag::create( 'small', $underline, ['class' => "muted"] );
	$label			= $file->title;
	$label			= preg_replace( '/\.[a-z]+$/', '<small class="muted">\\0</small>', $label );
	$label			= $label.'<br/>'.$underline;

	$url			= in_array( 'view', $rights ) ? $urlView : $urlDownload;
	$label			= HtmlTag::create( 'a', $label, ['href' => $url, 'class' => 'name'] );
	$buttonView	= "";
	$buttonDownload	= "";
	$buttonRemove	= "";
	if( in_array( 'download', $rights ) ){
		$buttonDownload	= HtmlTag::create( 'a', $iconDownload, [
			'href'	=> $urlDownload,
			'class'	=> 'btn not-btn-small btn-primary',
			'title'	=> $words['index']['buttonDownload']
		] );
	}
	if( in_array( 'remove', $rights ) ){
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> $urlRemove,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		] );
	}
	$buttons		= $buttonDownload.'&nbsp;'.$buttonRemove;
	$actions		= HtmlTag::create( 'div', $buttonView.$buttonDownload.$buttonRemove, ['class' => 'btn-group pull-right'] );
//	$actions		= HtmlTag::create( 'div', $buttonDownload.'&nbsp;'.$buttonRemove, ['class' => 'pull-right'] );
	$cells			= array(
		HtmlTag::create( 'td', $label/*$link*/, ['class' => 'file'] ),
		HtmlTag::create( 'td', $actions ),
	);
	$row			= HtmlTag::create( 'tr', $cells, ['class' => $class] );
	$rows['files'][$file->title]		= $row;
}
ksort( $rows['files'] );

foreach( $folders as $folder ){
	$url	= './manage/download/index/'.$folder->downloadFolderId;
	$label	= $folder->title.'<br/>';
	$info	= HtmlTag::create( 'small', $folder->nrFiles.' Dateien und '.$folder->nrFolders.' Unterordner', ['class' => 'muted'] );
	$label	= HtmlTag::create( 'a', $label.$info, ['class' => 'name', 'href' => $url] );

	$buttons	= [];
	$buttons[]	= HtmlTag::create( 'a', $iconOpenFolder, [
		'href'	=> $url,
		'class'	=> 'btn not-btn-small btn-info',
		'title'	=> $words['index']['buttonOpenFolder']
	] );
	if( in_array( 'ajaxRenameFolder', $rights ) ){
		$buttons[]	= HtmlTag::create( 'button', $iconEdit, array(
			'onclick'	=> 'ModuleManageDownloads.changeFolderName('.$folder->downloadFolderId.', \''.$folder->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonRename'],
		) );
	}
	if( in_array( 'rankTopic', $rights ) && count( $folders ) > 1 ){
		$buttons[]	= HtmlTag::create( 'a', $iconUp, [
			'href'	=> './manage/download/rankFolder/'.$folder->downloadFolderId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		] );
		$buttons[]	= HtmlTag::create( 'a', $iconDown, [
			'href'	=> './manage/download/rankFolder/'.$folder->downloadFolderId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		] );
	}
	if( in_array( 'removeFolder', $rights ) && !$folder->nrFiles && !$folder->nrFolders ){
		$buttons[]	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> './manage/download/removeFolder/'.$folder->downloadFolderId,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove']
		] );
	}
	$actions	= HtmlTag::create( 'div', join( $buttons ), ['class' => 'btn-group pull-right'] );
//	$actions	= HtmlTag::create( 'div', $buttonOpen.'&nbsp'.$buttonRemove, ['class' => 'pull-right'] );
	$cells		= array(
		HtmlTag::create( 'td', $label, ['class' => 'folder'] ),
		HtmlTag::create( 'td', $actions ),
	);
	$row	= HtmlTag::create( 'tr', $cells, ['class' => 'info folder'] );
	$rows['folders'][$folder->title]	= $row;
}
//ksort( $rows['folders'] );
$rows	= $rows['folders'] + $rows['files'];

$table	= '<br/><p><em><small class="muted">'.$words['index']['empty'].'</small></em></p>';

if( $rows ){
	$colgroup	= HtmlElements::ColumnGroup( "85%", "15%" );
	$heads		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', $words['index']['headFile'] ),
		HtmlTag::create( 'th', $words['index']['headActions'], ['class' => 'pull-right'] ),
	) );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped not-table-condensed'] );
}
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';


$panels		= [];
if( 0 )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.info.php' );
if( in_array( 'upload', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.upload.php' );
if( in_array( 'addFolder', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.folder.php' );
if( in_array( 'scan', $rights ) )
	$panels[]	= $view->loadTemplateFile( 'manage/download/index.scan.php' );

$way		= '';
$parts		= $folderPath ? explode( "/", '/'.trim( $folderPath, " /\t" ) ) : [''];
$iconHome	= new Icon( 'home', !$folderPath );
$buttonHome	= new LinkButton( './manage/download/index', $iconHome );
if( !$folderPath )
	$buttonHome	= new Button( $iconHome, 'btn-inverse', NULL, TRUE );
$buttons	= [$buttonHome];
foreach( $steps as $nr => $stepFolder ){
	$way		.= strlen( $stepFolder->title ) ? $stepFolder->title.'/' : '';
	$isCurrent	= $folderId === (int) $stepFolder->downloadFolderId;
	$url		= './manage/download/index/'.$stepFolder->downloadFolderId;
	$icon		= new Icon( 'folder-open', $isCurrent );
	$class		= $isCurrent ? 'btn-inverse' : NULL;
	$buttons[]	= new LinkButton( $url, $stepFolder->title, $class, $icon, $isCurrent );
}
$position	= new ButtonGroup( $buttons );
$position->setClass( 'position-bar' );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/download/' ) );

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
