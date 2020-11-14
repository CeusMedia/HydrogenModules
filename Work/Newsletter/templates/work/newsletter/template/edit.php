<?php
$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$isUsed	= FALSE;
$currentTab		= (int) $this->env->getSession()->get( 'work.newsletter.template.content.tab' );
$tabs			= $words->tabs;
$tabsContent	= $this->renderTabs( $tabs, 'template/setContentTab/'.$templateId.'/', $currentTab );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';
$iconCopy		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clone' ) ).'&nbsp;';
$iconRefresh	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-refresh' ) ).'&nbsp;';
$iconExport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) ).'&nbsp;';

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.$words->edit->buttonCancel, array(
	'class'		=> "btn btn-small",
	'href'		=> "./work/newsletter/template/index",
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.$words->edit->buttonSave, array(
	'type'			=> "submit",
	'class'			=> "btn btn-primary".( $isUsed ? ' disabled' : '' ),
	'name'			=> "save",
	'readonly'		=> $isUsed ? 'readonly' : NULL,
	'onmousedown'	=> $isUsed ? "alert('".$words->edit->buttonSaveDisabled."');" : NULL,
) );
$buttonPreview	= UI_HTML_Tag::create( 'button', $iconPreview.$words->edit->buttonPreview, array(
	'type'			=> "button",
	'class'			=> "btn btn-info",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-preview",
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("./work/newsletter/template/preview/'.$format.'/'.$templateId.'");'
) );
/*
$buttonPreview	= UI_HTML_Tag::create( 'a', $iconPreview.$words->edit->buttonPreview, array(
	'class'		=> "btn btn-info",
	'href'		=> './work/newsletter/template/preview/'.$format.'/'.$templateId.'/1',
	'target'	=> "NewsletterTemplatePreview",
) );*/
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.$words->edit->buttonRemove, array(
	'class'		=> "btn btn-danger",
	'href'		=> $isUsed ? '#' : "./work/newsletter/template/remove/".$templateId,
	'disabled'	=> $isUsed ? 'disabled' : NULL,
	'onclick'	=> $isUsed ? "alert('".$words->edit->buttonRemoveDisabled."'); return false;" : NULL,
) );
$buttonCopy		= UI_HTML_Tag::create( 'a', $iconCopy.$words->edit->buttonCopy, array(
	'class'		=> "btn btn-success btn-small",
	'href'		=> "./work/newsletter/template/add?templateId=".$templateId
) );
$buttonExport	= UI_HTML_Tag::create( 'a', $iconExport.$words->edit->buttonExport, array(
	'class'		=> "btn",
	'href'		=> "./work/newsletter/template/export/".$templateId
) );

$buttons		= UI_HTML_Tag::create( 'div', join( ' ', array(
	$buttonCancel,
	$buttonSave,
	$buttonPreview,
	$buttonExport,
//	$buttonRemove,
//	$buttonCopy,
) ), array( 'class' => 'buttonbar' ) );

switch( $currentTab ){
	case 0:
		$content	= $view->loadTemplateFile( 'work/newsletter/template/edit.details.php', array( 'buttons' => $buttons ) );
		break;
	case 1:
		$content	= $view->loadTemplateFile( 'work/newsletter/template/edit.html.php', array( 'buttons' => $buttons ) );
		break;
	case 2:
		$content	= $view->loadTemplateFile( 'work/newsletter/template/edit.text.php', array( 'buttons' => $buttons ) );
		break;
	case 3:
		$content	= $view->loadTemplateFile( 'work/newsletter/template/edit.style.php', array( 'buttons' => $buttons ) );
		break;
	case 4:
		$content	= $view->loadTemplateFile( 'work/newsletter/template/edit.styles.php', array( 'buttons' => $buttons ) );
		break;
	default:
		throw new InvalidArgumentException( 'Invalid tab: '.$currentTab );
}
$tabsContent	.= UI_HTML_Tag::create( 'div', $content, array( 'tab-content' ) );

$modalPreview	= '
<div id="modal-preview" class="modal hide -fade preview">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>'.sprintf( $words->preview->heading, $template->title ).'</h3>
	</div>
	<div class="modal-body">
		<div>
			<iframe></iframe>
		</div>
	</div>
	<div class="modal-footer">
<!--		<button type="button" class="btn btn-info" id="preview-refresh">'.$iconRefresh.$words->preview->buttonRefresh.'</button>-->
<!--		<button type="button" class="btn btn-small btn-warning" onclick="ModuleWorkNewsletter.showPreview(\'./work/newsletter/template/preview/'.$format.'/'.$templateId.'/1/1\');">'.$words->preview->buttonOffline.'</button>-->
		<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.$iconRemove.$words->preview->buttonClose.'</button>
	</div>
</div>';

$modalStyleAdd	= '
<div id="modal-style-add" class="modal hide fade">
	<form action="./work/newsletter/template/addStyle/'.$templateId.'" method="post">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><small class="muted"></small>'.$words->addStyle->heading.'</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid">
			<label for="input_style_url">'.$words->addStyle->labelUrl.'</label>
			<input type="text" name="style_url" id="input_style_url" class="span12"/>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.$words->addStyle->buttonCancel.'</button>
		<button type="submit" class="btn btn-success">'.$words->addStyle->buttonAdd.'</button>
	</div>
	</form>
</div>';

extract( $view->populateTexts( array( 'above', 'bottom', 'top' ), 'html/work/newsletter/template/edit/', array(
	'words'		=> $words,
	'template'	=> $template
) ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	<!--<a href="./work/newsletter/template" class="btn btn-mini">'.$iconCancel.$words->edit->buttonList.'</a>-->
	'.$textAbove.'
	<form action="./work/newsletter/template/edit/'.$templateId.'" method="post">
		'.$tabsContent.'
	</form>
</div>'.$textBottom.$modalPreview.$modalStyleAdd;
?>
