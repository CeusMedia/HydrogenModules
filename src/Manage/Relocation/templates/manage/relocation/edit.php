<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var View_Manage_Relocation $view */
/** @var object $relocation */
/** @var string $shortcut */

$w		= (object) $words['edit'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonBack, [
	'href'		=> './manage/relocation',
	'class'		=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-success',
] );
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'href'		=> './manage/relocation/remove/'.$relocation->relocationId,
	'class'		=> 'btn btn-small btn-danger',
	'onclick'	=> htmlentities( 'return confirm("'.$w->buttonRemove_confirm.'")', ENT_QUOTES, 'UTF-8' ),
] );

$optStatus		= HtmlElements::Options( $words['states'], $relocation->status );
$urlService		= $env->url.'info/relocation/'.$relocation->relocationId;
$urlShortcut	= $env->url.$shortcut.'/'.$relocation->relocationId;

$panelEdit	= '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/relocation/edit/'.$relocation->relocationId.'" method="post">
					<div class="row-fluid">
							<div class="span10">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $relocation->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">'.$w->labelUrl.' <small class="muted">'.$w->labelUrl_suffix.'</small></label>
							<input type="text" name="url" id="input_url" class="span12" value="'.htmlentities( $relocation->url, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label>'.$w->labelUrlService.' <small class="muted">'.$w->labelUrlService_suffix.'</small></label>
							<input type="text" class="span12" value="'.$urlService.'" readonly="readonly"/>
						</div>
					</div>
					<div class="row-fluid" '.( $shortcut ? '' : 'style="display: none"' ).'>
						<div class="span12">
							<label>'.$w->labelUrlShortcut.' <small class="muted">'.$w->labelUrlShortcut_suffix.'</small></label>
							<input type="text" class="span12" value="'.$urlShortcut.'" readonly="readonly"/>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
';

$panelInfo	= $view->loadTemplateFile( 'manage/relocation/edit.info.php' );

extract( $view->populateTexts( ['edit.top', 'edit.bottom'], 'html/manage/relocation/' ) );

return $textEditTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
	</div>
	<div class="span4">
		'.$panelInfo.'
	</div>
</div>
'.$textEditBottom;
