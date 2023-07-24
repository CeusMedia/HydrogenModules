<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
if( $env->hasModule( 'UI_Font_FontAwesome' ) )
	$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$w		= (object) $words['index'];

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/newsletter' ) );

$optGender	= $words['genders'];
$optGender	= HtmlElements::Options( $optGender, $data->get( 'gender' ) );

$topics		= '';
if( $groups ){
	$list   = [];
	foreach( $groups as $group ){
		$checkbox	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'name'		=> 'groups[]',
			'value'		=> $group->newsletterGroupId,
			'checked'	=> $group->type == 2 || $group->isChecked ? 'checked' : NULL,
			'disabled'	=> $group->type == 2 ? 'disabled' : NULL,
			'class'		=> 'bs4-form-check-input',
		] );
		$label  = HtmlTag::create( 'label', $checkbox.'&nbsp;<span class="bs4-form-check-label">'.$group->title.'</span>', ['class' => 'bs2-checkbox bs4-form-check'] );
		$list[] = HtmlTag::create( 'li', $label );
	}
	if( count( $list ) >= 2 ){
		$list	= HtmlTag::create( 'ul', $list, ['class' => 'bs2-unstyled bs3-list-unstyled bs4-list-unstyled newsletter-topic-list'] );
		$topics	= '<div class="bs2-row-fluid bs3-row bs4-row">
			<div class="bs2-span12 bs3-col-md-12 bs4-col-md-12">
				<label>'.$w->labelTopics.'</label>
				'.$list.'
			</div>
		</div>';
	}
}

$panelLatest	= '';
if( $latest ){
	$helper	= new View_Helper_Newsletter( $env, $latest->newsletterTemplateId );
	$panelLatest	= '
<div class="content-panel content-panel-info">
	<h3>... Latest ...</h3>
	<div class="content-panel-inner">
		<div style="width: 133.33%; height: 133.33%; transform: scale(0.75); transform-origin: 0 0 0">
			<iframe src="./info/newsletter/preview" style="width: 100%; height: 500px;" frameborder="0"/>
		</div>
	</div>
</div>';
}

return $textIndexTop.'
<div class="content-panel content-panel-form">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form name="" action="./info/newsletter" method="post" onsubmit="return checkNewsletterRegisterForm();">
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_gender">'.$w->labelGender.'</label>
					<select name="gender" id="input_gender" class="bs2-span12 bs3-form-control bs4-form-control">'.$optGender.'</select>
				</div>
				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_prefix">'.$w->labelPrefix.'</label>
					<input type="text" name="prefix" id="input_prefix" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->get( 'prefix' ), ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="bs2-span3 bs3-col-md-3 bs3-form-group bs4-col-md-3 bs4-form-group">
					<label for="input_firstname" class="mandatory required">'.$w->labelFirstname.'</label>
					<input type="text" name="firstname" id="input_firstname" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->get( 'firstname' ), ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="bs2-span5 bs3-col-md-5 bs3-form-group bs4-col-md-5 bs4-form-group">
					<label for="input_surname" class="mandatory required">'.$w->labelSurname.'</label>
					<input type="text" name="surname" id="input_surname" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->get( 'surname' ), ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span7 bs3-col-md-7 bs3-form-group bs4-col-md-7 bs4-form-group">
					<label for="input_email" class="mandatory" class="mandatory required">'.$w->labelEmail.'</label>
					<input type="email" name="email" id="input_email" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->get( 'email' ), ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="bs2-span5 bs3-col-md-5 bs3-form-group bs4-col-md-5 bs4-form-group">
					<label for="input_institution">'.$w->labelInstitution.'</label>
					<input type="text" name="institution" id="input_institution" class="bs2-span12 bs3-form-control bs4-form-control" value="'.htmlentities( $data->get( 'institution' ), ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			'.$topics.'
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-checkbox bs4-col-md-12">
					<label>'.$w->labelAccept.'</label>
					<label class="bs2-checkbox bs4-form-check">
						<input type="checkbox" name="accept" id="input_accept" value="1" class="bs4-form-check-input" required="required"/>
						<small class="bs4-form-check-label">
							'.$w->acceptRules.'
						</small>
					</label>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" id="button_save" class="btn btn-primary">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
<!--'.$panelLatest.'-->
'.$textIndexBottom;
