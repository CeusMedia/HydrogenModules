<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Modal\Dialog as ModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as ModalTrigger;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
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
	->setClass( 'btn-success' )
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
	->setClass( 'btn-success' )
	->setIcon( 'plus' );


$w	= (object) $words['index.files'];

$rows		= [];
$buttonUp	= '';
if( $selectedPath ){
	$label	= HtmlTag::create( 'big', $iconUp.'&nbsp;..' );
	$target	= dirname( $selectedPath );
	$url	= $baseLinkPath;
	if( $target !== '.' )
		$url	.= '/'.base64_encode( $target );
	$link	= HtmlTag::create( 'a', $label, ['href' => $url] );
	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $link ),
		HtmlTag::create( 'td', '' ),
	] );
	$buttonUp	= HtmlTag::create( 'a', $iconUp.'&nbsp;zurück', [
		'href'	=> $url,
		'class'	=> 'btn',
	] );
}
foreach( $folders as $folder ){
	$fileCode	= base64_encode( $selectedPath.$folder );
	$label		= HtmlTag::create( 'big', $iconFolder.'&nbsp;'.$folder );
	$link		= HtmlTag::create( 'a', $label, [
		'href'	=> $baseLinkPath.'/'.$fileCode,
	] );
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
		'href'		=> $baseLinkPath.'/remove/'.$fileCode,
		'class'		=> 'btn btn-small btn-danger',
	] );
	$buttons	= [$buttonRemove];
	$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
	$rows[]		= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $link ),
		HtmlTag::create( 'td', $buttons ),
	] );
}
if( $folders || $files ){
	foreach( $files as $fileName ){
		$label	= HtmlTag::create( 'big', $fileName );
		$fileCode		= base64_encode( $selectedPath.$fileName );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> $baseLinkPath.'/remove/'.$fileCode,
			'class'		=> 'btn btn-small btn-danger',
		] );
		$buttonDownload	= HtmlTag::create( 'a', $iconDownload, [
			'href'		=> $baseLinkPath.'/download/'.$fileCode,
			'class'		=> 'btn btn-small',
		] );

	//		$mimeType	= HtmlTag::create( 'span', $w->labelMimeType.': '.$file->mimeType );
		$fileSize	= HtmlTag::create( 'span', $w->labelFileSize.': '.UnitFormater::formatBytes( filesize( $basePath.$selectedPath.$fileName ) ) );
		$info		= HtmlTag::create( 'small', $fileSize/*.' | '.$mimeType*/, ['class' => 'muted'] );

		$buttons	= [$buttonDownload, $buttonRemove];
		$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
		$rows[]		= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $label.'<br/>'.$info ),
			HtmlTag::create( 'td', $buttons ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "60px" );
	$thead		= HtmlTag::create( 'thead', '' );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}
else{
	$list	= HtmlTag::create( 'td', HtmlTag::create( 'div', $w->noEntries, ['class' => 'alert alert-warn'] ) );
}

return '
<div class="content-panel">
	<h3>Dateien</h3>
	<div class="content-panel-inner">
		<div><strong>Pfad: /'.rtrim( $selectedPath, '/' ).'</strong></div>
		'.$list.'
		<div class="buttonbar">
			'.$buttonUp.'
			'.$modalFileAddTrigger.'
			'.$modalFolderAddTrigger.'
		</div>
	</div>
</div>'.$modalFileAdd.$modalFolderAdd;
