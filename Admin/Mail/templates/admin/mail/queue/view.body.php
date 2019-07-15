<?php

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
		$libraries		= Logic_Mail::detectAvailableMailLibraries();
		$usedLibrary	= Logic_Mail::detectMailLibraryFromMailObject( $mail->object );

		$html			= '';
		$text			= '';
		$attachments	= array();
		$images			= array();

		if( $libraries & $usedLibrary ){
			if( $usedLibrary === Logic_Mail::LIBRARY_COMMON ){
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
							'fileName'	=> $part->getFileName(),
							'mimeType'	=> $part->getMimeType(),
						);
					}
				}
			}
			if( $usedLibrary === Logic_Mail::LIBRARY_MAIL_V1 ){
				foreach( $mail->parts as $key => $part ){
	//				$this->env->getMessenger()->noteNotice( 'LIBRARY_MAIL1: '.get_class( $part ) );
					if( $part instanceof \CeusMedia\Mail\Part\HTML )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Part\Text )
						$text	= $part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Part\Attachment )
						$attachments[]	= (object) array(
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'mimeType'	=> $part->getMimeType(),
						);
					else if( $part instanceof \CeusMedia\Mail\Part\InlineImage )
						$images[]	= (object) array(
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'mimeType'	=> $part->getMimeType(),
						);
				}
			}
			if( $usedLibrary === Logic_Mail::LIBRARY_MAIL_V2 ){
				foreach( $mail->parts as $key => $part ){
					if( $part instanceof \CeusMedia\Mail\Message\Part\HTML )
						$html	= TRUE;//$part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Message\Part\Text )
						$text	= $part->getContent();
					else if( $part instanceof \CeusMedia\Mail\Message\Part\Attachment )
						$attachments[]	= (object) array(
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'mimeType'	=> $part->getMimeType(),
						);
					else if( $part instanceof \CeusMedia\Mail\Message\Part\InlineImage )
						$images[]	= (object) array(
							'fileName'	=> $part->getFileName(),
							'fileSize'	=> $part->getFileSize(),
							'mimeType'	=> $part->getMimeType(),
						);
				}
			}
		}

		$parts	= array();

		if( $html ){																			//  realize HTML view if available
			$headingHtml	= UI_HTML_Tag::create( 'h4', 'HTML' );
			$displayHtml	= UI_HTML_Tag::create( 'iframe', '', array(
				'src'			=> './admin/mail/queue/html/'.$mail->mailId,
				'frameborder'	=> '0',
				'style'			=> "width: 100%; height: 450px; border: 1px solid gray; border-radius: 2px;",
			) );
			$parts[]	= $headingHtml.$displayHtml;
		}

		if( $attachments ){																		//  realize attachments view if available
			foreach( $attachments as $nr => $attachment ){
				$size	= Alg_UnitFormater::formatBytes( $attachment->fileSize );
				$size	= UI_HTML_Tag::create( 'small', $size, array( 'class' => 'muted' ) );
				$label	= $attachment->fileName.' '.$size;
				$attachments[$nr]	= UI_HTML_Tag::create( 'li', $label );
			}
			$displayAttachments	= UI_HTML_Tag::create( 'ul', $attachments );
			$headingAttachments	= '<h4>Anhänge</h4>';
			$parts[]	= $headingAttachments.$displayAttachments;
		}

		if( $images ){																			//  realize inline images view if available
			foreach( $images as $nr => $image ){
				$size	= Alg_UnitFormater::formatBytes( $image->fileSize );
				$size	= UI_HTML_Tag::create( 'small', $size, array( 'class' => 'muted' ) );
				$label	= $image->fileName.' '.$size;
				$images[$nr]	= UI_HTML_Tag::create( 'li', $label );
			}
				$images[$nr]	= UI_HTML_Tag::create( 'li', $image->fileName.' <small class="muted">('.Alg_UnitFormater::formatBytes( $image->fileSize ).')</small>' );
			$displayImages	= UI_HTML_Tag::create( 'ul', $images );
			$headingImages	= '<h4>Eingebundene Bilder</h4>';
			$parts[]	= $headingImages.$displayImages;
		}

		//  realize plain text view if available
		if( $text ){
			$headingText	= UI_HTML_Tag::create( 'h4', 'Plain Text' );
			$displayText	= UI_HTML_Tag::create( 'pre', $text, array(
				'style' => "width: 98%; height: 450px; border: 1px solid gray; overflow: auto; border-radius: 2px;",
			) );
			$parts[]	= $headingText.$displayText;
		}

		$helperSource	= new View_Helper_Mail_View_Source( $env );
		$helperSource->setMail( $mail );
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
		$parts		= array();
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
