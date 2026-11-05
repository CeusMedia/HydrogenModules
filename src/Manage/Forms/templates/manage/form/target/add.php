<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */

$w	= (object) $words['add'];

$iconCancel		= new Icon( 'arrow-left' );
$iconSave		= new Icon( 'check' );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, ['class' => 'btn btn-small', 'href' => './manage/form/target'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, ['type' => 'submit', 'class' => 'btn btn-primary'] );

$optStatus		= $words['statuses'];
$optStatus		= HtmlElements::Options( $optStatus );

return '<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/target/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span6">
					<label for="input_className" class="mandatory">'.$w->labelClassName.' <small class="muted">'.$w->labelClassName_suffix.'</small></label>
					<input type="text" name="className" id="input_className" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_baseUrl" class="mandatory">'.$w->labelBaseUrl.'</label>
					<input type="text" name="baseUrl" id="input_baseUrl" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_apiKey">'.$w->labelApiKey.'</label>
					<input type="text" name="apiKey" id="input_apiKey" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
