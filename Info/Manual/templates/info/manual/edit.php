<?php

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
$helperNav->setActivePageId( $pageId );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-arrow-left" ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-ok icon-white" ) );
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-eye-open" ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-remove icon-white" ) );
$iconUp			= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-arrow-up" ) );
$iconDown		= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-arrow-down" ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
	$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-eye" ) );
	$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );
	$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );
	$iconUp			= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-chevron-up" ) );
	$iconDown		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-chevron-down" ) );
}

$buttonAdd	= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-primary" ) );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

return '
<div class="row-fluid">
	<div class="span3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3><span class="muted">'.$words['edit']['heading'].' </span> '.htmlentities( $file, ENT_QUOTES, 'UTF-8' ).'</h3>
			<div class="content-panel-inner">
				<form action="./info/manual/edit/'.$page->manualPageId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_content">'.$words['edit']['labelContent'].'</label>
							<textarea class="span12 CodeMirror-auto ace-auto" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">'.$words['edit']['labelTitle'].'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
		<!--				<div class="span2">
							<label for="input_version">'.$words['add']['labelVersion'].'</label>
							<input type="number" min="1" name="version" id="input_version" class="span12" value="'.htmlentities( $page->version, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_rank">'.$words['add']['labelRank'].'</label>
							<input type="number" min="1" name="rank" id="input_rank" class="span12" value="'.htmlentities( $page->rank, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>-->
		<!--				<div class="span4">
							<label>&nbsp;</label>
							<label class="checkbox">
								<input type="checkbox" name="backup"/>
								'.$words['edit']['labelBackup'].'
							</label>
						</div>-->
					</div>
					<div class="buttonbar">
						<a href="./info/manual/page/'.$pageId.'-'.$view->urlencode( $file ).'" class="btn">'.$iconCancel.' '.$words['edit']['buttonCancel'].'</a>
						<button type="submit" name="save" class="btn btn-success">'.$iconSave.' '.$words['edit']['buttonSave'].'</button>
						<a href="./info/manual/page/'.$pageId.'-'.$view->urlencode( $file ).'" class="btn">'.$iconPreview.' '.$words['edit']['buttonPreview'].'</a>
						<a href="./info/manual/removePage/'.base64_encode( $file ).'" class="btn btn-small btn-danger" onclick="return confirm(\''. addslashes( $words['edit']['buttonRemoveConfirm'] ).'\');">'.$iconRemove.' '.$words['edit']['buttonRemove'].'</a>
						&nbsp;&nbsp;|&nbsp;&nbsp;
						<a href="./info/manual/movePageUp/'.base64_encode( $file ).'" class="btn btn-small">'.$iconUp.' '.$words['edit']['buttonMoveUp'].'</a>
						<a href="./info/manual/movePageDown/'.base64_encode( $file ).'" class="btn btn-small">'.$iconDown.' '.$words['edit']['buttonMoveDown'].'</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';

?>
