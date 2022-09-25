<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zur Liste', array(
	'href'	=> './work/billing/corporation',
	'class'	=> 'btn btn',
) );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$panelFacts		= '
<div class="content-panel">
	<h3>Unternehmen</h3>
	<div class="content-panel-inner">
		<form action="./work/billing/corporation/edit/'.$corporation->corporationId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title">Bezeichnung</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $corporation->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_iban">IBAN</label>
					<input type="text" name="iban" id="input_iban" class="span12" required="required" value="'.htmlentities( $corporation->iban, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_bic">BIC</label>
					<input type="text" name="bic" id="input_bic" class="span12" required="required" value="'.htmlentities( $corporation->bic, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_balance">Kontostand</label>
					<input type="number" step="0.01" name="balance" id="input_balance" class="span10 input-number" disabled="disabled" value="'.number_format( $corporation->balance, 2, '.', '' ).'"/><span class="suffix">&euro;</span>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';


$tabs		= View_Work_Billing_Corporation::renderTabs( $env, $corporation->corporationId, 0 );
$heading	= '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelFacts.'
	</div>
</div>';
