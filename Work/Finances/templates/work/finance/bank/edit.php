<?php
$w			= (object) $words['edit'];

$optType	= $words['readers'];
$optType	= UI_HTML_Elements::Options( $optType, $bank->type );

return '
<form action="./work/finance/bank/edit/'.$bank->bankId.'" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-20">
				<label for="input_type" class="mandatory">'.$w->labelType.'</label><br/>
				<select name="type" id="input_type" class="max mandatory">'.$optType.'</select>
			</li>
			<li class="column-left-30">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory" value="'.$bank->title.'"/>
			</li>
			<li class="column-left-25">
				<label for="input_username">'.$w->labelUsername.'</label><br/>
				<input type="text" name="username" id="input_username" class="max" value="'.$bank->username.'"/>
			</li>
			<li class="column-left-25">
				<label for="input_password">'.$w->labelPassword.'</label><br/>
				<input type="password" name="password" id="input_password" class="max"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/finance/bank', $w->buttonCancel, 'button icon cancel' ).'
			'.UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button icon save' ).'
			'.UI_HTML_Elements::LinkButton( './work/finance/bank/remove/'.$bank->bankId, $w->buttonRemove, 'button icon remove', $w->buttonRemoveConfirm, TRUE ).'
		</div>
	</fieldset>
</form>
';
?>
