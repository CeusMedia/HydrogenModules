<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$tabs	= $view->renderTabs( $env, 2 );

$panelAdd	= '
<div class="content-panel">
	<h3>Neuer E-Mail-Server</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/server/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_imap_host" class="mandatory">IMAP-Server</label>
					<input type="text" name="imap_host" id="input_imap_host" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_imap_port">IMAP-Port</label>
					<input type="text" name="imap_port" id="input_imap_port" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_smtp_host" class="mandatory">SMTP-Server</label>
					<input type="text" name="smtp_host" id="input_smtp_host" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_smtp_port">SMTP-Port</label>
					<input type="text" name="smtp_port" id="input_smtp_port" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group/server" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$tabs	= $view->renderTabs( $env, 'server' );

return $tabs.'<div class="row-fluid"><div class="span6">'.$panelAdd.'</div></div>';
