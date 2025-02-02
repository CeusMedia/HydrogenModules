<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Bookmark $view */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
}

return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				'.$view->renderList().'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>Lesezeichen</h3>
			<div class="content-panel-inner">
				Hier kannst du Verknüpfungen zu Internetseiten anderer Anbieter speichern, die du öfter einbinden willst.<br/>
				Die notierten Links lassen sich dann z.B. im HTML-Editor der Seiten oder Neuigkeiten komfortabel verwenden.<br/>
				<br/>
				<div class="buttonbar">
					<a href="./manage/bookmark/add" class="btn btn-small btn-success">'.$iconAdd.' hinzufügen</a>
				</div>
			</div>
		</div>
	</div>
</div>
';
