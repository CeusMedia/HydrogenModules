<?php
$w		= (object) $words['index'];

extract( $this->populateTexts( array( 'before', 'after', 'top', 'right', 'bottom' ), 'html/info/contact/' ) );
$newsletter    = '';
if( $useNewsletter ){
	$inputTopics	= '';
	if( $newsletterTopics ){
		$list	= array();
		foreach( $newsletterTopics as $topic ){
			$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'topics[]',
				'value'		=> $topic->newsletterTopicId,
			);
			$label		= UI_HTML_Tag::create( 'label', $checkbox.'&nbsp;'.$topic->title, array( 'class' => 'checkbox' ) );
			$list[]		= UI_HTML_Tag::create( 'li', $label );
		}
		$listTopics		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
		$inputTopics	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $w->labelNewsletterTopics ),
			$listTopics,
		), array( 'class' => 'optional newsletter newssletter-yes' ) );
	}
	$newsletter     = '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_captcha" class="checkbox">
					<input type="checkbox" name="newsletter" id="input_newsletter" value="yes" class="has-optionals" data-animation="slide"/>
					'.$w->labelNewsletter.'
				</label>
				'.$inputTopics.'
			</div>
		</div>';
}

$captcha	= '';
if( $useCaptcha ){
	$captcha	= new UI_Image_Captcha();
	$captcha->background	= array( 0, 0, 0 );
	$captcha->background	= array( 255, 255, 255 );
//	$captcha->width			= 100;
	$captcha->height		= 55;
	$captcha->fontSize		= 16;
	$captcha->offsetX		= 0;
	$captcha->offsetY		= 0;
	$captcha->font			= "./themes/common/font/tahoma.ttf";
	$captcha->generateImage( $captchaWord, $captchaFilePath );

	$captcha	= '
	<div class="row-fluid">
		<div class="span5">
			<label for="input_captcha">'.$w->labelCaptcha.'&nbsp;<small class="muted">('.$w->labelCaptcha_suffix.')</small></label>
			<input type="text" name="captcha" id="input_captcha" value="" class="span12"/>
		</div>
		<div class="span7">
			<img src="'.$captchaFilePath.'" title="CAPTCHA" class="img-captcha">
		</div>
	</div>';
}

$honeypot	= '';
if( $useHoneypot ){
	$honeypot	= '
	<div style="display: none;">
		<input type="text" name="trap" value=""/>
	</div>';
}

$content	= $textTop.'
<a id="'.$w->idPanel.'"></a>
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="'.$formPath.'" method="post">
			<div class="row-fluid">
				<div class="span5">
					<label for="input_name">'.$w->labelName.'</label>
					<input type="text" name="name" id="input_name" class="span12" maxlength="40" required="required" value="'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span7">
					<label for="input_email">'.$w->labelEmail.'</label>
					<input type="text" name="email" id="input_email" class="span12" maxlength="50" required="required" value="'.htmlentities( $email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_subject">'.$w->labelSubject.'</label>
					<input type="text" name="subject" id="input_subject" class="span12" maxlength="80" required="required" value="'.htmlentities( $subject, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message">'.$w->labelMessage.'</label>
					<textarea name="message" id="input_message" class="span12" rows="10" required="required">'.htmlentities( $message, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			'.$newsletter.'
			'.$captcha.'
			'.$honeypot.'
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-envelope icon-white"></i> '.$w->buttonSave.'</button>
				<button type="reset" class="btn btn-small">'.$w->buttonReset.'</button>
			</div>
		</form>
	</div>
</div>
'.$textBottom;

if( $textRight )
	$content	= '
<div class="row-fluid">
	<div class="span9">
		'.$content.'
	</div>
	<div class="span3">
		'.$textRight.'
	</div>
</div>';

return $textBefore.$content.$textAfter;
?>
