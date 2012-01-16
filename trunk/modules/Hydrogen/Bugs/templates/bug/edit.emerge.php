<?php
$optType		= $this->renderOptions( $words['types'], 'type', $bug->type, 'bug-type type-%1$d');
$optSeverity	= $this->renderOptions( $words['severities'], 'severity', $bug->severity, 'bug-severity severity-%1$d');
$optPriority	= $this->renderOptions( $words['priorities'], 'priority', $bug->priority, 'bug-priority priority-%1$d');
$optStatus		= $this->renderOptions( $words['states'], 'status', $bug->status, 'bug-status status-%1$d');

return '
<fieldset>
	<legend>Fehler bearbeiten</legend>
	<form action="./labs/bug/emerge/'.$bug->bugId.'" method="post">
		<div style="float: left; width: 30%; margin-right: 1%">
			<ul class="input">
				<li>
					<label for="type">'.$words['edit']['labelType'].'</label><br/>
					'.UI_HTML_Elements::Select( 'type', $optType, 'm' ).'
				</li>
				<li>
					<label for="severity">'.$words['edit']['labelSeverity'].'</label><br/>
					'.UI_HTML_Elements::Select( 'severity', $optSeverity, 'm' ).'
				</li>
				<li>
					<label for="priority">'.$words['edit']['labelPriority'].'</label><br/>
					'.UI_HTML_Elements::Select( 'priority', $optPriority, 'm' ).'
				</li>
				<li>
					<label for="status">'.$words['edit']['labelStatus'].'</label><br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
				<li>
					<label for="progress">'.$words['edit']['labelProgress'].'</label><br/>
					'.UI_HTML_Elements::Input( 'progress', $bug->progress, 'xs numeric' ).'%
				</li>
			</ul>
		</div>
		<div style="float: left; width: 66%; margin-right: 3%">
			<ul class="input">
				<li>
					<label for="content">'.$words['edit']['labelContent'].'</label><br/>
					'.UI_HTML_Tag::create( 'textarea', '', array( 'name' => 'note', 'rows' => 13 ) ).'
				</li>
			</ul>
		</div>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'save', 'aktualisieren', 'button save' ).'
		</div>
</fieldset>
';
?>