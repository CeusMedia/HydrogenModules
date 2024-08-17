<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Dictionary $moduleConfig */
/** @var array<string,array<string,string>> $words */
/** @var object $post */

$w		= (object) $words['comment'];

$iconSave	= '';
if( $env->getModules()->has( 'UI_Bootstrap' ) )
	$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] ).'&nbsp;';
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';

$buttonSave	= HtmlTag::create( 'button', $iconSave.$w->buttonSave, [
	'type'		=> 'submit',
	'name'		=> 'save',
	'value'		=> '1',
	'class'		=> 'btn btn-primary',
] );

if( $moduleConfig->get( 'comments.ajax' ) )
	$buttonSave	= HtmlTag::create( 'button', $iconSave.$w->buttonSave, array(
		'type'		=> 'button',
		'onclick'	=> 'Blog.comment()',
		'class'		=> 'btn btn-primary',
	) );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form-info-blog-comment-add" action="./info/blog/comment/'.$post->postId.'" method="post">
			<input type="hidden" name="postId" id="input_postId" value="'.$post->postId.'"/>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_content">'.$w->labelContent.' <small class="muted">'.$w->labelContent_suffix.'</small></label>
					<textarea name="content" id="input_content" class="span12" rows="5" required="required"></textarea>
				</div>
				<div class="span4">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_username">'.$w->labelUsername.' <small class="muted">'.$w->labelUsername_suffix.'</small></label>
							<input type="text" name="username" id="input_username" class="span12" value="" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_email">'.$w->labelEmail.' <small class="muted">'.$w->labelEmail_suffix.'</small></label>
							<input type="text" name="email" id="input_email" class="span12" value=""/>
						</div>
					</div>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span4">
					<label for="input_username">'.$w->labelUsername.' <small class="muted">'.$w->labelUsername_suffix.'</small></label>
					<input type="text" name="username" id="input_username" class="span12" value="" required="required"/>
				</div>
				<div class="span8">
					<label for="input_email">'.$w->labelEmail.' <small class="muted">'.$w->labelEmail_suffix.'</small></label>
					<input type="text" name="email" id="input_email" class="span12" value=""/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.' <small class="muted">'.$w->labelContent_suffix.'</small></label>
					<textarea name="content" id="input_content" class="span12" rows="6" required="required"></textarea>
				</div>
			</div>-->
			<div class="buttonbar">
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
<script>
</script>';
