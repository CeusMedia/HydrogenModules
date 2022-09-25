<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

return '
'.HtmlTag::create( 'h2', $words['edit']['heading'] ).'
<p>
	This is a typical <em>Edit Form</em>.
</p>
'.HtmlElements::Form( 'edit', './test/table/edit/'.$testId ).'
	<fieldset>
		<legend>edit entry</legend>
		'.HtmlTag::create( 'label', 'Title', array( 'for' => 'title' ) ).'<br/>
		'.HtmlElements::Input( 'title', htmlspecialchars( $test['title'], ENT_COMPAT, 'UTF-8' ) ).'<br/>
		<div class="buttonbar">
			<div class="right">
				'.HtmlElements::LinkButton( './test/table', 'cancel', 'button cancel' ).'
			</div>
			'.HtmlElements::Button( 'doEdit', 'save', 'button save' ).'
			&nbsp;|&nbsp;
			'.HtmlElements::LinkButton( './test/table/delete/'.$testId, 'delete', 'button delete' ).'
			<div class="clearfloat"></div>
		</div>
	</fieldset>
</form>';
?>
