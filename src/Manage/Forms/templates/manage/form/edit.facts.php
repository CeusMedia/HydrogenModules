<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\URL as Url;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var object $form */
/** @var array<object> $mailsCustomer */
/** @var array<object> $mailsManager */
/** @var bool $hasFills */
/** @var array<string,string|HtmlTag> $navButtons */
/** @var array<string> $references */

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconPrev	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconNext	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );

$statuses	= [
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
];
$optStatus	= HtmlElements::Options( $statuses, $form->status );

$types		= [
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
];
$optType	= HtmlElements::Options( $types, $form->type );

$optMailCustomer	= ['' => '- keine -'];
foreach( $mailsCustomer as $item )
	$optMailCustomer[$item->mailId]	= $item->title;
$optMailCustomer	= HtmlElements::Options( $optMailCustomer, $form->customerMailId );

$optMailManager		= ['' => '- keine -'];
foreach( $mailsManager as $item )
	$optMailManager[$item->mailId]	= $item->title;
$optMailManager		= HtmlElements::Options( $optMailManager, $form->managerMailId );

$panelReferrers	= '';
if( 0 ){
	$listReferences = '<em class="muted">Keine.</em>';
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
		HtmlTag::create( 'h3', 'Verwendung' ),
		HtmlTag::create( 'div', [
			$listReferences
		], ['class' => 'content-panel-inner'] )
	], ['class' => 'content-panel'] );
}

$form	= '
		<form action="./manage/form/edit/'.$form->formId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_formId">ID</label>
					<input type="text" name="formId" id="input_formId" class="span12" disabled="disabled" value="'.htmlentities( $form->formId, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $form->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_type">Typ</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_receivers">Empfänger <small class="muted">(mit Komma getrennt)</small></label>
					<input type="text" name="receivers" id="input_receivers" class="span12" value="'.htmlentities( $form->receivers, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_customerMailId">Ergebnis-Email an Kunden <small class="muted">(=Absender)</small></label>
					<select name="customerMailId" id="input_customerMailId" class="span12">'.$optMailCustomer.'</select>
				</div>
				<div class="span6">
					<label for="input_managerMailId">Ergebnis-Email an Manager <small class="muted">(=Empfänger)</small></label>
					<select name="managerMailId" id="input_managerMailId" class="span12">'.$optMailManager.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_forwardOnSubmit">URL-Weiterleitung nach Submit<!-- <small class="muted">(...)</small>--></label>
					<input type="text" name="forwardOnSubmit" id="input_forwardOnSubmit" class="span12" value="'.htmlentities( $form->forwardOnSubmit ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_forwardOnSuccess">URL-Weiterleitung bei Erfolg<!-- <small class="muted">(...)</small>--></label>
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
						benutze ALTCHA-Prüfung<!-- <small class="muted">(...)</small>-->
					</label>
				</div>
			</div>
			<div class="buttonbar">
				'.$navButtons['list'].'
				'.HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', [
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				] ).'
				'.$navButtons['nextView'].'
				'.HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
					'href'		=> './manage/form/remove/'.$form->formId,
					'class'		=> 'btn btn-danger',
					'disabled'	=> $hasFills ? 'disabled' : NULL,
					'onclick'	=> "return confirm('Wirklich ?');",
				] ).'
			</div>
		</form>';

$panelDetails	= HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$form
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel'] );

return $panelDetails.$panelReferrers;
