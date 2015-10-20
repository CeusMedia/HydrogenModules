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
		$list['9-source']	= '<h4>Source</h4><pre style="max-height: 300px; scroll-y: auto; overflow: auto">'.$mail->object->mail->getBody().'</pre>';
		if( !class_exists( 'CMM_Mail_Parser' ) ){
			$message	= 'No mail parser available.';
		}
		else {
			$parts	= \CeusMedia\Mail\Parser::parseBody( $mail->object->mail->getBody() );

/*			print_m( $parts );
			xmp( $mail->object->mail->getBody() );
			die;
*/
			foreach( $parts as $key => $part ){
				if( strlen( trim( $part->getContent() ) ) ){
					if( $part->getMimeType() === "text/html" )
						$list['1-html']	= '<h4>HTML</h4><iframe src="./admin/mail/queue/html/'.$mail->mailId.'" style="width: 100%; height: 450px; border: 1px solid black" frameborder="0"></iframe>';
					if( $part->getMimeType() === "text/plain" )
						$list['5-text']	= '<h4>Text</h4><pre style="width: 98%; height: 450px; border: 1px solid black; overflow: auto">'.$part->getContent().'</pre>';
				}
			}
		}
		ksort( $list );
	}
}

return '
<div class="content-panel">
	<h4>Body</h4>
	<div class="content-panel-inner">
		<b>'.$message.'</b>
		'.join( $list ).'
	</div>
</div>';
?>
