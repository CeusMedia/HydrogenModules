<?php
$w			= (object) $words['edit'];

return '
<form action="./work/fund/edit/'.$fund->fundId.'" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-50">
				<label for="input_title">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" class="max" value="'.$fund->title.'"/>
			</li>
			<li class="column-left-50">
				<label for="input_ISIN">'.$w->labelISIN.'</label><br/>
				<span style="font-size: 1.2em;">'.$fund->ISIN.'</span>
			</li>
			<li class="column-clear column-left-50">
				<label for="input_kag">'.$w->labelKag.'</label><br/>
				<input type="text" name="kag" id="input_kag" class="max" value="'.$fund->kag.'"/>
			</li>
			<li class="column-left-50">
				<label for="input_pieces">'.$w->labelPieces.'</label><br/>
				<input type="text" name="pieces" id="input_pieces" value="'.$fund->pieces.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/fund', $w->buttonCancel, 'button icon cancel' ).'
			'.UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button icon save' ).'
			'.UI_HTML_Elements::LinkButton( './work/fund/remove/'.$fund->fundId, $w->buttonRemove, 'button icon remove', $w->buttonRemoveConfirm, TRUE ).'
		</div>
	</fieldset>
</form>
';
?>
