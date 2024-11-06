<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\Mail\Message\Part\Attachment as MailV2AttachmentPart;
use CeusMedia\Mail\Message\Part\HTML as MailV2HtmlPart;
use CeusMedia\Mail\Message\Part\InlineImage as MailV2InlineImagePart;
use CeusMedia\Mail\Message\Part\Text as MailV2TextPart;
use CeusMedia\Mail\Part\Attachment as MailV1AttachmentPart;
use CeusMedia\Mail\Part\HTML as MailV1HtmlPart;
use CeusMedia\Mail\Part\InlineImage as MailV1InlineImagePart;
use CeusMedia\Mail\Part\Text as MailV1TextPart;

/** @var Environment $env */
/** @var Entity_Mail $mail */
/** @var int $libraries */
/** @var array<array<string,string>> $words */

//$iconDownload		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconDownload		= new Icon( 'download' );
$iconView			= new Icon( 'eye' );
$iconFile			= new Icon( 'file' );

$list	= [
	'1-html'		=> '',
	'2-attachments'	=> [],
	'3-images'		=> [],
	'5-text'		=> '',
	'9-source'		=> '',
];

$message	= '';
if( !$mail->object )
	$message	= 'No mail object available.';
else if( !is_object( $mail->objectInstance ) )
	$message	= 'Mail object could not by parsed.';
else if( !isset( $mail->parts ) )
	$message	= 'Mail parts not provided';
