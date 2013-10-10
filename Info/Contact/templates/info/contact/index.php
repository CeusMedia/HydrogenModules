<?php
$w		= (object) $words['index'];
$texts	= $this->populateTexts( array( 'top', 'bottom' ), 'html/info/contact/' );
return '
<h2>'.$w->heading.'</h2>
'.$texts['top'].'
<form action="" method="post">
	<div class="row-fluid">
		<div class="span5">
			<label for="input_name">'.$w->labelName.'</label>
			<input type="text" name="name" id="input_name" class="span12" maxlength="40" value="'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span7">
			<label for="input_email">'.$w->labelEmail.'</label>
			<input type="text" name="email" id="input_email" class="span12" maxlength="50" value="'.htmlentities( $email, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_subject">'.$w->labelSubject.'</label>
			<input type="text" name="subject" id="input_subject" class="span12" maxlength="80" value="'.htmlentities( $subject, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_message">'.$w->labelMessage.'</label>
			<textarea name="message" id="input_message" class="span12" rows="10">'.htmlentities( $message, ENT_QUOTES, 'UTF-8' ).'</textarea>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-success -btn-small"><i class="icon-envelope icon-white"></i> '.$w->buttonSave.'</button>
		<button type="reset" class="btn btn-small">zurücksetzen</button>
	</div>
</form>
'.$texts['bottom'].'
';
?>
