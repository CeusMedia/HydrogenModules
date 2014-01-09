<?php

return '
<h3>Neuer Kunde</h3>
<form action="./customer/add" method="post">
	<div class="row-fluid">
		<label for="input_title">Name</label>
		<input type="text" name="title" id="input_title" class="span4" value=""/>
	</div>
	<div class="row-fluid">
		<div class="span2">
			<label for="input_targetClass">Ziel</label>
			<select name="targetClass" id="input_targetClass" class="span12">
				<option key="0">A-Kunde</option>
				<option key="1">B-Kunde</option>
				<option key="2">C-Kunde</option>
			</select>
		</div>
	</div>
	<div class="buttonbar">
		<button type="button" class="btn btn-small" onclick="document.location.href=\'./customer\';"><i class="icon-arrow-left"></i> zurück</button>
		<button type="submit" class="btn btn-small btn-primary" name="save"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>
';
?>
