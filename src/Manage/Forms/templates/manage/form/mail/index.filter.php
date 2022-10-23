<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$buttonSubmit	= HtmlTag::create( 'button', $iconFilter.' filtern', array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-small btn-info'
) );
$buttonReset	= HtmlTag::create( 'a', $iconReset.'&nbsp;leeren', array(
	'href'	=> './manage/form/mail/filter/reset',
	'class'	=> 'btn btn-small btn-inverse'
) );

/*$optIdentifier	= array( '' => '- alle -');
foreach( $identifiers as $identifier )
	$optIdentifier[$identifier]	= $identifier;
$optIdentifier	= HtmlElements::Options( $optIdentifier, $filterIdentifier );
*/
$formatMap	= array(
	Model_Form_Mail::FORMAT_HTML	=> 'HTML',
	Model_Form_Mail::FORMAT_TEXT	=> 'Text',
);

$optFormat	= array( '' => '- alle -');
foreach( $formatMap as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat	= HtmlElements::Options( $optFormat, $filters->get( 'format' ) );

$roleTypeMap	= array(
	Model_Form_Mail::ROLE_TYPE_NONE				=> 'keinen',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_ALL		=> 'Kunde',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_RESULT	=> 'Kunde: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_REACT	=> 'Kunde: Reaktion',
	Model_Form_Mail::ROLE_TYPE_LEADER_ALL		=> 'Leiter',
	Model_Form_Mail::ROLE_TYPE_LEADER_RESULT	=> 'Leiter: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_LEADER_REACT		=> 'Leiter: Reaktion',
	Model_Form_Mail::ROLE_TYPE_MANAGER_ALL		=> 'Manager',
	Model_Form_Mail::ROLE_TYPE_MANAGER_RESULT	=> 'Manager: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_MANAGER_REACT	=> 'Manager: Reaktion',
);

$optRoleType	= array( '' => '- egal -');
foreach( $roleTypeMap as $roleTypeKey => $roleTypeLabel )
	$optRoleType[$roleTypeKey]	= $roleTypeLabel;
$optRoleType	= HtmlElements::Options( $optRoleType, $filters->get( 'roleType' ) );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/mail/filter" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_mailId">ID</label>
					<input type="text" name="mailId" id="input_mailId" class="span12" value="'.htmlentities( $filters->get( 'mailId' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
				<div class="span8">
					<label for="input_format">Format</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel <small class="muted">(ungefähr)</small></label>
					<input type="text"  name="title" id="input_title" class="span12" value="'.htmlentities( $filters->get( 'title' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_roleType">Nutzbar für </label>
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
