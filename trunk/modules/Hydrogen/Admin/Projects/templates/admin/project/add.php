<?php
$title		= empty( $title ) ? '' : $title;
$description	= empty( $description ) ? '' : $description;
$description	= empty( $status ) ? '' : $status;

$optStatus	= $words['states'];
krsort( $optStatus );
$optStatus['_selected']	= $status;

return '
<div class="column-right-33">
</div>
<div class="column-left-66">
	'.UI_HTML_Tag::create( 'h2', $words['add']['heading'] ).'
	'.UI_HTML_Elements::Form( 'add', './admin/project/add' ).'
		<fieldset>
			<legend>'.$words['add']['legend'].'</legend>
			<ul class="input">
				<li>
					'.UI_HTML_Tag::create( 'label', $words['add']['labelTitle'], array( 'for' => 'title' ) ).'<br/>
					'.UI_HTML_Elements::Input( 'title', htmlspecialchars( $title, ENT_COMPAT, 'UTF-8' ), 'l' ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'description', $words['add']['labelDescription'], array( 'for' => 'description' ) ).'<br/>
					'.UI_HTML_Elements::TextArea( 'description', htmlspecialchars( $description, ENT_COMPAT, 'UTF-8' ), 'xl-m' ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'label', $words['add']['labelStatus'], array( 'for' => 'status' ) ).'<br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				</div>
				'.UI_HTML_Elements::LinkButton( './admin/project', $words['add']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'doAdd', $words['add']['buttonSave'], 'button save' ).'
				<div class="clearfloat"></div>
			</div>
		</fieldset>
	</form>
</div>

';
?>
