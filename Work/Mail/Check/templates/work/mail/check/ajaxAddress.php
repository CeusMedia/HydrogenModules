<?php
function renderFacts( array $facts ): string
{
	$list	= [];
	foreach( $facts as $term => $definition ){
		$list[]	= UI_HTML_Tag::create( 'dt', $term );
		$list[]	= UI_HTML_Tag::create( 'dd', $definition );
	}
	return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );
}


function renderCodeBadge( $check, string $label = NULL ): string
{
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
	$rows	= [];
	foreach( $address->checks as $check ){
		$codeLabel		= renderCodeBadge( $check );
		$codeDesc		= \CeusMedia\Mail\Transport\SMTP\Code::getText( $check->code );

		$errorLabel		= ucwords( strtolower( str_replace( "_", " ", $words['errorCodes'][$check->error] ) ) );
		$errorDesc		= $words['errorLabels'][$check->error];

		$facts	= renderFacts( array(
			'SMTP-Code'			=> $codeLabel.' <small class="muted">'.$codeDesc.'</small>',
			'Fehler'			=> $errorLabel.' <small class="muted">'.$errorDesc.'</small>',
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
<br/>';
