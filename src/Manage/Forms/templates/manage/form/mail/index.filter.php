<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Dictionary $filters */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index.filter'];

$iconFilter		= new Icon( 'search' );
$iconReset		= new Icon( 'search-minus' );

$buttonSubmit	= HtmlTag::create( 'button', $iconFilter.' '.$w->buttonFilter, [
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-small btn-info'
] );
$buttonReset	= HtmlTag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, [
	'href'	=> './manage/form/mail/filter/reset',
	'class'	=> 'btn btn-small btn-inverse'
] );

/*$optIdentifier	= ['' => '- alle -'];
foreach( $identifiers as $identifier )
	$optIdentifier[$identifier]	= $identifier;
$optIdentifier	= HtmlElements::Options( $optIdentifier, $filterIdentifier );
*/
$formatMap	= [
	Model_Form_Mail::FORMAT_HTML	=> $words['formats'][Model_Form_Mail::FORMAT_HTML],
	Model_Form_Mail::FORMAT_TEXT	=> $words['formats'][Model_Form_Mail::FORMAT_TEXT],
];

$optFormat	= ['' => $w->optionAll];
foreach( $formatMap as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat	= HtmlElements::Options( $optFormat, $filters->get( 'format' ) );

$roleTypeMap	= $words['roleTypes'];

$optRoleType	= ['' => $w->optionAny];
foreach( $roleTypeMap as $roleTypeKey => $roleTypeLabel )
	$optRoleType[$roleTypeKey]	= $roleTypeLabel;
$optRoleType	= HtmlElements::Options( $optRoleType, $filters->get( 'roleType' ) );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/mail/filter" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_mailId">'.$w->labelId.'</label>
					<input type="text" name="mailId" id="input_mailId" class="span12" value="'.htmlentities( $filters->get( 'mailId' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
				<div class="span8">
					<label for="input_format">'.$w->labelFormat.'</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$w->labelTitle.' <small class="muted">'.$w->labelApproximate.'</small></label>
					<input type="text"  name="title" id="input_title" class="span12" value="'.htmlentities( $filters->get( 'title' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_roleType">'.$w->labelRoleType.' </label>
					<select name="roleType" id="input_roleType" class="span12">'.$optRoleType.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					'.$buttonSubmit.'
					'.$buttonReset.'
				</div>
			</div>
		</form>
	</div>
</div>';
