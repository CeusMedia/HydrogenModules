<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w				= (object) $words['add'];
#$optCurrency	= HtmlElements::Options( $words['currencies'], $bank->currency );

$optType	= $words['readers'];
$optType	= HtmlElements::Options( $optType, $bank->type );

return '
<form action="./work/finance/bank/add" method="post">
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
				<label for="input_username" class="">'.$w->labelUsername.'</label><br/>
				<input type="text" name="username" id="input_username" class="max" value="'.$bank->username.'"/>
			</li>
			<li class="column-left-25">
				<label for="input_password" class="">'.$w->labelPassword.'</label><br/>
				<input type="password" name="password" id="input_password" class="max" value="'.$bank->password.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './work/finance/bank', $w->buttonCancel, 'button icon cancel' ).'
			'.HtmlElements::Button( 'add', $w->buttonAdd, 'button icon add' ).'
		</div>
	</fieldset>
</form>';
