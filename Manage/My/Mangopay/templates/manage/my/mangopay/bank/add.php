<?php

$linkBack	= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/bank' );

return '
<div class="content-panel">
	<h3><i class="fa fa-fw fa-bank"></i> Neues Bankkonto</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/add" method="post">
<!--			<input type="hidden" name="backwardTo" value="'.$backwardTo.'"/>
			<input type="hidden" name="forwardTo" value="'.$forwardTo.'"/>-->
			<div class="row-fluid">
				<div class="span3">
					<label for="input_title">Bezeichnung <small class="muted"></small></label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label for="input_address">Adresse <small class="muted"></small></label>
					<input type="text" name="address" id="input_address" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_iban">IBAN <small class="muted"></small></label>
					<input type="text" name="iban" id="input_iban" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_bic">BIC <small class="muted"></small></label>
					<input type="text" name="bic" id="input_bic" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="'.$linkBack.'" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="select" class="btn btn-primary"><b class="fa fa-check"></b> weiter</button>
			</div>
		</form>
	</div>
</div>';
?>
