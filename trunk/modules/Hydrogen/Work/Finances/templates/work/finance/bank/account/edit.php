<?php
$w			= (object) $words['edit'];

$optCurrency	= UI_HTML_Elements::Options( $words['currencies'], $account->currency );

$optBankId	= array();
foreach( $banks as $bank )
	$optBankId[$bank->bankId]	= $bank->title;
$optBankId	= UI_HTML_Elements::Options( $optBankId, $account->bankId );

$optType	= array( 0 => 'manuell / statisch', 1 => 'automatisch / dynamisch' );
$optType	= UI_HTML_Elements::Options( $optType, $account->type );

return '
<form action="./work/finance/bank/account/edit/'.$account->bankAccountId.'" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-20">
				<label for="input_bankId">'.$w->labelBankId.'</label><br/>
				<select name="bankId" id="input_bankId" class="max">'.$optBankId.'</select>
			</li>
			<li class="column-left-30">
				<label for="input_title">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" class="max" value="'.$account->title.'"/>
			</li>
			<li class="column-left-25">
				<label for="input_type">'.$w->labelType.'</label><br/>
				<select type="text" name="type" id="input_type" class="max">'.$optType.'</select/>
			</li>
			<li class="column-left-25">
				<label for="input_accountKey">'.$w->labelAccountKey.'</label><br/>
				<input type="text" name="username" id="input_accountKey" class="max" value="'.$account->accountKey.'"/>
			</li>
			<li class="column-clear column-left-10">
				<label for="input_value">'.$w->labelValue.'</label><br/>
				<input type="text" name="value" id="input_value" class="max" value="'.$account->value.'"/>
			</li>
			<li class="column-left-10">
				<label for="input_currency">'.$w->labelCurrency.'</label><br/>
				<select type="text" name="currency" id="input_currency" class="max">'.$optCurrency.'</select/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/finance/bank', $w->buttonToTop, 'button icon cancel top up' ).'
			'.UI_HTML_Elements::LinkButton( './work/finance/bank/account', $w->buttonCancel, 'button icon cancel' ).'
			'.UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button icon save' ).'
			'.UI_HTML_Elements::LinkButton( './work/finance/bank/remove/'.$account->bankId, $w->buttonRemove, 'button icon remove', $w->buttonRemoveConfirm, TRUE ).'
		</div>
	</fieldset>
</form>
';
?>
