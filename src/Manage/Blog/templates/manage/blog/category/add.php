<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Blog_Category $view */
/** @var array<string,array<string,string>> $words */
/** @var object $category */

$w		= (object) $words['add'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-arrow-left'] );
	$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-check'] );
}

$languages		= $env->getLanguage()->getLanguages();

$optLanguage	= HtmlElements::Options( array_combine( $languages, $languages ), $category->language );

$optStatus		= HtmlElements::Options( $words['states'], $category->status );


$buttonCancel		= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'		=> "./manage/blog",
	'class'		=> "btn btn-small",
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'		=> "submit",
	'name'		=> "save",
	'value'		=> "1",
	'class'		=> "btn btn-primary"
] );

$tabs	= $view->renderTabs( '/category' );

return '
'.$tabs.'
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/blog/category/add" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $category->title ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
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
					<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="10">'.htmlentities( $category->content ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
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
