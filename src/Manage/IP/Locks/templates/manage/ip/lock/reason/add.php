<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['add'];

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-check icon-white'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './manage/ip/lock/reason',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optStatus	= HtmlElements::Options( [
	Model_IP_Lock_Reason::STATUS_ENABLED	=> $words['statuses'][Model_IP_Lock_Reason::STATUS_ENABLED],
	Model_IP_Lock_Reason::STATUS_DISABLED	=> $words['statuses'][Model_IP_Lock_Reason::STATUS_DISABLED],
] );

$panelAdd	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock/reason">'.$w->heading.'</a> '.$w->headingNew.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/ip/lock/reason/add" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title" class="required mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span1">
					<label for="input_code"><abbr title="'.htmlentities( $w->labelCode_title, ENT_QUOTES, 'UTF-8' ).'">'.$w->labelCode.'</abbr></label>
					<input type="text" name="code" id="input_code" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_duration"><abbr title="'.htmlentities( $w->labelDuration_title, ENT_QUOTES, 'UTF-8' ).'">'.$w->labelDuration.'</abbr></label>
					<input type="text" name="pattern" id="input_duration" class="span12"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.' <small class="muted">'.$w->labelDescription_suffix.'</small></label>
					<textarea name="description" class="span12" rows="5"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
';

$tabs	= View_Manage_IP_Lock::renderTabs( $env, 'reason' );
return $tabs.HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		 $panelAdd
	).
	HTML::DivClass( 'span4' )
);
