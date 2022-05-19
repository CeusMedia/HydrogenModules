<?php
$w		= (object) $words['index'];

$iconSave	= '<i class="icon-envelope icon-white"></i>';
if( $env->hasModule( 'UI_Font_FontAwesome' ) )
	$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope' ) );

extract( $this->populateTexts( array( 'before', 'after', 'top', 'right', 'bottom', 'privacy' ), 'html/info/contact/' ) );

$newsletter    = '';
if( $useNewsletter ){
	$inputTopics	= '';
	if( isset( $newsletterTopics ) && count( $newsletterTopics ) ){
		$list	= [];
		foreach( $newsletterTopics as $topic ){
			$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'topics[]',
				'value'		=> $topic->newsletterGroupId,
				'checked'	=> $topic->type == Model_Newsletter_Group::TYPE_AUTOMATIC ? 'checked' : NULL,
				'disabled'	=> $topic->type == Model_Newsletter_Group::TYPE_AUTOMATIC ? 'disabled' : NULL,
			) );
			$label		= UI_HTML_Tag::create( 'label', $checkbox.'&nbsp;'.$topic->title, array( 'class' => 'checkbox' ) );
			$list[]		= UI_HTML_Tag::create( 'li', $label );
		}
		$listTopics		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
		$inputTopics	= UI_HTML_Tag::create( 'blockquote', array(
			UI_HTML_Tag::create( 'label', $w->labelNewsletterTopics ),
			$listTopics,
		), array( 'class' => 'optional newsletter newsletter-true' ) );
	}
	$newsletter     = '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_newsletter" class="checkbox">
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'				=> 'checkbox',
						'name'				=> 'newsletter',
						'id'				=> 'input_newsletter',
						'class'				=> 'has-optionals',
						'value'				=> 'yes',
						'data-animation'	=> 'slide',
					) ).'
					'.$w->labelNewsletter.'
				</label>
				'.$inputTopics.'
			</div>
		</div>';
}

$captcha	= '';
if( $useCaptcha ){
	if( $useCaptcha === 'recaptcha' )
		$captcha	= '[captcha mode="recaptcha"]';
	else{
		$captcha	= '
	<div class="row-fluid">
		<div class="span6">
			<label for="input_captcha">'.$w->labelCaptcha.'&nbsp;<small class="muted">('.$w->labelCaptcha_suffix.')</small></label>
			'.UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'captcha',
				'id'			=> 'input_captcha',
				'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
				'maxlength'		=> '20',
				'required'		=> 'required',
				'value'			=> ''
			) ).'
		</div>
		<div class="span5 offset1">
			[captcha length="'.$captchaLength.'" strength="'.$captchaStrength.'"]
		</div>
	</div>';
	}
}

$honeypot	= '';
if( $useHoneypot ){
	$honeypot	= '
	<div style="display: none;">
		<input type="text" name="trap" value=""/>
	</div>';
}

$csrf		= '';
if( $useCsrf ){
	$helper	= new View_Helper_CSRF( $env );
	$csrf	= $helper->setFormName( 'Contact' )->render();
}

$content	= $textTop.'
<a id="'.$w->idPanel.'"></a>
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="'.$formPath.'" method="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="input_fullname" class="mandatory required">'.$w->labelFullName.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'fullname',
						'id'			=> 'input_fullname',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'maxlength'		=> '40',
						'required'		=> 'required',
						'value'			=> htmlentities( $fullname, ENT_QUOTES, 'UTF-8' ),
					) ).'
				</div>
				<div class="span7">
					<label for="input_email" class="mandatory required">'.$w->labelEmail.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'email',
						'id'			=> 'input_email',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'maxlength'		=> '100',
						'required'		=> 'required',
						'value'			=> htmlentities( $email, ENT_QUOTES, 'UTF-8' ),
					) ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_subject" class="mandatory required">'.$w->labelSubject.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'subject',
						'id'			=> 'input_subject',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'maxlength'		=> '100',
						'required'		=> 'required',
						'value'			=> htmlentities( $subject, ENT_QUOTES, 'UTF-8' ),
					) ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message" class="mandatory required">'.$w->labelMessage.'</label>
					'.UI_HTML_Tag::create( 'textarea', htmlentities( $message, ENT_QUOTES, 'UTF-8' ), array(
						'name'			=> 'message',
						'id'			=> 'input_message',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'rows'			=> '10',
						'required'		=> 'required',
					) ).'
				</div>
			</div>
			'.$newsletter.'
			'.$captcha.'
			'.$honeypot.'
			'.$textPrivacy.'
			'.$csrf.'
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' '.$w->buttonSave.'</button>
				<button type="reset" class="btn btn-small">'.$w->buttonReset.'</button>
			</div>
		</form>
	</div>
</div>
'.$textBottom;

if( $textRight )
	$content	= '
<div class="bs2-row-fluid bs3-row bs4-row">
	<div class="bs2-span7 bs3-col-md-7 bs4-col-md-7">
		'.$content.'
	</div>
	<div class="bs2-span4 bs3-col-md-4 bs4-col-md-4 bs2-offset1 bs3-col-md-offset-1 bs4-col-md-offset-1">
		'.$textRight.'
	</div>
</div>';

return $textBefore.$content.$textAfter;
?>
