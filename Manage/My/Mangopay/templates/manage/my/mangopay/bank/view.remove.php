<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-remove" ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => "fa fa-trash" ) );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.' entfernen', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-danger',
) );

$textRemove		= '
		<strong>Ist das Bankkonto nicht mehr aktuell?</strong>
		<p>
			Registrierte Bankkonten können hier natürlich auch wieder abgemeldet werden.<br/>
			Mögliche Situationen:
			<ul>
				<li>Konto existiert nicht mehr</li>
				<li>Konto soll gegen ein anderes getauscht werden</li>
				<li>Lastschrifteinzug <small class="muted">(falls eingerichtent)</small> abbrechen</li>
			</ul>
		</p>
		<br/>
		<div class="alert alert-info">
			Das Entfernen einens Bankkontos kann nicht rückgängig gemacht werden.<br/>
			Sollle das Bankkonto In Zukunft wieder eine Rolle spielen, kann es erneut registriert werden.
		</div>
		<p>
			Das Abmelden eines Bankkontos muss mit dem Passwort bestätigt werden.
		</p>
';
return '
<div class="content-panel panel-mangopay-view" id="panel-mangopay-card-view">
	<h3><i class="fa fa-fw fa-ban"></i> Bankkonto abmelden</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/bank/deactivate/'.$bankAccountId.'" method="post">
			'.$textRemove.'
			<div class="row-fluid">
				<div class="span12">
					<label for="input_password">Passwort</label>
					<input type="password" name="password" id="input_password"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>';

?>
