<?php

use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $data */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] );
$iconList		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-list"] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] );

$optStatus	= [
	0		=> 'inaktiv',
	1		=> 'aktiv',
];
$optStatus	= HtmlElements::Options( $optStatus, @$data->status );

$optRegex	= [
	0		=> 'nein',
	1		=> 'ja',
];
$optRegex	= HtmlElements::Options( $optRegex, @$data->regex );

$optCode	= [];
foreach( View_Admin_Route::$availableHttpCodes as $httpCode )
	$optCode[$httpCode]	= $httpCode.' '.HttpStatus::getText( $httpCode );
$optCode	= HtmlElements::Options( $optCode, @$data->code );

$buttonsCancel	= HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', [
	'href'		=> './admin/route',
	'class'		=> 'btn',
] );
$buttonsSave	= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
] );

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
