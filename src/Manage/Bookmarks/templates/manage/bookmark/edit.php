<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Bookmark $view */
/** @var object $bookmark */

$iconList		= HtmlTag::create( 'i', '', ['class' => 'icon-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
}

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				'.$view->renderList( $bookmark->bookmarkId ).'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>Lesezeichen verändern</h3>
			<div class="content-panel-inner">
				<form action="./manage/bookmark/edit/'.$bookmark->bookmarkId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">Internet-Adresse <small class="muted">(vollständige URL)</small></label>
							<input class="span12" type="text" name="url" id="input_url" required="required" value="'.htmlentities( $bookmark->url, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							<input class="span12" type="text" name="title" id="input_title" required="required" value="'.htmlentities( $bookmark->title, ENT_QUOTES, 'UTF-8' ).'">
						</div>
					</div>
					<div class="buttonbar">
						<a class="btn btn-small" href="./manage/bookmark">'.$iconList.' Liste</a>
						<button type="submit" name="save" class="btn not-btn-small btn-primary">'.$iconSave.' speichern</button>
						<a class="btn btn-small btn-danger" href="./manage/bookmark/remove/'.$bookmark->bookmarkId.'">'.$iconRemove.' entfernen</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
