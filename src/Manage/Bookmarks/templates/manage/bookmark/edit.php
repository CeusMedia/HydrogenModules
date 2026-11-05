<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Bookmark $view */
/** @var array<string,array<string,string>> $words */
/** @var object $bookmark */

$iconList		= HtmlTag::create( 'i', '', ['class' => 'icon-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
}

$w	= (object) $words['edit'];

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>'.$words['list']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$view->renderList( $bookmark->bookmarkId ).'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/bookmark/edit/'.$bookmark->bookmarkId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">'.$w->labelUrl.' <small class="muted">'.$w->labelUrl_suffix.'</small></label>
							<input class="span12" type="text" name="url" id="input_url" required="required" value="'.htmlentities( $bookmark->url, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input class="span12" type="text" name="title" id="input_title" required="required" value="'.htmlentities( $bookmark->title, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="buttonbar">
						<a class="btn btn-small" href="./manage/bookmark">'.$iconList.' '.$w->buttonList.'</a>
						<button type="submit" name="save" class="btn not-btn-small btn-primary">'.$iconSave.' '.$w->buttonSave.'</button>
						<a class="btn btn-small btn-danger" href="./manage/bookmark/remove/'.$bookmark->bookmarkId.'">'.$iconRemove.' '.$w->buttonRemove.'</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
