<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $projects */
/** @var object $server */

$optProject		= [];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject );

$optVersion		= [];
/*foreach( $versions as $version ){
	$label	= $version->version;
	if( $version->title )
		$label	.= ' ('.$version->title.')';
	$optVersion[$version->projectVersionId]	= $label;
}
*/
$optVersion	= HtmlElements::Options( $optVersion );

$optStatus	= $words['states-project'];
ksort( $optStatus );
$optStatus	= HtmlElements::Options( $optStatus, 0 );

$wf			= (object) $words['addProject'];

$panelAddProject	= '
	'.HtmlElements::Form( 'add_server', './admin/server/addProject/'.$server->serverId ).'
		<fieldset>
			<legend>'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Project' ).'<br/>
					'.HtmlElements::Select( 'projectId', $optProject, 'max' ).'
				</li>
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Version' ).'<br/>
					'.HtmlElements::Select( 'projectVersionId', $optVersion, 'max' ).'
				</li>
				<li class="column-left-50">
					'.HtmlTag::create( 'label', 'Status' ).'<br/>
					'.HtmlElements::Select( 'status', $optStatus, 'max' ).'
				</li>
				<li>
					'.HtmlTag::create( 'label', 'Titel' ).'<br/>
					'.HtmlElements::Input( 'title' ).'
				</li>
				<li class="column-clear">
					'.HtmlTag::create( 'label', 'Beschreibung' ).'<br/>
					'.HtmlElements::Textarea( 'description' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::Button( 'add', 'hinzufügen', 'button add' ).'
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
				let select = $("#projectVersionId");
				select.html("");
				let option;
				for(let i in data){
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
