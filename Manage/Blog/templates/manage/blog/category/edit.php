<?php

$w		= (object) $words['edit'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-arrow-left' ) );
	$iconSave		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-check' ) );
}

$languages		= $env->getLanguage()->getLanguages();

$optLanguage	= UI_HTML_Elements::Options( array_combine( $languages, $languages ), $category->language );

$optStatus		= UI_HTML_Elements::Options( $words['states'], $category->status );


$buttonCancel		= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'href'		=> "./manage/blog",
	'class'		=> "btn btn-small",
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
	'type'		=> "submit",
	'name'		=> "save",
	'value'		=> "1",
	'class'		=> "btn btn-primary"
) );

$tabs	= $view->renderTabs( '/category' );

return '
'.$tabs.'
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/blog/category/edit/'.$category->categoryId.'" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $category->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_language">'.$w->labelLanguage.'</label>
					<select name="language" id="input_language" class="span12">'.$optLanguage.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="10">'.htmlentities( $category->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
					<br/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
