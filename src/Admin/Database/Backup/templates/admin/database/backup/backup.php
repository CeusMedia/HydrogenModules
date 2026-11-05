<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $path */

$w				= (object) $words['backup'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'		=> './admin/database/backup',
	'class'		=> 'btn',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
] );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/backup" method="post">
		<p>'.$w->description.'</p>
		<div class="row-fluid">
			<div class="span4">
				<label for="input_path">'.$w->labelPath.'</label>
				<input type="text" name="path" id="input_path" class="span12" readonly="readonly" value="'.htmlentities( $path, ENT_QUOTES, 'UTf-8' ).'"/>
			</div>
			<div class="span8">
				<label for="input_comment">'.$w->labelComment.'</label>
				<input type="text" name="comment" id="input_comment" class="span12"/>
			</div>
		</div>
		<div class="alert alert-info">
			'.$w->noteDuration.'
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonSave.'
		</div>
	</div>
</div>';
