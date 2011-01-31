<?php
$title	= empty( $title ) ? '' : $title;

return '
'.UI_HTML_Tag::create( 'h2', $words['add']['heading'] ).'
'.UI_HTML_Elements::Form( 'add', './admin/project/add' ).'
	<fieldset>
		<legend>new entry</legend>
		'.UI_HTML_Tag::create( 'label', $words['add']['labelTitle'], array( 'for' => 'title' ) ).'<br/>
		'.UI_HTML_Elements::Input( 'title', $title ).'<br/>
		<div class="buttonbar">
			<div class="right">
				'.UI_HTML_Elements::LinkButton( './admin/project', $words['add']['buttonCancel'], 'button cancel' ).'
			</div>
			'.UI_HTML_Elements::Button( 'doAdd', $words['add']['buttonSave'], 'button save' ).'
			<div class="clearfloat"></div>
		</div>
	</fieldset>
</form>';
?>
