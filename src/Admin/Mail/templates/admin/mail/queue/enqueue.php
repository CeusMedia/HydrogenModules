<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var array<array<string,string>> $words */
/** @var array $classes */
/** @var string $class */
/** @var string $subject */
/** @var string $body */
/** @var string $sender */
/** @var string $receiver */

$optClass	= HtmlElements::Options( array_combine( $classes, $classes ), $class );
//$optClass['_selected']	= $class;

$w	= (object) $words['enqueue'];

return '
<h2>'.$w->heading.'</h2>
<div class="layout-panel">
	<div class="layout-panel-inner">
		<form action="./admin/mail/queue/enqueue" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class">'.$w->labelClass.'</label>
					<select id="input_class" name="class">'.$optClass.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_sender">'.$w->labelSender.'</label>
					<input type="text" id="input_sender" name="sender" class="span12" value="'.htmlentities( $sender, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_receiver">'.$w->labelReceiver.'</label>
					<input type="text" id="input_receiver" name="receiver" class="span12" value="'.htmlentities( $receiver, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_subject">'.$w->labelSubject.'</label>
					<input type="text" id="input_subject" name="subject" class="span12" value="'.htmlentities( $subject, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_body">'.$w->labelBody.'</label>
					<textarea id="input_body" name="body" class="span12" rows="20">'.htmlentities( $body, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-success"><i class="icon-plus icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';
