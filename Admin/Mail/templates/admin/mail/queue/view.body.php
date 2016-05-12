<?php

$frontend	= Logic_Frontend::getInstance( $env );
CMC_Loader::registerNew( 'php5', 'Mail_', $frontend->getPath().'classes/Mail/' );


$message	= '';
$list		= array();
if( !$mail->object ){
	$message	= 'No mail object available.';
}
else{
	if( !is_object( $mail->object ) ){
		$message	= 'Mail object could not by parsed.';
	}
	else {
		$list	= array();

		$parts	= array();
		if( $mail->object->mail instanceof \CeusMedia\Mail\Message ){
			$parts	= $mail->object->mail->getParts();
		}
		else{
			if( !class_exists( 'CMM_Mail_Parser' ) )												//  @todo change to \CeusMedia\Mail\Parser
				$message	= 'No mail parser available.';
			else
				$parts	= \CeusMedia\Mail\Parser::parseBody( $mail->object->mail->getBody() );
		}
		//  @todo implement suport for attachments and inline images
/*		print_m( $mail->object->mail->getParts() );
		print_m( $parts );
		die;
//		$source	= \CeusMedia\Mail\Renderer::render( $mail->object->mail );
//		$list['9-source']	= '<h4>Source</h4><pre style="max-height: 300px; scroll-y: auto; overflow: auto">'.$source.'</pre>';
*/
		foreach( $parts as $key => $part ){
			if( strlen( trim( $part->getContent() ) ) ){
				if( $part->getMimeType() === "text/html" )
					$list['1-html']	= '<h4>HTML</h4><iframe src="./admin/mail/queue/html/'.$mail->mailId.'" style="width: 100%; height: 450px; border: 1px solid gray; border-radius: 2px;" frameborder="0"></iframe>';
				else if( $part->getMimeType() === "text/plain" )
					$list['5-text']	= '<h4>Text</h4><pre style="width: 98%; height: 450px; border: 1px solid gray; overflow: auto; border-radius: 2px;">'.$part->getContent().'</pre>';
//				else
//					$list['9']	= $part->getMimeType();
			}
		}
		ksort( $list );
	}
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
