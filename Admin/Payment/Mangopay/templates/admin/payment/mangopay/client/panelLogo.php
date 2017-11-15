<?php
$w	= (object) $words['panel-logo'];

$iconFolder	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
$form	= '<form action="./admin/payment/mangopay/client/logo" method="post" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12">
			<a href="'.$client->Logo.'" class="fancybox-auto">
			 	<img src="'.$client->Logo.'" class="not-img-polaroid" style="width: 99%"/>
			</a>
		</div>
	</div>
	<br/>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_logo">'.$w->labelLogo.'</label>
			'.View_Helper_Input_File::renderStatic( $env, 'logo', $iconFolder, TRUE ).'
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-upload"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