else{

	try{
		$html			= '';
		$text			= '';
		$attachments	= [];
		$images			= [];

		if( $libraries & $mail->usedLibrary ){
			if( $mail->usedLibrary === Logic_Mail::LIBRARY_MAIL_V2 ){
				foreach( $mail->parts as $key => $part ){
					if( $part instanceof MailV2HtmlPart )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof MailV2TextPart )
						$text	= $part->getContent();
					else if( $part instanceof MailV2AttachmentPart )
						$attachments[]	= (object) [
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileATime'	=> $part->getFileATime(),
							'fileCTime'	=> $part->getFileCTime(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						];
					else if( $part instanceof MailV2InlineImagePart )
						$images[]	= (object) [
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						];
				}
			}
			else if( $mail->usedLibrary === Logic_Mail::LIBRARY_MAIL_V1 ){
				foreach( $mail->parts as $key => $part ){
	//				$this->env->getMessenger()->noteNotice( 'LIBRARY_MAIL1: '.get_class( $part ) );
					if( $part instanceof MailV1HtmlPart )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof MailV1TextPart )
						$text	= $part->getContent();
					else if( $part instanceof MailV1AttachmentPart )
						$attachments[]	= (object) [
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileATime'	=> $part->getFileATime(),
							'fileCTime'	=> $part->getFileCTime(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						];
					else if( $part instanceof MailV1InlineImagePart )
						$images[]	= (object) [
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						];
				}
			}
		}

		$parts	= [];

		if( $html ){																			//  realize HTML view if available
			$headingHtml	= HtmlTag::create( 'h4', 'HTML' );
			$displayHtml	= HtmlTag::create( 'iframe', '', [
				'src'			=> './admin/mail/queue/html/'.$mail->mailId,
				'frameborder'	=> '0',
				'style'			=> "width: 100%; height: 450px; border: 1px solid gray; border-radius: 2px;",
			] );
			$parts[]	= $headingHtml.$displayHtml;
		}

		//  realize plain text view if available
		if( $text ){
			$headingText	= HtmlTag::create( 'h4', 'Plain Text' );
			$displayText	= HtmlTag::create( 'pre', $text, [
				'style' => "width: 98%; height: 450px; border: 1px solid gray; overflow: auto; border-radius: 2px;",
			] );
			$parts[]	= $headingText.$displayText;
		}

		if( $attachments ){																		//  realize attachments view if available
			$list	= [];
			foreach( $attachments as $attachment ){
				$buttonDownload	= HtmlTag::create( 'a', $iconDownload.' speichern', [
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey.'/download',
					'class'	=> 'btn btn-small',
				] );
/*				$buttonView		= HtmlTag::create( 'a', $iconView.' öffnen', [
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey,
					'class'	=> 'btn btn-small',
				] );*/
				$buttons	= HtmlTag::create( 'div', [$buttonDownload], [
					'class'	=> 'btn-group',
				] );
				$date		= '';
				if( $attachment->fileMTime ){
					$date	= date( 'Y-m-d H:i:s', $attachment->fileMTime );
				}
				$link		= HtmlTag::create( 'a', $iconFile.' '.$attachment->fileName, [
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey,
				] );
				$list[]	= HtmlTag::create( 'tr', [
					HtmlTag::create( 'td', $link ),
					HtmlTag::create( 'td', $attachment->mimeType ),
					HtmlTag::create( 'td', UnitFormater::formatBytes( $attachment->fileSize ) ),
					HtmlTag::create( 'td', $date ),
					HtmlTag::create( 'td', $buttons, ['style' => 'text-align: right'] ),
				] );
			}
			$heads	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', 'Dateiname' ),
				HtmlTag::create( 'th', 'MIME-Type' ),
				HtmlTag::create( 'th', 'Dateigröße' ),
				HtmlTag::create( 'th', 'letzte Änderung' ),
				HtmlTag::create( 'th', '' ),
			], ['style' => 'background-color: rgba(255, 255, 255, 0.75);'] );
			$colgroup	= HtmlElements::ColumnGroup( '', '15%', '10%', '20%', '15%' );
			$thead	= HtmlTag::create( 'thead', $heads );
			$tbody	= HtmlTag::create( 'tbody', $list );
			$displayAttachments	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table not-table-condensed table-striped'] );
			$headingAttachments	= '<h4>Anhänge</h4>';
			$parts[]	= $headingAttachments.$displayAttachments;
		}

		if( $images ){																			//  realize inline images view if available
			$list = [];
			foreach( $images as $image ){
				$date		= '';
				if( $image->fileMTime ){
					$date	= date( 'Y-m-d H:i:s', $image->fileMTime );
				}
				$link		= HtmlTag::create( 'a', $iconFile.' '.$image->fileName, [
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$image->partKey,
				] );
				$list[]	= HtmlTag::create( 'tr', [
					HtmlTag::create( 'td', $link ),
					HtmlTag::create( 'td', $image->mimeType ),
					HtmlTag::create( 'td', UnitFormater::formatBytes( $image->fileSize ) ),
					HtmlTag::create( 'td', $date ),
					HtmlTag::create( 'td', '', ['style' => 'text-align: right'] ),
				] );
			}
			$heads	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', 'Dateiname' ),
				HtmlTag::create( 'th', 'MIME-Type' ),
				HtmlTag::create( 'th', 'Dateigröße' ),
				HtmlTag::create( 'th', 'letzte Änderung' ),
				HtmlTag::create( 'th', '' ),
			], ['style' => 'background-color: rgba(255, 255, 255, 0.75);'] );
			$colgroup	= HtmlElements::ColumnGroup( '', '15%', '10%', '20%', '15%' );
			$thead	= HtmlTag::create( 'thead', $heads );
			$tbody	= HtmlTag::create( 'tbody', $list );
			$displayImages	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table not-table-condensed table-striped'] );
			$headingImages	= '<h4>Eingebundene Bilder</h4>';
			$parts[]	= $headingImages.$displayImages;
		}

		$helperSource	= new View_Helper_Mail_View_Source( $env );
		$helperSource->setMailObjectInstance( $mail->objectInstance );
		$helperSource->setMode( View_Helper_Mail_View_Source::MODE_CONDENSED );
		$headingSource	= HtmlTag::create( 'h4', 'Source' );
		$valueSource	= htmlentities( $helperSource->render(), ENT_QUOTES, 'UTF-8' );
		$viewSource		= HtmlTag::create( 'pre', $valueSource, [
			'style'	=> "max-height: 300px; scroll-y: auto; overflow: auto",
		] );
		$parts[]	= $headingSource.$viewSource;

	}
	catch( Exception $e ){
		$message	= $e->getMessage();
		$parts		= [];
	}
}
return '
<div class="content-panel">
	<h4>'.$words['view-body']['heading'].'</h4>
	<div class="content-panel-inner">
		<b>'.$message.'</b>
		'.join( $parts ).'
	</div>
</div>';
