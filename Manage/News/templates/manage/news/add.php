<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$w			= (object) $words['add'];

$optStatus	= UI_HTML_Elements::Options( $words['states'], $news->status );

$env->getPage()->js->addScriptOnReady( 'ModuleNewsEditor.init();' );

$buttonAdd	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, array(
	'href'	=> './manage/news',
	'class'	=> 'btn btn-small',
) );
$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

$editorClass	= View_Manage_News::getEditorClass( $env );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/news/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title" class="mandatory required">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $news->title, ENT_QUOTES, 'UTF-8' ).'" required/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_startsAt">'.$w->labelStartsAt.'</label>
					<input type="date" name="startsAt" id="input_startsAt" class="span12" value="'.( $news->startsAt ? date( 'd.m.Y', $news->startsAt ) : '' ).'"/>
				</div>
				<div class="span2">
					<label for="input_endsAt">'.$w->labelEndsAt.'</label>
					<input type="date" name="endsAt" id="input_endsAt" class="span12" value="'.( $news->endsAt ? date( 'd.m.Y', $news->endsAt ) : '' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<label for="input_content" class="mandatory required">'.$w->labelContent.'</label>
				<textarea name="content" id="input_content" class="span12 '.$editorClass.'" data-tinymce-mode="minimal" rows="12">'.htmlentities( $news->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
			<br/>
			'.HtmlTag::create( 'div', join( '&nbsp;', array(
				$buttonAdd,
				$buttonSave,
			) ), array( 'class' => 'buttonbar' ) ).'
		</form>
	</div>
</div>';
?>
