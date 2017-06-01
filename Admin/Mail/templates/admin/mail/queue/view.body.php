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

	$helperSource	= new View_Helper_Mail_View_Source( $env );
	$helperSource->setMail( $mail );
	$helperSource->setMode( View_Helper_Mail_View_Source::MODE_CONDENSED );
	$headingSource	= UI_HTML_Tag::create( 'h4', 'Source' );
	$valueSource	= htmlentities( $helperSource->render(), ENT_QUOTES, 'UTF-8' );
	$viewSource		= UI_HTML_Tag::create( 'pre', $valueSource, array(
		'style'	=> "max-height: 300px; scroll-y: auto; overflow: auto",
	) );
	$list['9-source']	= $headingSource.$viewSource;

	foreach( $mail->parts as $key => $part ){
		if( $part instanceof \CeusMedia\Mail\Part\HTML )
			$list['1-html']	= TRUE;
		else if( $part instanceof \CeusMedia\Mail\Part\Attachment )
			$list['2-attachments'][]	= $part;
		else if( $part instanceof \CeusMedia\Mail\Part\InlineImage )
			$list['3-images'][]	= $part;
		else if( $part instanceof \CeusMedia\Mail\Part\Text )
			$list['5-text']	= $part->getContent();
	}
	//  support for mails saved with older implementation using CeusMedia::Common:Net_Mail
	//  @deprecated for all instances having saved mails using CeusMedia::Mail
	//  @todo remove this outdated support in version 0.9
	foreach( $mail->parts as $key => $part ){
		if( !$list['1-html'] && $part->getMimeType() === "text/html" )
			$list['1-html']	= TRUE;
		else if( !$list['5-text'] && $part->getMimeType() === "text/plain" )
			$list['5-text']	= $part->getContent();
	}


	//  realize HTML view if available
	if( $list['1-html'] ){
		$headingHtml	= UI_HTML_Tag::create( 'h4', 'HTML' );
		$displayHtml	= UI_HTML_Tag::create( 'iframe', $list['5-text'], array(
			'src'			=> './admin/mail/queue/html/'.$mail->mailId,
			'frameborder'	=> '0',
			'style'			=> "width: 100%; height: 450px; border: 1px solid gray; border-radius: 2px;",
		) );
		$list['1-html']	= $headingHtml.$displayHtml;
	}

	//  realize attachments view if available
	$rows	= array();
	foreach( $list['2-attachments'] as $attachment )
		$rows[]	= UI_HTML_Tag::create( 'li', $attachment->getFileName() );
	$list['2-attachments']	= $rows ? '<h4>Anhänge</h4>'.UI_HTML_Tag::create( 'ul', $rows ) : '';

	//  realize inline images view if available
	$rows	= array();
	foreach( $list['3-images'] as $attachment )
		$rows[]	= UI_HTML_Tag::create( 'li', $attachment->getFilename() );
	$list['3-images']	= $rows ? '<h4>Eingebundene Bilder</h4>'.UI_HTML_Tag::create( 'ul', $rows ) : '';

	//  realize plain text view if available
	if( $list['5-text'] ){
		$headingText	= UI_HTML_Tag::create( 'h4', 'Plain Text' );
		$displayText	= UI_HTML_Tag::create( 'pre', $list['5-text'], array(
			'style' => "width: 98%; height: 450px; border: 1px solid gray; overflow: auto; border-radius: 2px;",
		) );
		$list['5-text']	= $headingText.$displayText;
	}
	ksort( $list );
}

return '
<div class="content-panel">
	<h4>'.$words['view-body']['heading'].'</h4>
	<div class="content-panel-inner">
		<b>'.$message.'</b>
		'.join( $list ).'
	</div>
</div>';
?>
