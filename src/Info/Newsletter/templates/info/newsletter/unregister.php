<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

extract( $view->populateTexts( ['unregister.top', 'unregister.bottom', 'unregister.info'], 'html/info/newsletter' ) );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-right'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->hasModule( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
	$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
}

$w	= (object) $words['unregister'];

if( !$reader ){
	return $textUnregisterTop.'
<div class="bs2-row-fluid bs3-row bs4-row">
	<div class="bs2-span6 bs3-col-md-6 bs4-col-md-6">
		<div class="content-panel content-panel-form">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./info/newsletter/unregister" method="post">
					<div class="bs2-row-fluid bs3-row bs4-row">
						<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
							<label for="input_email" class="mandatory">'.$w->labelEmail.'</label>
							<input type="text" name="email" id="input_email" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->email, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="search" class="btn not-btn-primary bs3-btn-default bs4-btn-secondary">'.$iconNext.'&nbsp;'.$w->buttonSearch.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>'.$textUnregisterBottom;
}

$listGroups	= HtmlTag::create( 'em', 'Keine Bereiche mehr abonniert.', ['class' => 'muted'] );
if( $groups ){
	$list	= [];
	foreach( $groups as $group ){
		$isRegisteredGroup	= array_key_exists( $group->newsletterGroupId, $reader->groups );
		$checkbox	= HtmlTag::create( 'input', NULL, array(
			'type'		=> 'checkbox',
			'value'		=> $group->newsletterGroupId,
			'name'		=> 'groupIds[]',
			'id'		=> 'input_groupIds_'.$group->newsletterGroupId,
			'checked'	=> $isRegisteredGroup ? "checked" : NULL,
			'class'		=> 'bs4-form-check-input',
		) );
		$label	= HtmlTag::create( 'label', $checkbox.'&nbsp;<span class="bs4-form-check-label">'.$group->title.'</span>', ['class' => 'checkbox'] );
		$list[]	= HtmlTag::create( 'li', $label, ['class' => 'bs4-form-check'] );
	}
	$listGroups	= HtmlTag::create( 'ul', $list, ['class' => 'bs2-unstyled bs3-unstyled bs4-list-unstyled'] );
}

return $textUnregisterTop.'
<div class="bs2-row-fluid bs3-row bs4-row">
	<div class="bs2-span8 bs3-col-md-8 bs4-col-md-8">
		<div class="content-panel content-panel-form">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./info/newsletter/unregister" method="post">
					<div class="bs2-row-fluid bs3-row bs4-row">
						<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
							<label for="input_email" class="mandatory">'.$w->labelEmail.'</label>
							<input type="text" name="email" id="input_email" readonly="readonly" class="bs2-span6 bs3-col-md-6 bs3-form-control bs4-col-md-6 bs4-form-control" value="'.htmlentities( $data->email, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
					</div>
					<hr/>
					<div class="bs2-row-fluid bs3-row bs4-row">
						<div class="bs2-span12 bs3-col-md-12 bs3-checkbox bs4-col-md-12">
							<label for="input_disable" class="bs2-checkbox bs4-form-check">
								<input type="checkbox" name="disable" id="input_disable" class="bs4-form-check-input">
								<span class=" bs4-form-check-label">
									'.$w->labelDisable.'
								</span>
							</label>
						</div>
					</div>
					<div id="mode-incomplete">
						<hr/>
						<div class="bs2-row-fluid bs3-row bs4-row">
							<div class="bs2-span12 bs3-col-md-12 bs4-col-md-12">
								<h4>'.$w->headingGroups.'</h4>
								<label class="bs2-radio bs3-radio bs4-form-check">
									<input type="radio" name="mode" id="input_mode_all" value="all" class="bs4-form-check-input"/>
									<span class="bs4-form-check-label">'.$w->labelAllGroups.'</span>
								</label>
								<label class="bs2-radio bs3-radio bs4-form-check">
									<input type="radio" name="mode" id="input_mode_specific" value="specific" class="bs4-form-check-input" checked="checked"/>
									<span class="bs4-form-check-label">'.$w->labelSomeGroups.'</span>
								</label>
							</div>
						</div>
						<div class="bs2-row-fluid bs3-row bs4-row" id="list-newsletter-topics">
							<div class="newsletter-groups-selector-nested">
								<div class="bs2-visible-phone bs3-visible-sm-block bs4-d-none bs4-d-sm-block bs4-d-md-none">
									<br/>
									<h4>'.$w->headingPhoneGroups.'</h4>
									<p class="muted"><em>'.$w->hintPhoneGroups.'</em></p>
								</div>
								'.$listGroups.'
							</div>
						</div>
					</div>
					<br/>
					<div class="buttonbar">
						<a href="./info/newsletter/unregister" class="btn bs3-btn-default bs4-btn-secondary">'.$iconCancel.'&nbsp;abbrechen</a>
						<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="bs2-span3 bs2-offset1 bs3-col-md-3 bs3-md-offset-1 bs4-offset-md-1">
		'.$textUnregisterInfo.'
	</div>
</div>'.$textUnregisterBottom.'
<style>
@media (min-width: 768px){
	.newsletter-groups-selector-nested {
		margin-left: 2em;
		}
	}
</style>
<script>
var ModuleInfoNewsletter = {
	init: function(){
		$("#input_disable").on("change", ModuleInfoNewsletter.handleUnregisterMode)
		$("label.radio input, label.form-check input").on("change", ModuleInfoNewsletter.handleUnregisterType);
	},
	handleUnregisterMode: function(){
		$("#mode-incomplete").slideToggle();
	},
	handleUnregisterType: function(){
		$("#list-newsletter-topics").slideToggle();
	}
}
$(document).ready(ModuleInfoNewsletter.init);
</script>';
