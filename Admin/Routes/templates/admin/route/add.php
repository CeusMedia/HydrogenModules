<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
$iconList		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-list" ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );

$optStatus	= array(
	0		=> 'inaktiv',
	1		=> 'aktiv',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, @$data->status );

$optRegex	= array(
	0		=> 'nein',
	1		=> 'ja',
);
$optRegex	= UI_HTML_Elements::Options( $optRegex, @$data->regex );

$optCode	= array(
	200 => "200 OK",
	201 => "201 Created",
	202 => "202 Accepted",
	203 => "203 Non-Authoritative Information",
	204 => "204 No Content",
//	206 => "206 Partial Content",
	300 => "300 Multiple Choices",
	301 => "301 Moved Permanently",
	303 => "303 See Other",
	304 => "304 Not Modified",
	307 => "307 Temporary Redirect",
	308 => "308 Permanent Redirect",
	400 => "400 Bad Request",
	401 => "401 Unauthorized",
//	402 => "402 Payment Required",
	403 => "403 Forbidden",
	404 => "404 Not Found",
//	405 => "405 Method Not Allowed",
//	406 => "406 Not Acceptable",
//	407 => "407 Proxy Authentication Required",
	409 => "409 Conflict",
	410 => "410 Gone",
//	411 => "411 Length Required",
//	412 => "412 Precondition Failed",
//	413 => "413 Request Entity Too Large",
//	414 => "414 Request-URI Too Long",
	415 => "415 Unsupported Media Type",
//	416 => "416 Requested Range Not Satisfiable",
//	417 => "417 Expectation Failed",
//	420 => "420 Enhance Your Calm",
//	422 => "421 Unprocessable Entity",
	423 => "423 Locked",
//	424 => "424 Failed Dependency",
//	428 => "428 Precondition Required",
	429 => "429 Too Many Requests",
//	444 => "444 No Response",
	451 => "451 Unavailable For Legal Reasons",
	495 => "495 Cert Error",
	496 => "496 No Cert",
	497 => "497 HTTP to HTTPS",
	501 => "501 Not Implemented",
	502 => "502 Bad Gateway",
	503 => "503 Service Unavailable",
//	505 => "505 HTTP Version Not Supported",
	508 => "508 Loop Detected",
//	511 => "511 Network Authentication Required",
);
$optCode	= UI_HTML_Elements::Options( $optCode, @$data->code );

$buttonsCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'		=> './admin/route',
	'class'		=> 'btn',
) );
$buttonsSave	= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

return '<div class="content-panel">
	<h3>Neue Route</h3>
	<div class="content-panel-inner">
		<form action="./admin/route/add" method="post">
			<div class="row-fluid">
				<div class="span9">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( @$data->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_status">Aktiv</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_regex">Regulärer Ausdruck</label>
					<select name="regex" id="input_regex" class="span12">'.$optRegex.'</select>
				</div>
				<div class="span9">
					<label for="input_source">Quelle</label>
					<input type="text" name="source" id="input_source" class="span12" value="'.htmlentities( @$data->source, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_code">HTTP Status Code</label>
					<select name="code" id="input_code" class="span12">'.$optCode.'</select>
				</div>
				<div class="span9">
					<label for="input_target">Ziel</label>
					<input type="text" name="target" id="input_target" class="span12" value="'.htmlentities( @$data->target, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonsCancel.'
				'.$buttonsSave.'
			</div>
		</form>
	</div>
</div>';
