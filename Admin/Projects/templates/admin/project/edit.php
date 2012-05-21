<?php

$optStatus	= $words['version-states'];
ksort( $optStatus );

$panelAddVersion	= '
	'.UI_HTML_Elements::Form( 'add_version', './admin/project/addVersion/'.$projectId ).'
		<fieldset>
			<legend>Version hinzufügen</legend>
			<ul class="input">
				<li>
					<div class="column-left-50">
						'.UI_HTML_Tag::create( 'label', 'Version' ).'<br/>
						'.UI_HTML_Elements::Input( 'version', NULL, 'max' ).'
					</div>
					<div class="column-left-50">
						'.UI_HTML_Tag::create( 'label', 'Status' ).'<br/>
						'.UI_HTML_Elements::Select( 'status', $optStatus, 'max' ).'
					</div>
					<div class="column-clear"></div>
				</li>
				<li>
					'.UI_HTML_Tag::create( 'label', 'Titel' ).'<br/>
					'.UI_HTML_Elements::Input( 'title', NULL ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'label', 'Beschreibung' ).'<br/>
					'.UI_HTML_Elements::Textarea( 'description', NULL ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'add', 'hinzufügen', 'button add' ).'
			</div>
		</fieldset>
	</form>	
';

//  --  VERSIONS  --  //
$list	= array();
foreach( $versions as $version ){
	$label	= $version->version;
	$status	= $words['version-states'][$version->status];
	$remove	= UI_HTML_Elements::Link( './admin/project/removeVersion/'.$version->projectVersionId, 'X' );
	if( $version->title )
		$label	.= ': '.$version->title;
	$list[]	= '<li>['.$remove.'] '.$label.' ('.$status.')</li>';
}
$listVersions	= '<ul>'.join( $list ).'</ul>';
$panelVersions		= '
	<fieldset>
		<legend>Versionen</legend>
		'.$listVersions.'
	</fieldset>
	<br/>
';


$optStatus	= $words['states'];
krsort( $optStatus );
$optStatus['_selected']	= $project->status;

$wf		= (object) $words['edit'];

$panelEdit	= '
	'.UI_HTML_Elements::Form( 'edit', './admin/project/edit/'.$projectId ).'
		<fieldset>
			<legend>'.sprintf( $wf->legend, $project->title ).'</legend>
			<ul class="input">
				<li class="column-left-66">
					'.UI_HTML_Tag::create( 'label', $wf->labelTitle, array( 'for' => 'title', 'class' => 'mandatory' ) ).'<br/>
					'.UI_HTML_Elements::Input( 'title', htmlspecialchars( $project->title, ENT_COMPAT, 'UTF-8' ), 'max mandatory' ).'
				</li>
				<li class="column-left-33">
					'.UI_HTML_Tag::create( 'label', $wf->labelStatus, array( 'for' => 'status' ) ).'<br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'max' ).'
				</li>
				<li>
					'.UI_HTML_Tag::create( 'description', $wf->labelDescription, array( 'for' => 'description' ) ).'<br/>
					'.UI_HTML_Elements::TextArea( 'description', htmlspecialchars( $project->description, ENT_COMPAT, 'UTF-8' ), 'max' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './admin/project', $wf->buttonCancel, 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'doEdit', $wf->buttonSave, 'button save' ).'
				&nbsp;|&nbsp;
				'.UI_HTML_Elements::LinkButton( './admin/project/remove/'.$projectId, $wf->buttonRemove, 'button remove', $wf->buttonRemoveConfirm ).'
				<div class="clearfloat"></div>
			</div>
		</fieldset>
	</form>
';

return '
<div class="column-right-33">
	'.$panelVersions.'
	'.$panelAddVersion.'
</div>
<div class="column-left-66">
	'.$panelEdit.'
</div>
<div class="column-clear"></div>';
?>
