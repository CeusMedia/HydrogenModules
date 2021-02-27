<?php
$w				= (object) $words['add'];

$optType		= UI_HTML_Elements::Options( $words['types'], $fund->type );
$optScope		= UI_HTML_Elements::Options( $words['scopes'], $fund->scope );
$optCurrency	= UI_HTML_Elements::Options( $words['currencies'], $fund->currency );

return '
<form action="./work/finance/fund/add" method="post">
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
			<li class="column-clear column-left-20">
				<label for="input_type">'.$w->labelType.'</label><br/>
				<select name="type" id="input_type" class="max">'.$optType.'</select>
			</li>
			<li class="column-left-20">
				<label for="input_scope">'.$w->labelScope.'</label><br/>
				<select name="scope" id="input_scope" class="max">'.$optScope.'</select>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/finance/fund', $w->buttonCancel, 'button icon cancel' ).'
			'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button icon add' ).'
		</div>
	</fieldset>
</form>';
