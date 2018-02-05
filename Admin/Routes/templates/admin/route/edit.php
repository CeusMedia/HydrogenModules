<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-list" ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );

//print_m( $route );die;

$optStatus	= array(
	0		=> 'inaktiv',
	1		=> 'aktiv',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $route->status );

$optRegex	= array(
	0		=> 'nein',
	1		=> 'ja',
);
$optRegex	= UI_HTML_Elements::Options( $optRegex, $route->regex );


$buttonsCancel	= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
	'href'		=> './admin/route',
	'class'		=> 'btn',
) );
$buttonsSave	= UI_HTML_Tag::create( 'a', $iconSave.'&nbsp;speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );
$buttonsRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'		=> './admin/route/remove/'.$route->routeId,
	'class'		=> 'btn btn-small btn-danger',
	'title'		=> 'entfernen'
) );


return '<div class="content-panel">
	<h3>Route verändern</h3>
	<div class="content-panel-inner">
		<form action="./admin/route/edit/'.$route->routeId.'" method="post">
			<div class="row-fluid">
				<div class="span9">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $route->title, ENT_QUOTES, 'UTF-8' ).'"/>
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
					<input type="text" name="source" id="input_source" class="span12" value="'.htmlentities( $route->source, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_code">HTTP Status Code</label>
					<input type="number" min="100" max="599" name="code" id="input_code" class="span12" value="'.htmlentities( $route->code, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span9">
					<label for="input_target">Ziel</label>
					<input type="text" name="target" id="input_target" class="span12" value="'.htmlentities( $route->target, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonsCancel.'
				'.$buttonsSave.'
				'.$buttonsRemove.'
			</div>
		</form>
	</div>
</div>';
