<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['add'];

$optCategory	= [];
foreach( $categories as $item )
	$optCategory[$item->categoryId] = $item->title;
$optCategory	= HtmlElements::Options( $optCategory, $post->categoryId );

$languages		= $env->getLanguage()->getLanguages();

$optLanguage	= HtmlElements::Options( array_combine( $languages, $languages ), $post->language );

$optStatus		= HtmlElements::Options( $words['states'], $post->status );

$optAuthor		= [];
foreach( $users as $item )
	$optAuthor[$item->userId]		= $item->username;
$optAuthor		= HtmlElements::Options( $optAuthor, $post->authorId );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-arrow-left'] );
	$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-check'] );
}

$tabs	= $view->renderTabs();

return '
'.$tabs.'
<div class="content-panel content-panel-form">
	<h3><span class="muted">'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/blog/add" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $post->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span4">
					<label for="input_categoryId">'.$w->labelCategoryId.'</label>
					<select name="categoryId" id="input_categoryId" class="span12">'.$optCategory.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12" disabled="disabled">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_authorId">'.$w->labelAuthorId.'</label>
					<select name="authorId" id="input_authorId" class="span12">'.$optAuthor.'</select>
				</div>
				<div class="span2">
					<label for="input_language">'.$w->labelLanguage.'</label>
					<select name="language" id="input_language" class="span12">'.$optLanguage.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="extended" rows="20">'.htmlentities( $post->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
					<br/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_abstract">'.$w->labelAbstract.' <small class="muted">'.$w->labelAbstract_suffix.'</small></label>
					<textarea name="abstract" id="input_abstract" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="8">'.htmlentities( $post->abstract, ENT_QUOTES, 'UTF-8' ).'</textarea>
					<br/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/blog" class="btn btn-small">'.$iconCancel.'&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" value="1" class="btn btn-primary">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
