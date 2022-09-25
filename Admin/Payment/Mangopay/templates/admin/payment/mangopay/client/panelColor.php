<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['panel-colors'];

$form	= '<form action="./admin/payment/mangopay/client/edit" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="input_colorTheme">'.$w->labelColorTheme.'</label>
			<input type="text" name="colorTheme" id="input_colorTheme" class="span12" value="'.htmlentities( $client->PrimaryThemeColour, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span6">
			<label for="input_colorButton">'.$w->labelColorButton.'</label>
			<input type="text" name="colorButton" id="input_colorButton" class="span12" value="'.htmlentities( $client->PrimaryButtonColour, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
