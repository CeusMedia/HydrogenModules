<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['assign'];
$wp		= (object) $words['position'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconOrder		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-shopping-cart'] );
$iconSearch		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconUser		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] );

$buttonSearch	= HtmlTag::create( 'a', $iconSearch.' '.$w->buttonSearch, [
	'href'			=> '#modalSelectUser',
	'role'			=> 'button',
	'class'			=> 'btn btn-success btn-small',
	'data-toggle'	=> 'modal',
] );
$buttonAssignSelf	= HtmlTag::create( 'a', $iconUser.' '.$w->buttonAssignSelf, [
	'href'		=> './manage/my/provision/license/assign/'.$userLicense->userLicenseId.'/?userId='.$currentUserId.'&save',
	'class'		=> 'btn btn-small',
] );
$buttonRemove		= '';/*HtmlTag::create( 'a', 'entziehen', [
	'href'		=> './manage/my/provision/license/unassign/'.$userLicense->userLicenseId.'/'.$currentUserId,
	'class'		=> 'btn btn-small btn-remove',
] );*/


$user	= '<div id="user-container">
	'.$buttonSearch.'
	'.$buttonAssignSelf.'
	'.$buttonRemove.'
	<br/>
</div>';


$data	= [];
$data[$w->labelProduct]			= $userLicense->product->title;
$data[$w->labelLicense]			= $userLicense->productLicense->title;
$data[$w->labelLicenseNumber]	= $userLicense->uid;
$data[$w->labelDuration]		= $words['durations'][$userLicense->duration];
$data[$w->labelKeysInLicense]	= $userLicense->users;
//$data1['davon vergeben']		= $userLicense->users;

$factsLicense	= View_Manage_My_License::renderDefinitionList( $data );

$panelLicense	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->headingLicense.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$factsLicense.'
			</div>
		</div>
		<div class="buttonbar">
			<a href="./manage/my/provision/license/view/'.$userLicense->userLicenseId.'" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
		</div>
	</div>
</div>';



$data	= [];
$data[$w->labelProduct]			= $userLicense->product->title;
$data[$w->labelLicense]			= $userLicense->productLicense->title;
//$data[$w->labelLicenseNumber]	= $userLicense->uid;
$data[$w->labelDuration]		= $words['durations'][$userLicense->duration];
//$data[$w->labelKeysInLicense]	= $userLicense->users;
$data[$w->labelKeyNumber]		= $userLicenseKey->uid;
$data[$w->labelUser]			= $user;
//$data1['davon vergeben']		= $userLicense->users;

$factsAssign	= View_Manage_My_License::renderDefinitionList( $data );

$panelAssign	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->headingAssign.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/provision/license/assign/'.$userLicenseKey->userLicenseKeyId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<input type="hidden" name="userLicenseId" value="'.$userLicense->userLicenseId.'"/>
					<input type="hidden" name="userId" id="input_userId" readonly="readonly"/>
					'.$factsAssign.'
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/provision/license/view/'.$userLicense->userLicenseId.'" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary" id="btn-save" disabled="disabled"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>

<!-- Modal -->
<div id="modalSelectUser" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">'.$w->headingModal.'</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid">
			<div class="span6">
				<label>'.$w->labelQuery.'</label>
				<input type="text" name="query" id="input_query" onkeyup="ManageMyLicense.suggestUsers()" class="span12" autocomplete="off"/>
			</div>
		</div>
		<hr/>
		<div id="user-list">
			<div id="user-list-container">
			</div>
			<div id="user-list-empty" style="display: none">
				<em class="muted">'.$w->textNoUserFound.'</em>
			</div>
			<div id="user-list-hint">
				<h4>'.$w->headingHint.'</h4>
				<p>
					<small>
						'.$w->textHint1.'
					</small>
				</p>
				<p>
					<small>
						'.$w->textHint2.'
					</small>
				</p>
				<p>
					<small>
						<b>'.$w->labelNote.'</b><br/>
						'.$w->textNote.'
					</small>
				</p>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">'.$w->buttonClose.'</button>
	</div>
</div>';

$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/my/provision/license/assign/' ) );

$tabs	= View_Manage_My_Provision_License::renderTabs( $env, '' );

$script	= '
var ManageMyLicense = {
	data: [],
	selectThisUser: function (){
		var userId = $(this).data("userId");
		$("#input_userId").val(userId);
		$("#input_user").val(ManageMyLicense.data[userId].user.username);
		$("#user-container").html(ManageMyLicense.data[userId].html);
		$("#modalSelectUser").modal("hide");
		$("#btn-save").removeProp("disabled");
	},
	suggestUsers: function(){
		var query = $("#input_query").val();
		$("#user-list-container").html("");
		$("#user-list-empty").hide();
		if(query.length < 1 ){
			$("#user-list-hint").show();
			return;
		}
		$("#user-list-hint").hide();
		$.ajax({
			url: "./manage/my/provision/license/ajaxGetUsers",
			data: {query: query},
			method: "post",
			context: $("#user-list-container"),
			dataType: "json",
			success: function(json){
				if(json.status == "success"){
					var i, item;
					var list = $("<div></div>").addClass("list");
					ManageMyLicense.data = [];
					json.data.count > 0 ? $("#user-list-empty").hide() : $("#user-list-empty").show();
					if(json.data.count){
						for(i=0; i<json.data.count; i++){
							item = $("<div></div>").addClass("item");
							item.html(json.data.list[i].html);
							item.data("userId", json.data.list[i].user.userId);
							item.on("click", ManageMyLicense.selectThisUser);
							list.append(item);
							ManageMyLicense.data[json.data.list[i].user.userId] = json.data.list[i];
						}

						$(this).html(list);
					}
				}
			}
		});
	}
}';


return $tabs.$textTop.'
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;'.$wp->label.' </big>
	<a href="./manage/my/provision/license">'.$wp->licenseList.'</a>
	<i class="fa fa-fw fa-chevron-right"></i>
	<a href="./manage/my/provision/license/view/'.$userLicense->userLicenseId.'">
		<strong>'.$product->title.'</strong>
		<em>'.$userLicense->productLicense->title.'</em>
	</a>
	<i class="fa fa-fw fa-chevron-right"></i>
	<span>'.$wp->assignKey.'<!--<span class="muted">'.$userLicenseKey->uid.'</span>--></span>
	<hr/>
</div>
<div class="row-fluid">
	<div class="span5">
		'.$panelLicense.'
	</div>
	<div class="span7">
		'.$panelAssign.'
	</div>
</div>
<script>'.$script.'</script>';

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelAssign.'
	</div>
</div>
<script>'.$script.'</script>';
