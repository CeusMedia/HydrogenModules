<?php
$w				= (object) $words['add'];
$optCurrency	= UI_HTML_Elements::Options( $words['currencies'], $fund->currency );

return '
<form action="./work/fund/add" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-50">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory" value="'.$fund->title.'"/>
			</li>
			<li class="column-left-50">
				<label for="input_kag">'.$w->labelKag.'</label><br/>
				<input type="text" name="kag" id="input_kag" class="max" value="'.$fund->kag.'"/>
			</li>
			<li class="column-clear column-left-20">
				<label for="input_ISIN" class="mandatory">'.$w->labelISIN.'</label><br/>
				<input type="text" name="ISIN" id="input_ISIN" class="max mandatory" value="'.$fund->ISIN.'"/>
			</li>
			<li class="column-left-10">
				<label for="input_pieces" class="mandatory">'.$w->labelPieces.'</label><br/>
				<input type="text" name="pieces" id="input_pieces" class="max mandatory" value="'.$fund->pieces.'"/>
			</li>
			<li class="column-left-10">
				<label for="input_currency">'.$w->labelCurrency.'</label><br/>
				<select name="currency" id="input_currency" class="s">'.$optCurrency.'</select>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/fund', $w->buttonCancel, 'button icon cancel' ).'
			'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button icon add' ).'
		</div>
	</fieldset>
</form>
';
?>
