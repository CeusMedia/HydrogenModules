<?php

return '<h2>Ups!</h2>
<p>Es ist ein Problem aufgetreten.</p>
<dl class="dl-horizontal">
	<dt>Message</dt>
	<dd>'.$exception->message.'</dd>
	<dt>Code</dt>
	<dd>'.$exception->code.'</dd>
	<dt>File</dt>
	<dd>'.$exception->file.'</dd>
	<dt>Line</dt>
	<dd>'.$exception->line.'</dd>
	<dt>Trace</dt>
	<dd><kbd>'.nl2br( $exception->trace ).'</kbd></dd>
</dl>';

?>
