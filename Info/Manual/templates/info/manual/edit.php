<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
$helperNav->setActivePageId( $pageId );

$helperNav	= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );
$helperNav->setActivePageId( $pageId );

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => "icon-arrow-left" ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => "icon-ok icon-white" ) );
$iconPreview	= HtmlTag::create( 'i', '', array( 'class' => "icon-eye-open" ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => "icon-remove icon-white" ) );
$iconUp			= HtmlTag::create( 'i', '', array( 'class' => "icon-arrow-up" ) );
$iconDown		= HtmlTag::create( 'i', '', array( 'class' => "icon-arrow-down" ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-arrow-left" ) );
	$iconPreview	= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-eye" ) );
	$iconSave		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-check" ) );
	$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );
	$iconUp			= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-chevron-up" ) );
	$iconDown		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-fw fa-chevron-down" ) );
}

$optParentId	= array( '' => '- ohne -' );
foreach( $folders as $folder )
	$optParentId[$folder->manualPageId]	= $folder->title;
$optParentId	= HtmlElements::Options( $optParentId, $page->parentId );

$buttonAdd	= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconReload		= HtmlTag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-primary" ) );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= HtmlTag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

$panelEdit	= '
		<div class="content-panel">
			<h3><span class="muted">'.$words['edit']['heading'].' </span> '.htmlentities( $file, ENT_QUOTES, 'UTF-8' ).'</h3>
			<div class="content-panel-inner">
				<form action="./info/manual/edit/'.$page->manualPageId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_content">'.$words['edit']['labelContent'].'</label>
							<!--noShortcode-->
							'.HtmlTag::create( 'textarea', $content, array(
								'class'		=> "span12 CodeMirror-auto ace-auto",
								'name'		=> "content",
								'id'		=> "input_content",
								'rows'		=> $moduleConfig->get( 'editor.rows' ),
								'data-ace-option-max-lines'	=> 20,
							) ).'
<!--							<textarea class="span12 CodeMirror-auto ace-auto" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>-->
							<!--/noShortcode-->
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">'.$words['edit']['labelTitle'].'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $page->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_parentId">'.$words['edit']['labelParent'].'</label>
							<select name="parentId" id="input_parentId" class="span12">'.$optParentId.'</select>
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
						<a href="./info/manual/removePage/'.$pageId.'-'.$view->urlencode( $file ).'" class="btn btn-small btn-danger" onclick="return confirm(\''. addslashes( $words['edit']['buttonRemoveConfirm'] ).'\');">'.$iconRemove.' '.$words['edit']['buttonRemove'].'</a>
<!--						&nbsp;&nbsp;|&nbsp;&nbsp;
						<a href="./info/manual/movePageUp/'.$pageId.'-'.$view->urlencode( $file ).'" class="btn btn-small">'.$iconUp.' '.$words['edit']['buttonMoveUp'].'</a>
						<a href="./info/manual/movePageDown/'.$pageId.'-'.$view->urlencode( $file ).'" class="btn btn-small">'.$iconDown.' '.$words['edit']['buttonMoveDown'].'</a>
-->
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';

return '
<div class="bs2-row-fluid bs4-row">
	<div class="bs2-span3 bs4-col-lg-3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		<hr/>
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="bs2-span9 bs4-col-lg-9">
		'.$panelEdit.'
	</div>
</div>';
