<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optStatus	= $words['version-states'];
ksort( $optStatus );

$panelAddVersion	= '
	'.HtmlElements::Form( 'add_version', './admin/project/addVersion/'.$projectId ).'
		<fieldset>
			<legend>Version hinzufügen</legend>
			<ul class="input">
				<li>
					<div class="column-left-50">
						'.HtmlTag::create( 'label', 'Version' ).'<br/>
						'.HtmlElements::Input( 'version', NULL, 'max' ).'
					</div>
					<div class="column-left-50">
						'.HtmlTag::create( 'label', 'Status' ).'<br/>
						'.HtmlElements::Select( 'status', $optStatus, 'max' ).'
					</div>
					<div class="column-clear"></div>
				</li>
				<li>
					'.HtmlTag::create( 'label', 'Titel' ).'<br/>
					'.HtmlElements::Input( 'title', NULL ).'
				</li>
				<li>
					'.HtmlTag::create( 'label', 'Beschreibung' ).'<br/>
					'.HtmlElements::Textarea( 'description', NULL ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::Button( 'add', 'hinzufügen', 'button add' ).'
			</div>
		</fieldset>
	</form>	
';

//  --  VERSIONS  --  //
$list	= [];
foreach( $versions as $version ){
	$label	= $version->version;
	$status	= $words['version-states'][$version->status];
	$remove	= HtmlElements::Link( './admin/project/removeVersion/'.$version->projectVersionId, 'X' );
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
	'.HtmlElements::Form( 'edit', './admin/project/edit/'.$projectId ).'
		<fieldset>
			<legend>'.sprintf( $wf->legend, $project->title ).'</legend>
			<ul class="input">
				<li class="column-left-66">
					'.HtmlTag::create( 'label', $wf->labelTitle, array( 'for' => 'title', 'class' => 'mandatory' ) ).'<br/>
					'.HtmlElements::Input( 'title', htmlspecialchars( $project->title, ENT_COMPAT, 'UTF-8' ), 'max mandatory' ).'
				</li>
				<li class="column-left-33">
					'.HtmlTag::create( 'label', $wf->labelStatus, array( 'for' => 'status' ) ).'<br/>
					'.HtmlElements::Select( 'status', $optStatus, 'max' ).'
				</li>
				<li>
					'.HtmlTag::create( 'description', $wf->labelDescription, array( 'for' => 'description' ) ).'<br/>
					'.HtmlElements::TextArea( 'description', htmlspecialchars( $project->description, ENT_COMPAT, 'UTF-8' ), 'max' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './admin/project', $wf->buttonCancel, 'button cancel' ).'
				'.HtmlElements::Button( 'doEdit', $wf->buttonSave, 'button save' ).'
				&nbsp;|&nbsp;
				'.HtmlElements::LinkButton( './admin/project/remove/'.$projectId, $wf->buttonRemove, 'button remove', $wf->buttonRemoveConfirm ).'
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
