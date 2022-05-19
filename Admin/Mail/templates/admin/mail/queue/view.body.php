<?php

use \CeusMedia\Bootstrap\Icon;

//$iconDownload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconDownload		= new Icon( 'download' );
$iconView			= new Icon( 'eye' );
$iconFile			= new Icon( 'file' );

$list	= array(
	'1-html'		=> '',
	'2-attachments'	=> array(),
	'3-images'		=> array(),
	'5-text'		=> '',
	'9-source'		=> '',
);

$message	= '';
if( !$mail->object )
	$message	= 'No mail object available.';
else if( !is_object( $mail->object ) )
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
					if( $part instanceof \CeusMedia\Mail\Message\Part\HTML )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Message\Part\Text )
						$text	= $part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Message\Part\Attachment )
						$attachments[]	= (object) array(
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileATime'	=> $part->getFileATime(),
							'fileCTime'	=> $part->getFileCTime(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						);
					else if( $part instanceof \CeusMedia\Mail\Message\Part\InlineImage )
						$images[]	= (object) array(
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						);
				}
			}
			else if( $mail->usedLibrary === Logic_Mail::LIBRARY_MAIL_V1 ){
				foreach( $mail->parts as $key => $part ){
	//				$this->env->getMessenger()->noteNotice( 'LIBRARY_MAIL1: '.get_class( $part ) );
					if( $part instanceof \CeusMedia\Mail\Part\HTML )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Part\Text )
						$text	= $part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Part\Attachment )
						$attachments[]	= (object) array(
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileATime'	=> $part->getFileATime(),
							'fileCTime'	=> $part->getFileCTime(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						);
					else if( $part instanceof \CeusMedia\Mail\Part\InlineImage )
						$images[]	= (object) array(
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'fileMTime'	=> $part->getFileMTime(),
							'mimeType'	=> $part->getMimeType(),
						);
				}
			}
			else if( $mail->usedLibrary === Logic_Mail::LIBRARY_COMMON ){
				foreach( $mail->parts as $key => $part ){
	//				$this->env->getMessenger()->noteNotice( 'LIBRARY_COMMON: '.get_class( $part ) );
	//				$this->env->getMessenger()->noteNotice( 'TYPE: '.$part->getMimeType() );
					if( $part instanceof Net_Mail_Body ){
						if( $part->getMimeType() == 'text/html' ){
							$html	= TRUE;//$part->getContent();
						}
						else if( $part->getMimeType() == 'text/plain' )
							$text	= $part->getContent();
							if( $part->getContentEncoding() === "base64" )
								$text	= base64_decode( $text );
							if( $part->getContentEncoding() === "quoted-printable" ){
								$text	= str_replace( Net_Mail::$delimiter, '', $text );
								$text	= quoted_printable_decode( $text );
							}
					}
					else if( $part instanceof Net_Mail_Attachment ){
						$attachments[]	= (object) array(
							'partKey'	=> $key,
							'fileName'	=> $part->getFileName(),
							'mimeType'	=> $part->getMimeType(),
						);
					}
				}
			}
		}

		$parts	= [];

		if( $html ){																			//  realize HTML view if available
			$headingHtml	= UI_HTML_Tag::create( 'h4', 'HTML' );
			$displayHtml	= UI_HTML_Tag::create( 'iframe', '', array(
				'src'			=> './admin/mail/queue/html/'.$mail->mailId,
				'frameborder'	=> '0',
				'style'			=> "width: 100%; height: 450px; border: 1px solid gray; border-radius: 2px;",
			) );
			$parts[]	= $headingHtml.$displayHtml;
		}

		//  realize plain text view if available
		if( $text ){
			$headingText	= UI_HTML_Tag::create( 'h4', 'Plain Text' );
			$displayText	= UI_HTML_Tag::create( 'pre', $text, array(
				'style' => "width: 98%; height: 450px; border: 1px solid gray; overflow: auto; border-radius: 2px;",
			) );
			$parts[]	= $headingText.$displayText;
		}

		if( $attachments ){																		//  realize attachments view if available
			$list	= [];
			foreach( $attachments as $attachment ){
				$buttonDownload	= UI_HTML_Tag::create( 'a', $iconDownload.' speichern', array(
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey.'/download',
					'class'	=> 'btn btn-small',
				) );
/*				$buttonView		= UI_HTML_Tag::create( 'a', $iconView.' öffnen', array(
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey,
					'class'	=> 'btn btn-small',
				) );*/
				$buttons	= UI_HTML_Tag::create( 'div', array( $buttonDownload ), array(
					'class'	=> 'btn-group',
				) );
				$date		= '';
				if( $attachment->fileMTime ){
					$date	= date( 'Y-m-d H:i:s', $attachment->fileMTime );
				}
				$link		= UI_HTML_Tag::create( 'a', $iconFile.' '.$attachment->fileName, array(
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$attachment->partKey,
				) );
				$list[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $link ),
					UI_HTML_Tag::create( 'td', $attachment->mimeType ),
					UI_HTML_Tag::create( 'td', Alg_UnitFormater::formatBytes( $attachment->fileSize ) ),
					UI_HTML_Tag::create( 'td', $date ),
					UI_HTML_Tag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
				) );
			}
			$heads	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'th', 'Dateiname' ),
				UI_HTML_Tag::create( 'th', 'MIME-Type' ),
				UI_HTML_Tag::create( 'th', 'Dateigröße' ),
				UI_HTML_Tag::create( 'th', 'letzte Änderung' ),
				UI_HTML_Tag::create( 'th', '' ),
			), array( 'style' => 'background-color: rgba(255, 255, 255, 0.75);' ) );
			$colgroup	= UI_HTML_Elements::ColumnGroup( '', '15%', '10%', '20%', '15%' );
			$thead	= UI_HTML_Tag::create( 'thead', $heads );
			$tbody	= UI_HTML_Tag::create( 'tbody', $list );
			$displayAttachments	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table not-table-condensed table-striped' ) );
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
				$link		= UI_HTML_Tag::create( 'a', $iconFile.' '.$image->fileName, array(
					'href'	=> './admin/mail/queue/attachment/'.$mail->mailId.'/'.$image->partKey,
				) );
				$list[]	= UI_HTML_Tag::create( 'tr', array(
					UI_HTML_Tag::create( 'td', $link ),
					UI_HTML_Tag::create( 'td', $image->mimeType ),
					UI_HTML_Tag::create( 'td', Alg_UnitFormater::formatBytes( $image->fileSize ) ),
					UI_HTML_Tag::create( 'td', $date ),
					UI_HTML_Tag::create( 'td', '', array( 'style' => 'text-align: right' ) ),
				) );
			}
			$heads	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'th', 'Dateiname' ),
				UI_HTML_Tag::create( 'th', 'MIME-Type' ),
				UI_HTML_Tag::create( 'th', 'Dateigröße' ),
				UI_HTML_Tag::create( 'th', 'letzte Änderung' ),
				UI_HTML_Tag::create( 'th', '' ),
			), array( 'style' => 'background-color: rgba(255, 255, 255, 0.75);' ) );
			$colgroup	= UI_HTML_Elements::ColumnGroup( '', '15%', '10%', '20%', '15%' );
			$thead	= UI_HTML_Tag::create( 'thead', $heads );
			$tbody	= UI_HTML_Tag::create( 'tbody', $list );
			$displayImages	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table not-table-condensed table-striped' ) );
			$headingImages	= '<h4>Eingebundene Bilder</h4>';
			$parts[]	= $headingImages.$displayImages;
		}

		$helperSource	= new View_Helper_Mail_View_Source( $env );
		$helperSource->setMailObjectInstance( $mail->object->instance );
		$helperSource->setMode( View_Helper_Mail_View_Source::MODE_CONDENSED );
		$headingSource	= UI_HTML_Tag::create( 'h4', 'Source' );
		$valueSource	= htmlentities( $helperSource->render(), ENT_QUOTES, 'UTF-8' );
		$viewSource		= UI_HTML_Tag::create( 'pre', $valueSource, array(
			'style'	=> "max-height: 300px; scroll-y: auto; overflow: auto",
		) );
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
?>
