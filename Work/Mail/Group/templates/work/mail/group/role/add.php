<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$optStatus	= array(
	0		=> 'deaktiviert',
	1		=> 'aktiviert',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $role->status );

$panelAdd	= '
<div class="content-panel">
	<h3>Neue Rolle</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/role/add" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $role->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span1">
					<label for="input_rank">Rank</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.htmlentities( $role->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span4 offset1">
					<label><strong>Rechte</strong></label>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_read" class="checkbox mandatory">
								<input type="checkbox" name="read" id="input_read" value="1" '.( $role->read ? 'checked="checked"' : '' ).'/>
								darf lesen
							</label>
							<label for="input_write" class="checkbox mandatory">
								<input type="checkbox" name="write" id="input_write" value="1" '.( $role->write ? 'checked="checked"' : '' ).'/>
								darf schreiben
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group/role" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$tabs	= $view->renderTabs( $env, 'role' );

return $tabs.$panelAdd;
