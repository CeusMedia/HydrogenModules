<?php

//$env->getMessenger()->noteNotice( 'Diese Anwendung ist noch nicht vollständig implementiert.' );

$panelFacts		= $view->loadTemplateFile( 'admin/mail/queue/view.facts.php' );
$panelBody		= $view->loadTemplateFile( 'admin/mail/queue/view.body.php' );

$mail->date		= date( "Y-m-d H:i:s", max( $mail->enqueuedAt, $mail->sentAt ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/queue/' ) );

return $textTop.'
<h3><span class="muted">Mail: </span>'.$mail->subject.' <small>&rArr; '.$mail->receiverAddress.' <small>('.$mail->date.')</small></small></h3>
<div class="row-fluid">
	<div class="span12">
		'.$panelFacts.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelBody.'
	</div>
</div>'.$textBottom;
?>
