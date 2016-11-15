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


function renderCodeBadge( $check, $label = NULL ){
	$code	= $check->code;
	switch( (int) substr( $check->code, 0, 1 ) ){
		case 0:
			$code		= str_pad( $check->error, 3, "0", STR_PAD_LEFT );
			$labelCode	= 'label-inverse';
			break;
		case 1:
		case 2:
		case 3:
			$labelCode	 = 'label-success';
			break;
		case 4:
			$labelCode	 = 'label-warning';
			break;
		case 5:
			$labelCode	= 'label-important';
			break;
		default:
			$labelCode	= '<em>unknown</em>';
			break;
	}
	$label	= strlen( trim( $label ) ) ? trim( $label ) : $code;
	return UI_HTML_Tag::create( 'span', $label, array( 'class' => 'label '.$labelCode ) );
}

$checks		= UI_HTML_Tag::create( 'div', 'Keine Prüfungen bisher.', array( 'class' => 'text text-info' ) );
if( $address->checks ){
	$rows	= array();
	foreach( $address->checks as $check ){
		$description	= \CeusMedia\Mail\Transport\SMTP\Code::getText( $check->code );
		$labelCode		= renderCodeBadge( $check );
		$status		 	= UI_HTML_Tag::create( 'abbr', $labelCode, array( 'title' => $description ) );
		$error			= ucwords( strtolower( str_replace( "_", " ", $errors[$check->error] ) ) );

		$facts	= renderFacts( array(
			'SMTP-Code'			=> $labelCode.' <small class="muted">'.$description.'</small>',
			'Fehler'			=> ucwords( strtolower( str_replace( "_", " ", $errors[$check->error] ) ) ),
			'Servermeldung'		=> '<not-pre>'.$check->message.'</not-pre>',
			'Datum / Uhrzeit'	=> date( 'Y-m-d', $check->createdAt ).' <small class="muted">'.date( 'H:i:s', $check->createdAt ).'</small>',
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
