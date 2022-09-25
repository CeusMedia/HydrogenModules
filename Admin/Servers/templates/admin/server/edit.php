<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$panelProjectList		= $this->loadTemplateFile( 'admin/server/edit.project.list.php' );
$panelProjectAdd		= $this->loadTemplateFile( 'admin/server/edit.project.add.php' );

$wf		= (object) $words['edit'];

$optStatus	= HtmlElements::Options( $words['states'], $server->status );

return '
<div class="column-left-66">
	<form action="./admin/server/edit/'.$server->serverId.'" method="post">
		<fieldset>
			<legend>'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-66">
					<label for="input_title" classs="mandatory">'.$wf->labelTitle.'</label><br/>
					<input type="text" name="title" id="input_title" class="max mandatory" value="'.htmlentities( $server->title, ENT_QUOTES ).'"/>
				</li>
				<li class="column-left-33">
					<label for="input_status" classs="">'.$wf->labelStatus.'</label><br/>
					<select name="status" id="input_status" class="max">'.$optStatus.'</select>
				</li>
				<li class="column-clear">
					<label for="input_description" classs="">'.$wf->labelDescription.'</label><br/>
					<textarea name="description" id="input_description" class="max">'.htmlentities( $server->description, ENT_QUOTES ).'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './admin/server', $wf->buttonCancel, 'button cancel' ).'
				'.HtmlElements::Button( 'edit', $wf->buttonSave, 'button save' ).'
				'.HtmlElements::LinkButton( './admin/server/remove/'.$server->serverId, $wf->buttonRemove, 'button remove', $wf->buttonRemoveConfirm ).'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-left-33">
	'.$panelProjectList.'
	'.$panelProjectAdd.'
</div>
<div class="column-clear"></div>
';
?>