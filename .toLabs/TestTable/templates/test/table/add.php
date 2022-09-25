<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$title	= empty( $title ) ? '' : $title;

return '
'.HtmlTag::create( 'h2', $words['add']['heading'] ).'
<p>
	This is a typical <em>Add Form</em>.
</p>
'.HtmlElements::Form( 'add', './test/table/add' ).'
	<fieldset>
		<legend>new entry</legend>
		'.HtmlTag::create( 'label', 'Title', array( 'for' => 'title' ) ).'<br/>
		'.HtmlElements::Input( 'title', $title ).'<br/>
		<div class="buttonbar">
			<div class="right">
				'.HtmlElements::LinkButton( './test/table', 'cancel', 'button cancel' ).'
			</div>
			'.HtmlElements::Button( 'doAdd', 'add', 'button save' ).'
			<div class="clearfloat"></div>
		</div>
	</fieldset>
</form>';
?>
