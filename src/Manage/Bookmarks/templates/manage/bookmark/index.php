<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Bookmark $view */
/** @var array<string,array<string,string>> $words */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
}

$w	= (object) $words['index'];

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>'.$words['list']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$view->renderList().'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$view->loadContentFile( 'html/manage/bookmark/index.html' ).'
				<div class="buttonbar">
					<a href="./manage/bookmark/add" class="btn btn-small btn-success">'.$iconAdd.' '.$w->buttonAdd.'</a>
				</div>
			</div>
		</div>
	</div>
</div>
';
