<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optProject		= [];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= UI_HTML_Elements::Options( $optProject );

$optVersion		= [];
/*foreach( $versions as $version ){
	$label	= $version->version;
	if( $version->title )
		$label	.= ' ('.$version->title.')';
	$optVersion[$version->projectVersionId]	= $label;
}
*/$optVersion	= UI_HTML_Elements::Options( $optVersion );

$optStatus	= $words['states-project'];
ksort( $optStatus );
$optStatus	= UI_HTML_Elements::Options( $optStatus, 0 );

$wf			= (object) $words['addProject'];

$panelAddProject	= '
	'.UI_HTML_Elements::Form( 'add_server', './admin/server/addProject/'.$server->serverId ).'
		<fieldset>
			<legend>'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Project' ).'<br/>
					'.UI_HTML_Elements::Select( 'projectId', $optProject, 'max' ).'
				</li>
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Version' ).'<br/>
					'.UI_HTML_Elements::Select( 'projectVersionId', $optVersion, 'max' ).'
				</li>
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Status' ).'<br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'max' ).'
				</li>
				<li>
					'.HtmlTag::create( 'label', 'Titel' ).'<br/>
					'.UI_HTML_Elements::Input( 'title', NULL ).'
				</li>
				<li class="column-clear">
					'.HtmlTag::create( 'label', 'Beschreibung' ).'<br/>
					'.UI_HTML_Elements::Textarea( 'description', NULL ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'add', 'hinzufügen', 'button add' ).'
			</div>
		</fieldset>
	</form>
	<script>
$(document).ready(function(){
	$("#projectId").on("change",function(){
		$.ajax({
			url: "./admin/project/ajaxGetVersions/"+$("#projectId").val(),
			dataType: "json",
			success: function(data){
				var select = $("#projectVersionId");
				select.html("");
				var option;
				for(i in data){
					option = $("<option></option>");
					option.attr("value",data[i].projectVersionId);
					option.html(data[i].version+" "+data[i].title);
					select.append(option);
				}
				console.log(data);
			}
		})
	}).trigger("change");
});
	</script>
';

return $panelAddProject;
?>
