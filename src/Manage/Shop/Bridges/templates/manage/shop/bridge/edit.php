<?php

/** @var array<string,array<string,string>> $words */

$w		= (object) $words['form'];
$wAction	= (object) $words['edit'];

$panelEdit	= '
<div class="content-panel">
	<h3>'.$wAction->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/shop/bridge/edit/'.$bridgeId.'" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $bridge->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span5">
					<label for="input_class">'.$w->labelClass.'</label>
					<input type="text" name="class" id="input_class" class="span12" required="required" value="'.htmlentities( $bridge->class, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<hr/>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_frontendController">'.$w->labelFrontendController.'</label>
					<input type="text" name="frontendController" id="input_frontendController" class="span12" required="required" value="'.htmlentities( $bridge->frontendController, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_backendController">'.$w->labelBackendController.'</label>
					<input type="text" name="backendController" id="input_backendController" class="span12" required="required" value="'.htmlentities( $bridge->backendController, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_frontendUriPath">'.$w->labelFrontendUriPath.'</label>
					<input type="text" name="frontendUriPath" id="input_frontendUriPath" class="span12" required="required" value="'.htmlentities( $bridge->frontendUriPath, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_backendUriPath">'.$w->labelBackendUriPath.'</label>
					<input type="text" name="backendUriPath" id="input_backendUriPath" class="span12" required="required" value="'.htmlentities( $bridge->backendUriPath, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label for="input_articleTableName">'.$w->labelArticleTableName.'</label>
					<input type="text" name="articleTableName" id="input_articleTableName" class="span12" required="required" value="'.htmlentities( $bridge->articleTableName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span5">
					<label for="input_articleIdColumn">'.$w->labelArticleIdColumn.'</label>
					<input type="text" name="articleIdColumn" id="input_articleIdColumn" class="span12" required="required" value="'.htmlentities( $bridge->articleIdColumn, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>

			<div class="buttonbar">
				<a href="./manage/shop/bridge" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<a href="./manage/shop/bridge/remove/'.$bridgeId.'" class="btn btn-small btn-danger"><i class="icon-trash icon-white"></i> '.$w->buttonRemove.'</a>
			</div>
		</form>
	</div>
</div>';

$panelList	= $view->loadTemplateFile( 'manage/shop/bridge/index.list.php' );

$tabs	= View_Manage_Shop::renderTabs( $env, 'bridge' );

return $tabs.'
<!--<h2 class="muted">Shop Bridges</h2>-->
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
	<div class="span8">
		'.$panelEdit.'
	</div>
</div>';
