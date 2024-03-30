<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-check'] );

$helperUrl	= new \View_Helper_Mangopay_URL( $env );
$helperUrl->set( ( isset( $backwardTo ) && $backwardTo ) ? $backwardTo :  'manage/my/mangopay/bank' );
$helperUrl->setForwardTo( TRUE );
//$helperUrl->setBackwardTo( TRUE );
$helperUrl->setFrom( TRUE );
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> $helperUrl->render(),
	'class'	=> 'btn',
) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' weiter', [
	'type'	=> "submit",
 	'name'	=> "save",
	'value'	=> "select",
	'class'	=> "btn btn-primary",
] );

$panelAdd	= '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-bank"></i> Neues Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/add" method="post">
			<input type="hidden" name="forwardTo" value="'.( $forwardTo ?? '' ).'"/>
			<input type="hidden" name="backwardTo" value="'.( $backwardTo ?? '' ).'"/>
			<input type="hidden" name="from" value="'.( $from ?? '' ).'"/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_title">Bezeichnung <small class="muted"></small></label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span7">
					<label for="input_address">Adresse <small class="muted"></small></label>
					<input type="text" name="address" id="input_address" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_iban">IBAN <small class="muted"></small></label>
					<input type="text" name="iban" id="input_iban" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label for="input_bic">BIC <small class="muted"></small></label>
					<input type="text" name="bic" id="input_bic" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';

return '
<h2><a class="muted" href="./manage/my/mangopay/bank">Bankkonto</a> Neues Bankkonto</h2>
<div class="row-fluid">
	<div class="span6">
		'.$panelAdd.'
	</div>
</div>';
