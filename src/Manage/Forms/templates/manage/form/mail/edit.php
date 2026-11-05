<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection DuplicatedCode */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Entity_Form_Mail $mail */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['edit'];

$modelForm	= new Model_Form( $env );
//$modelMail	= new Model_Form_Mail( $env );

$iconList	= new Icon( 'list' );
$iconView	= new Icon( 'eye' );
$iconSave	= new Icon( 'check' );
$iconRemove	= new Icon( 'remove' );
$iconMail	= new Icon( 'square' );
$iconForm	= new Icon( 'th' );

$withinForms		= array_merge(
	$modelForm->getAllByIndex( 'customerMailId', $mail->mailId ),
	$modelForm->getAllByIndex( 'managerMailId', $mail->mailId )
);
$listWithinForms	= HtmlTag::create( 'p', '<em class="muted">'.$w->noEntries.'</em>' );
if( $withinForms ){
	$list	= [];
	foreach( $withinForms as $item ){
		$link	= HtmlTag::create( 'a', $iconForm.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/edit/'.$item->formId,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$listWithinForms	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

$optRoleType	= $words['roleTypes'];
$optRoleType	= HtmlElements::Options( $optRoleType, (int) $mail->roleType );

$optFormat	= $words['formats'];
$optFormat	= HtmlElements::Options( $optFormat, $mail->format );

return '
<h2><span class="muted">'.$w->headingPrefix.'</span> '.$mail->title.'</h2>
<div class="content-panel">
	<!--<h3><span class="muted">Mails-Vorlage:</span> '.$mail->title.'</h3>-->
	<div class="content-panel-inner">
		<form action="./manage/form/mail/edit/'.$mail->mailId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $mail->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_identifier">'.$w->labelIdentifier.'</label>
					<input type="text" name="identifier" id="input_identifier" class="span12" value="'.htmlentities( $mail->identifier, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_subject">'.$w->labelSubject.'</label>
					<input type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $mail->subject, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_roleType">'.$w->labelRoleType.'</label>
					<select name="roleType" id="input_roleType" class="span12">'.$optRoleType.'</select>
				</div>
				<div class="span4">
					<label for="input_format">'.$w->labelFormat.'</label>
					<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $mail->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/mail" class="btn">'.$iconList.' '.$w->buttonList.'</a>
				<a href="./manage/form/mail/view/'.$mail->mailId.'" class="btn btn-info">'.$iconView.' '.$w->buttonView.'</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' '.$w->buttonSave.'</button>
				'.HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
					'href'		=> './manage/form/mail/remove/'.$mail->mailId,
					'class'		=> 'btn btn-danger',
					'onclick'	=> 'return confirm('.json_encode( $w->buttonRemoveConfirm ).');',
				] ).'
			</div>
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<h4>'.$w->headingWithinForms.'</h4>
		'.$listWithinForms.'
	</div>
</div>
<script>
jQuery(document).ready(function(){
	FormEditor.applyAceEditor("#input_content");
});
</script>';
