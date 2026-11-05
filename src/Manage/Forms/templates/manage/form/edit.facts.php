<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\ADT\URL as Url;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\View $view */
/** @var object $form */
/** @var array<object> $mailsCustomer */
/** @var array<object> $mailsManager */
/** @var bool $hasFills */
/** @var array<string,string|HtmlTag> $navButtons */
/** @var array<string> $references */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['edit.facts'];

$iconList	= new Icon( 'list' );
$iconAdd	= new Icon( 'plus' );
$iconSave	= new Icon( 'check' );
$iconRemove	= new Icon( 'remove' );
$iconPrev	= new Icon( 'arrow-left' );
$iconNext	= new Icon( 'arrow-right' );

$statuses	= $words['statuses'];
$optStatus	= HtmlElements::Options( $statuses, $form->status );

$types		= $words['types'];
$optType	= HtmlElements::Options( $types, $form->type );

$optMailCustomer	= ['' => $w->optionNone];
foreach( $mailsCustomer as $item )
	$optMailCustomer[$item->mailId]	= $item->title;
$optMailCustomer	= HtmlElements::Options( $optMailCustomer, $form->customerMailId );

$optMailManager		= ['' => $w->optionNone];
foreach( $mailsManager as $item )
	$optMailManager[$item->mailId]	= $item->title;
$optMailManager		= HtmlElements::Options( $optMailManager, $form->managerMailId );

$panelReferrers	= '';
if( 0 ){
	$listReferences = '<em class="muted">'.$w->noReferrers.'</em>';
	if( [] !== $references ){
		$domains	= [];
		foreach( $references as $reference ){
			$url	= new Url( $reference );
			$domain	= $url->getHost();
			if( strlen( $url->getPath().$url->getQuery() ) < 2 )
				continue;
			if( !array_key_exists( $domain, $domains ) )
				$domains[$domain]   = [];
			$title  = preg_replace( '/^\//', '', $url->getPath() );
			if( strlen( $url->getQuery() ) > 0 ){
				$title	.= '<small class="muted">?'.$url->getQuery().'</small>';
			}
			$domains[$domain][] = HtmlTag::create( 'li', [
				HtmlTag::create( 'a', $title, [
					'href'		=> $reference,
					'target'	=> '_blank',
				])
			], ['class' => 'autocut']);
		}
		$lists = [];
		foreach( $domains as $domain => $domainReferences ){
			$list		= HtmlTag::create( 'ul', $domainReferences, ['class' => 'unstyled'] );
			$lists[]	= HtmlTag::create( 'h5', $domain ).$list;
		}
		$listReferences = HtmlTag::create( 'div', $lists );
	}

	$panelReferrers	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', $w->headingReferrers ),
		HtmlTag::create( 'div', [
			$listReferences
		], ['class' => 'content-panel-inner'] )
	], ['class' => 'content-panel'] );
}


return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/edit/'.$form->formId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_formId">'.$w->labelId.'</label>
					<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled" value="'.htmlentities( $form->formId, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $form->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_receivers">'.$w->labelReceivers.' <small class="muted">'.$w->labelReceiversSuffix.'</small></label>
					<input type="text" name="receivers" id="input_receivers" class="span12" value="'.htmlentities( $form->receivers, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_customerMailId">'.$w->labelCustomerMail.' <small class="muted">'.$w->labelCustomerMailSuffix.'</small></label>
					<select name="customerMailId" id="input_customerMailId" class="span12">'.$optMailCustomer.'</select>
				</div>
				<div class="span6">
					<label for="input_managerMailId">'.$w->labelManagerMail.' <small class="muted">'.$w->labelManagerMailSuffix.'</small></label>
					<select name="managerMailId" id="input_managerMailId" class="span12">'.$optMailManager.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_forwardOnSubmit">'.$w->labelForwardOnSubmit.'<!-- <small class="muted">(...)</small>--></label>
					<input type="text" name="forwardOnSubmit" id="input_forwardOnSubmit" class="span12" value="'.htmlentities( $form->forwardOnSubmit ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_forwardOnSuccess">'.$w->labelForwardOnSuccess.'<!-- <small class="muted">(...)</small>--></label>
					<input type="text" name="forwardOnSuccess" id="input_forwardOnSuccess" class="span12" value="'.htmlentities( $form->forwardOnSuccess ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_useAltcha" class="checkbox">
						'.HtmlTag::create( 'input', NULL, [
							'type'	=> "checkbox",
							'name'	=> "useAltcha",
							'id'	=> "input_useAltcha",
							'value'	=> "1",
							'checked'	=> $form->useAltcha ? 'checked' : NULL,
						] ).'
						'.$w->labelUseAltcha.'<!-- <small class="muted">(...)</small>-->
					</label>
				</div>
			</div>
			<div class="buttonbar">
				'.$navButtons['list'].'
				'.HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				] ).'
				'.$navButtons['nextView'].'
				'.HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
					'href'		=> './manage/form/remove/'.$form->formId,
					'class'		=> 'btn btn-danger',
					'disabled'	=> $hasFills ? 'disabled' : NULL,
					'onclick'	=> "return confirm('".addslashes( $w->buttonRemoveConfirm )."');",
				] ).'
			</div>
		</form>
	</div>
</div>
<div class="content-panel">
	<div class="content-panel-inner">
		<h3>Verwendung</h3>
		'.$listReferences.'
	</div>
</div>
';
