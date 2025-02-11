<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $attachments */
/** @var string $path */
/** @var int $page */
/** @var int $total */
/** @var int $limit */
/** @var string[] $files */
/** @var string[] $classes */

$iconEnable		= new Icon( 'toggle-on' );
$iconDisable	= new Icon( 'toggle-off' );
$iconRemove		= new Icon( 'trash' );

$w		= (object) $words['index'];
$list	= '<div class="alert alert-warning"><em class="muted">'.$w->noEntries.'</em></div><br/>';
if( count( $attachments ) ){
	$list	= [];
	foreach( $attachments as $attachment ){
		$buttonStatus	= HtmlTag::create( 'a', $iconEnable, [
			'href'	=> './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/1',
			'class'	=> 'btn btn-success not-btn-small',
			'title'	=> $w->buttonActivate
		] );
		if( $attachment->status )
			$buttonStatus	= HtmlTag::create( 'a', $iconDisable, [
				'href'	=> './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/0',
				'class'	=> 'btn btn-danger',
				'title'	=> $w->buttonDeactivate
			] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> './admin/mail/attachment/unregister/'.$attachment->mailAttachmentId,
			'class'	=> 'btn btn-inverse',
			'title'	=> $w->buttonUnregister
		] );

		$language	= HtmlTag::create( 'span', $attachment->language, ['class' => 'label'] );
		$mimeType	= HtmlTag::create( 'span', $w->labelMimeType.' '.$attachment->mimeType );
		$fileSize	= HtmlTag::create( 'span', $w->labelFileSize.' '.UnitFormater::formatBytes( filesize( $path.$attachment->filename ) ) );
		$info		= HtmlTag::create( 'small', $fileSize.' | '.$mimeType, ['class' => 'muted'] );
		$label		= $language.' '.HtmlTag::create( 'big', $attachment->filename ).'<br/>'.$info;
		$status		= (object) array (
			'label'		=> $words['states'][(int) $attachment->status],
			'icon'		=> $attachment->status > 0 ? $iconEnable : $iconDisable,
			'class'		=> $attachment->status > 0 ? 'label label-success' : 'label label-warning',
		);
		$status		= HtmlTag::create( 'span', $status->icon.' '.$status->label,  ['class' => $status->class] );
		$date		= date( "d.m.Y", $attachment->createdAt );
		$time		= date( "H:i", $attachment->createdAt );
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $label ),
			HtmlTag::create( 'td', $attachment->className.'<br/>'.$status ),
			HtmlTag::create( 'td', $date.' <small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', HtmlTag::create( 'div', [$buttonStatus, $buttonRemove], ['class' => 'btn-group']) ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "", "140px", "80px" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$w->headFile,
		$w->headClass,
		$w->headCreatedAt,
		$w->headActions
	] ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

$pagination	= new PageControl( './admin/mail/attachment', $page, ceil( $total / $limit ) );

$modal	= new View_Admin_Mail_Attachment_Modal_Add( $env );
$modal->setClasses( $classes );
$modal->setFiles( $files );
$modal->render();

return '
<!-- templates/admin/mail/attachment/index.list.php -->
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
		'.$modal->trigger->render().'
		'.$pagination.'
		</div>
	</div>
</div>'.$modal;

