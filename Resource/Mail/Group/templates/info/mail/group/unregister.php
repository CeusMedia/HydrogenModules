<?php

$panelUnregister	= '
<div class="content-panel">
	<h3>Abmelden</h3>
	<div class="content-panel-inner">
		<form action="./info/mail/group/unregister" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_address_group">E-Mail-Adresse der Gruppe</label>
					<input type="text" name="address_group" id="input_address_group" class="span12" value="'.htmlentities( $address_group, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_address_member">Ihre E-Mail-Adresse</label>
					<input type="text" name="address_member" id="input_address_member" class="span12" value="'.htmlentities( $address_member, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span12">
					<label class="checkbox">
						<input type="checkbox" name="inform" value="1" id="input_inform" checked="checked"/>
						andere Mitglieder der Gruppe über die Abmeldung informieren
					</label>
				</div>
			</div>-->
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;abmelden</button>
			</div>
		</form>
	</div>
</div>';


return '<div class="row-fluid">
	<div class="span4 offset4">
		<br/>
		<br/>
		<br/>
		<br/>
		<br/>
		'.$panelUnregister.'
	</div>
</div>';


return 'Info_Mail_Group::unregister';
