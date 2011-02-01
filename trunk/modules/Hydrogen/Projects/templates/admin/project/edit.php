<?php

$optStatus	= $words['status'];
krsort( $optStatus );
$optStatus['_selected']	= $project->status;

return '
<div class="column-right-33">
</div>
<div class="column-left-66">
	'.UI_HTML_Tag::create( 'h2', sprintf( $words['edit']['heading'], $project->title ) ).'
	'.UI_HTML_Elements::Form( 'edit', './admin/project/edit/'.$projectId ).'
		<fieldset>
			<legend>'.sprintf( $words['edit']['legend'], $project->title ).'</legend>
			<ul class="input">
				<li>
					'.UI_HTML_Tag::create( 'label', $words['edit']['labelTitle'], array( 'for' => 'title' ) ).'<br/>
					'.UI_HTML_Elements::Input( 'title', htmlspecialchars( $project->title, ENT_COMPAT, 'UTF-8' ), 'l' ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'description', $words['edit']['labelDescription'], array( 'for' => 'description' ) ).'<br/>
					'.UI_HTML_Elements::TextArea( 'description', htmlspecialchars( $project->description, ENT_COMPAT, 'UTF-8' ), 'xl-m' ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'label', $words['edit']['labelStatus'], array( 'for' => 'status' ) ).'<br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './admin/project', $words['edit']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'doEdit', $words['edit']['buttonSave'], 'button save' ).'
				&nbsp;|&nbsp;
				'.UI_HTML_Elements::LinkButton( './admin/project/remove/'.$projectId, $words['edit']['buttonRemove'], 'button remove', $words['edit']['buttonRemoveConfirm'] ).'
				<div class="clearfloat"></div>
			</div>
		</fieldset>
	</form>
</div>';
?>
