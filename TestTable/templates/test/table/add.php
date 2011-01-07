<?php
$title	= empty( $title ) ? '' : $title;

return '
'.UI_HTML_Tag::create( 'h2', $words['add']['heading'] ).'
<p>
	This is a typical <em>Add Form</em>.
</p>
'.UI_HTML_Elements::Form( 'add', './test/table/add' ).'
	<fieldset>
		<legend>new entry</legend>
		'.UI_HTML_Tag::create( 'label', 'Title', array( 'for' => 'title' ) ).'<br/>
		'.UI_HTML_Elements::Input( 'title', $title ).'<br/>
		<div class="buttonbar">
			<div class="right">
				'.UI_HTML_Elements::LinkButton( './test/table', 'cancel', 'button cancel' ).'
			</div>
			'.UI_HTML_Elements::Button( 'doAdd', 'add', 'button save' ).'
			<div class="clearfloat"></div>
		</div>
	</fieldset>
</form>';
?>
