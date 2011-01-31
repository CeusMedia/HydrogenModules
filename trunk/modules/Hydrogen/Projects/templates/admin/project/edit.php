<?php
return '
'.UI_HTML_Tag::create( 'h2', $words['edit']['heading'] ).'
<p>
	This is a typical <em>Edit Form</em>.
</p>
'.UI_HTML_Elements::Form( 'edit', './test/table/edit/'.$testId ).'
	<fieldset>
		<legend>edit entry</legend>
		'.UI_HTML_Tag::create( 'label', 'Title', array( 'for' => 'title' ) ).'<br/>
		'.UI_HTML_Elements::Input( 'title', htmlspecialchars( $test['title'], ENT_COMPAT, 'UTF-8' ) ).'<br/>
		<div class="buttonbar">
			<div class="right">
				'.UI_HTML_Elements::LinkButton( './test/table', 'cancel', 'button cancel' ).'
			</div>
			'.UI_HTML_Elements::Button( 'doEdit', 'save', 'button save' ).'
			&nbsp;|&nbsp;
			'.UI_HTML_Elements::LinkButton( './test/table/delete/'.$testId, 'delete', 'button delete' ).'
			<div class="clearfloat"></div>
		</div>
	</fieldset>
</form>';
?>
