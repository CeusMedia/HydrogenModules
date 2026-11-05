<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'corporation-add' );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './work/billing/corporation',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/corporation/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
						<div class="span4">
							<label for="input_iban">'.$w->labelIban.'</label>
							<input type="text" name="iban" id="input_iban" class="span12" required="required"/>
						</div>
						<div class="span2">
							<label for="input_bic">'.$w->labelBic.'</label>
							<input type="text" name="bic" id="input_bic" class="span12" required="required"/>
						</div>
						<div class="span2">
							<label for="input_balance">'.$w->labelBalance.'</label>
							<input type="number" step="0.01" name="balance" id="input_balance" class="span10 input-number" value="0"/><span class="suffix">&euro;</span>
						</div>
					</div>
					<div class="row-fluid">
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
