<?php

use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View_Admin_Mail_Queue $view */
/** @var array<array<string,string>> $words */
/** @var object{subject: string, enqueuedAt: int, sentAt: int, date: string} $mail */

//$env->getMessenger()->noteNotice( 'Diese Anwendung ist noch nicht vollständig implementiert.' );

$panelFacts		= $view->loadTemplateFile( 'admin/mail/queue/view.facts.php' );
$panelBody		= $view->loadTemplateFile( 'admin/mail/queue/view.body.php' );

$mail->date		= date( "Y-m-d H:i:s", max( $mail->enqueuedAt, $mail->sentAt ) );

[$textTop, $textBottom]	= array_values( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/queue/' ) );

/** @var object{subject: string, enqueuedAt: string, sentAt: string, date: string} $mail */
return $textTop.'
<h3><span class="muted">Mail: </span>'.$mail->subject.'</h3>
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
