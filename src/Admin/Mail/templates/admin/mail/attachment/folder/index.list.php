<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Modal\Dialog as ModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as ModalTrigger;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string[] $folders */
/** @var string[] $files */
/** @var string $basePath */
/** @var string[] $paths */
/** @var string $selectedPath */

$iconRemove		= new Icon( 'remove' );
$iconUpload		= new Icon( 'folder-open' );
$iconDownload	= new Icon( 'download' );
$iconFolder		= new Icon( 'folder' );
$iconFile		= new Icon( 'page' );
$iconUp			= new Icon( 'arrow-left' );
$iconCancel		= new Icon( 'arrow-left' );

$w				= (object) $words['upload'];

$maxSize		= UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize() );
$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setLabel( $iconUpload )->setRequired( TRUE )->setName( 'file' );

$baseLinkPath	= './admin/mail/attachment/folder';

$optPath	= ['' => ''];
foreach( $paths as $path )
	$optPath[$path]	= $path;
$optPath	= HtmlElements::Options( $optPath, rtrim( $selectedPath, '/' ) );

$modalBodyFileAdd	= '
<div class="row-fluid">
	<div class="span12">
		<label for="input_file">'.$w->labelFile.'</label>
		'.$helperUpload.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="hint">
			<small><em class="muted">'.sprintf( $w->hintMaxSize, $maxSize ).'</em></small>
		</div>
		<div class="hint">
			<small><em class="muted">'.$w->hintMimeType.'</em></small>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<label for="input_path">in Ordner</label>
		<select name="path" id="input_path" class="span12">'.$optPath.'</select>
	</div>
</div>';

$modalFileAdd	= ModalDialog::create( 'modal-file-add' )
	->setHeading( $w->heading )
	->setBody( $modalBodyFileAdd )
	->setFormAction( $baseLinkPath.'/upload' )
	->setFormIsUpload( TRUE )
	->setSubmitButtonLabel( 'hochladen' )
	->setSubmitButtonIconClass( 'check' )
	->setSubmitButtonClass( 'btn-primary' )
	->setCloseButtonLabel( 'abbrechen' )
	->setCloseButtonIconClass( 'arrow-left' );

$modalFileAddTrigger	= ModalTrigger::create( 'modal-trigger-file-add' )
	->setModalId( 'modal-file-add' )
	->setLabel( 'neue Datei' )
	->setClass( 'btn-primary' )
	->setIcon( 'plus' );


$modalBodyFolderAdd	= '
	<div class="row-fluid">
		<div class="span12">
			<label for="input_folder">Neuer Ordner</label>
			<input type="text" name="folder" id="input_folder" class="span12"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_path">in Ordner</label>
			<select name="path" id="input_path" class="span12">'.$optPath.'</select>
		</div>
	</div>';

$modalFolderAdd	= ModalDialog::create( 'modal-folder-add' )
	->setHeading( 'Neuer Ordner' )
	->setBody( $modalBodyFolderAdd )
	->setFormAction( $baseLinkPath.'/add' )
	->setSubmitButtonLabel( 'erzeugen' )
	->setSubmitButtonIconClass( 'check' )
	->setSubmitButtonClass( 'btn-primary' )
	->setCloseButtonLabel( 'abbrechen' )
	->setCloseButtonIconClass( 'arrow-left' );

$modalFolderAddTrigger	= ModalTrigger::create( 'modal-trigger-folder-add' )
	->setModalId( 'modal-folder-add' )
	->setLabel( 'neuer Ordner' )
	->setClass( 'btn-primary' )
	->setIcon( 'plus' );


$w	= (object) $words['index.files'];

$list		= [];
$buttonUp	= '';
if( $selectedPath ){
	$label	= Html::create( 'big', $iconUp.'&nbsp;..' );
	$target	= dirname( $selectedPath );
	$url	= $baseLinkPath;
	if( $target !== '.' )
		$url	.= '/'.base64_encode( $target );
	$link	= Html::create( 'a', $label, ['href' => $url] );
	$list[]	= Html::create( 'tr', [
		Html::create( 'td', $link ),
		Html::create( 'td', '' ),
	] );
	$buttonUp	= Html::create( 'a', $iconUp.'&nbsp;zurück', [
		'href'	=> $url,
		'class'	=> 'btn',
	] );
}
foreach( $folders as $folder ){
	$fileCode	= base64_encode( $selectedPath.$folder );
	$label		= Html::create( 'big', $iconFolder.'&nbsp;'.$folder );
	$link		= Html::create( 'a', $label, [
		'href'	=> $baseLinkPath.'/'.$fileCode,
	] );
	$buttonRemove	= Html::create( 'a', $iconRemove, [
		'href'		=> $baseLinkPath.'/remove/'.$fileCode,
		'class'		=> 'btn btn-small btn-danger',
	] );
	$buttons	= [$buttonRemove];
	$buttons	= Html::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
	$list[]		= Html::create( 'tr', [
		Html::create( 'td', $link ),
		Html::create( 'td', $buttons ),
	] );
}
if( $files ){
	foreach( $files as $fileName ){
		$label	= Html::create( 'big', $fileName );
		$fileCode		= base64_encode( $selectedPath.$fileName );
		$buttonRemove	= Html::create( 'a', $iconRemove, [
			'href'		=> $baseLinkPath.'/remove/'.$fileCode,
			'class'		=> 'btn btn-small btn-danger',
		] );
		$buttonDownload	= Html::create( 'a', $iconDownload, [
			'href'		=> $baseLinkPath.'/download/'.$fileCode,
			'class'		=> 'btn btn-small',
		] );

	//		$mimeType	= Html::create( 'span', $w->labelMimeType.': '.$file->mimeType );
		$fileSize	= Html::create( 'span', $w->labelFileSize.': '.UnitFormater::formatBytes( filesize( $basePath.$selectedPath.$fileName ) ) );
		$info		= Html::create( 'small', $fileSize/*.' | '.$mimeType*/, ['class' => 'muted'] );

		$buttons	= [$buttonDownload, $buttonRemove];
		$buttons	= Html::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
		$list[]		= Html::create( 'tr', [
			Html::create( 'td', $label.'<br/>'.$info ),
			Html::create( 'td', $buttons ),
		] );
	}
}
else{
	$list[]		= Html::create( 'tr', [
		Html::create( 'td', Html::create( 'div', $w->noEntries, ['class' => 'alert alert-warn'] ), ['colspan' => 2] ),
	] );
}
$colgroup	= HtmlElements::ColumnGroup( "", "60px" );
$thead		= Html::create( 'thead', '' );
$tbody		= Html::create( 'tbody', $list );
$table		= Html::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );

return '
<div class="content-panel">
	<h3>Dateien</h3>
	<div class="content-panel-inner">
		<div><strong>Pfad: /'.rtrim( $selectedPath, '/' ).'</strong></div>
		'.$table.'
		<div class="buttonbar">
			'.$buttonUp.'
			'.$modalFileAddTrigger.'
			'.$modalFolderAddTrigger.'
		</div>
	</div>
</div>'.$modalFileAdd.$modalFolderAdd;
