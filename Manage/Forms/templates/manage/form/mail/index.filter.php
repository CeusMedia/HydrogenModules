<?php

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$buttonSubmit	= UI_HTML_Tag::create( 'button', $iconFilter.' filtern', array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-small btn-info'
) );
$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;leeren', array(
	'href'	=> './manage/form/mail/filter/reset',
	'class'	=> 'btn btn-small btn-inverse'
) );

$optIdentifier	= array( '' => '- alle -');
foreach( $identifiers as $identifier )
	$optIdentifier[$identifier]	= $identifier;
$optIdentifier	= UI_HTML_Elements::Options( $optIdentifier, $filterIdentifier );

$formatMap	= array(
	Model_Form_Mail::FORMAT_HTML	=> 'HTML',
	Model_Form_Mail::FORMAT_TEXT	=> 'Text',
);

$optFormat	= array( '' => '- alle -');
foreach( $formatMap as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat	= UI_HTML_Elements::Options( $optFormat, $filterFormat );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/mail/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_mailId">ID</label>
					<input type="text" name="mailId" id="input_mailId" value="'.$filterMailId.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel <small class="muted">(ungefähr)</small></label>
					<input type="text"  name="title" id="input_title" value="'.$filterTitle.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_identifier">Shortcode <small class="muted">(ungefähr)</small></label>
					<input type="text" name="identifier" id="input_identifier" value="'.$filterIdentifier.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_format">Format</label>
					<select name="format" id="input_format">'.$optFormat.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					'.$buttonSubmit.'
					'.$buttonReset.'
				</div>
			</div>
		</form>
	</div>
</div>';
