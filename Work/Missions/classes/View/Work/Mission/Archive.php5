<?php
class View_Work_Mission_Archive extends CMF_Hydrogen_View{

	public function index(){

		$filter	= $this->loadTemplateFile( 'work/mission/index.filter.php' );
		return '
<div id="work-mission-index">
	'.$filter.'
	<div id="work-mission-index-content">
		<div class="alert alert-info">Loading...</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$.ajax({
		url: "./work/mission/ajaxRenderContent",
		success: function(html){
			$("#work-mission-index-content").html(html);
		}
	});
});
</script>
';
	}
}
?>
