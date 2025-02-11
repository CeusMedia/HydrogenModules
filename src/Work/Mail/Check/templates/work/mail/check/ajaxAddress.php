<?php
/** @deprecated remove template, is new: Controller_Ajax_Work_Mail_Check */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\Mail\Transport\SMTP\Code as SmtpCode;


/** @var array<string,array<string,string>> $words */
/** @var object $address */

$checks		= HtmlTag::create( 'div', 'Keine Prüfungen bisher.', ['class' => 'text text-info'] );
if( $address->checks ){
	$rows	= [];
	foreach( $address->checks as $check ){
		$codeLabel		= renderCodeBadge( $check );
		$codeDesc		= SmtpCode::getText( $check->code );

		$errorLabel		= ucwords( strtolower( str_replace( "_", " ", $words['errorCodes'][$check->error] ) ) );
		$errorDesc		= $words['errorLabels'][$check->error];

		$facts	= renderFacts( [
			'SMTP-Code'			=> $codeLabel.' <small class="muted">'.$codeDesc.'</small>',
			'Fehler'			=> $errorLabel.' <small class="muted">'.$errorDesc.'</small>',
			'Servermeldung'		=> '<not-pre>'.$check->message.'</not-pre>',
			'Datum / Uhrzeit'	=> date( 'Y-m-d', $check->createdAt ).' <small class="muted">'.date( 'H:i:s', $check->createdAt ).'</small>',
		] );

		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $facts ),
		] );
	}
	$checks	= HtmlTag::create( 'table', $rows, ['class' => 'table table-striped'] );
}

/** @noinspection XmlDeprecatedElement */
/** @noinspection HtmlDeprecatedTag */
return '
<big><span class="muted">Adresse: </span>'.$address->address.'</big>
<h4>Prüfungen <small class="muted">('.count( $address->checks ).')</small></h4>
'.$checks.'
<br/>
<br/>';



/**
 * @param array $facts
 * @return string
 */
function renderFacts( array $facts ): string
{
	$list	= [];
	foreach( $facts as $term => $definition ){
		$list[]	= HtmlTag::create( 'dt', $term );
		$list[]	= HtmlTag::create( 'dd', $definition );
	}
	return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
}

/**
 * @param $check
 * @param string|NULL $label
 * @return string
 */
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
	return HtmlTag::create( 'span', $label, ['class' => 'label '.$labelCode] );
}
