<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$wf		= (object) $words['add'];

$optStatus	= HtmlElements::Options( $words['states'], max( 0, $server->status ) );

return '
<div class="column-left-66">
	<form action="./admin/server/add" method="post">
		<fieldset>
			<legend>'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-66">
					<label for="input_title" class="mandatory">'.$wf->labelTitle.'</label><br/>
					<input type="text" name="title" id="input_title" class="max mandatory" value="'.htmlentities( $server->title, ENT_QUOTES ).'"/>
				</li>
				<li class="column-left-33">
					<label for="input_status" class="">'.$wf->labelStatus.'</label><br/>
					<select name="status" id="input_status" class="max">'.$optStatus.'</select>
				</li>
				<li class="column-clear">
					<label for="input_description" class="">'.$wf->labelDescription.'</label><br/>
					<textarea name="description" id="input_description" class="max">'.htmlentities( $server->description, ENT_QUOTES ).'</textarea>
				</li>
 			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './admin/server', $wf->buttonCancel, 'button cancel' ).'
				'.HtmlElements::Button( 'add', $wf->buttonAdd, 'button add' ).'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-left-33">
</div>
<div class="column-clear"></div>
';
?>