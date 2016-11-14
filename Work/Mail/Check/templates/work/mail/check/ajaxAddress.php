<?php


$errors	= array(
	0	=> 'NONE',
	1	=> 'MX_RESOLUTION_FAILED',
	2	=> 'SOCKET_FAILED',
	3	=> 'SOCKET_EXCEPTION',
	4	=> 'CONNECTION_FAILED',
	5	=> 'HELO_FAILED',
	6	=> 'SENDER_NOT_ACCEPTED',
	7	=> 'RECEIVER_NOT_ACCEPTED',
);

function renderFacts( $facts ){
	$list	= array();
	foreach( $facts as $term => $definition ){
		$list[]	= UI_HTML_Tag::create( 'dt', $term );
		$list[]	= UI_HTML_Tag::create( 'dd', $definition );
	}
	return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
}

$checks		= UI_HTML_Tag::create( 'div', 'Keine Prüfungen bisher.', array( 'class' => 'text text-info' ) );
if( $address->checks ){
	$rows	= array();
	foreach( $address->checks as $check ){
		$description	= \CeusMedia\Mail\Transport\SMTP\Code::getText( $check->code );
		$status		 	= UI_HTML_Tag::create( 'abbr', $check->code, array( 'title' => $description ) );
		$error			= ucwords( strtolower( str_replace( "_", " ", $errors[$check->error] ) ) );

		$facts	= renderFacts( array(
			'SMTP-Code'			=> $check->code .' <small class="muted">'.$description.'</small>',
			'Fehler'			=> ucwords( strtolower( str_replace( "_", " ", $errors[$check->error] ) ) ),
			'Datum / Uhrzeit'	=> date( 'Y-m-d', $check->createdAt ).' <small class="muted">'.date( 'H:i:s', $check->createdAt ).'</small>',
			'Servermeldung'		=> '<pre>'.$check->message.'</pre>',
		) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $facts ),
		) );
	}
	$checks	= UI_HTML_Tag::create( 'table', $rows, array( 'class' => 'table table-striped' ) );
}

return '
<big><span class="muted">Adresse: </span>'.$address->address.'</big>
<h4>Prüfungen <small class="muted">('.count( $address->checks ).')</small></h4>
'.$checks.'
<br/>
<br/>
';

?>
