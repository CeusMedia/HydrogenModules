<?php
$rows	= array();

$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconTest	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-circle' ) );
$iconInfo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa not-fa-fw fa-question-circle' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times-circle' ) );

function renderCodeBadge( $check, $label = NULL ){
	$code	= $check->code;
	switch( (int) substr( $check->code, 0, 1 ) ){
		case 0:
			$code		= str_pad( $check->error, 3, "0", STR_PAD_LEFT );
			$labelCode  = 'label-inverse';
			break;
		case 1:
		case 2:
		case 3:
			$labelCode  = 'label-success';
			break;
		case 4:
			$labelCode  = 'label-warning';
			break;
		case 5:
			$labelCode  = 'label-important';
			break;
		default:
			$labelCode  = '<em>unknown</em>';
			break;
	}
	$label	= strlen( trim( $label ) ) ? trim( $label ) : $code;
	return UI_HTML_Tag::create( 'span', $label, array( 'class' => 'label '.$labelCode ) );
}

foreach( $addresses as $address ){
	$timestamp	= $address->checkedAt ? $address->checkedAt : $address->createdAt;
	$time		= UI_HTML_Tag::create( 'small', date( "H:i:s", $timestamp ), array( 'class' => 'muted' ) );
	$date		= date( "Y-m-d", $timestamp );
	$buttonTestEnabled	= UI_HTML_Tag::create( 'a', $iconTest.'&nbsp;testen', array(
		'class'		=> 'btn btn-mini btn-primary',
		'onclick'	=> 'startTest(this)',
		'href'		=> './work/mail/check/check?addressId='.$address->mailAddressId.'&from=./work/mail/check/'.$page
	) );
	$buttonTestDisabled	= UI_HTML_Tag::create( 'a', $iconTest.'&nbsp;testen', array(
		'class'		=> 'btn btn-mini btn-primary disabled',
	) );
	$buttonEditEnabled	= UI_HTML_Tag::create( 'a', $iconEdit, array(
		'class'		=> 'btn btn-mini',
		'title'		=> 'bearbeiten',
		'onclick'	=> 'editAddress('.$address->mailAddressId.', \''.htmlentities( $address->address, ENT_QUOTES, 'UTF-8' ).'\')'
	) );
	$buttonEditDisabled	= UI_HTML_Tag::create( 'a', $iconEdit, array(
		'class'		=> 'btn btn-mini disabled',
		'title'		=> 'bearbeiten',
	) );
	$buttonRemoveEnabled	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'class'		=> 'btn btn-mini btn-inverse',
		'title'		=> 'entfernen',
		'href'		=> './work/mail/check/remove?addressId='.$address->mailAddressId
	) );
	$buttonRemoveDisabled	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'class'		=> 'btn btn-mini btn-inverse disabled',
		'title'		=> 'entfernen',
	) );
	$buttonInfoEnabled		= UI_HTML_Tag::create( 'a', $iconInfo, array(
		'class'			=> 'btn btn-mini not-btn-info modal-trigger',
		'title'			=> 'info',
		'href'			=> './work/mail/check/ajaxAddress/'.$address->mailAddressId,
	) );

	$status			= '-';
	$buttonEdit		= $buttonEditEnabled;
	$buttonInfo		= $buttonInfoEnabled;
	$buttonTest		= $buttonTestEnabled;
	$buttonRemove	= $buttonRemoveEnabled;
	if( $address->status == 2 ){
		$status			= renderCodeBadge( $address->check/*, 'OK'*/ );
		$buttonEdit		= $buttonEditDisabled;
		$buttonRemove	= $buttonRemoveDisabled;
	}
	else if( $address->status == 1 ){
		$status			= '<small class="muted">warte</small>';
		$buttonEdit		= $buttonEditDisabled;
		$buttonTest		= $buttonTestDisabled;
		$buttonRemove	= $buttonRemoveDisabled;
	}
	else if( $address->status == 0 ){
		$buttonInfo		= '';
	}
	else if( $address->status < 0 ){
		$description	= \CeusMedia\Mail\Transport\SMTP\Code::getText( $address->check->code, FALSE );
		$status		 	= UI_HTML_Tag::create( 'abbr', renderCodeBadge( $address->check ), array( 'title' => $description ) );
	}
	$status		.= '&nbsp;'.$buttonInfo;
	$buttons	= UI_HTML_Tag::create( 'div', array( $buttonEdit, $buttonTest, $buttonRemove ), array( 'class' => 'btn-group' ) );
	$rows[]		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $address->address, array( 'class' => 'cell-address-title' ) ),
		UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-address-status' ) ),
		UI_HTML_Tag::create( 'td', $date.' '.$time, array( 'class' => 'cell-address-datetime' ) ),
		UI_HTML_Tag::create( 'td', $buttons ),
	), array(
		'class'		=> 'mail-check-address-status-'.$address->status,
		'data-id'	=> $address->mailAddressId
	) );
}

$colgroup	= UI_HTML_Elements::ColumnGroup( array( "", "15%", "140px", "130px" ) );
$heads	= UI_HTML_Elements::TableHeads( array( 'Adresse', 'Status', 'Zeitpunkt', '' ) );

$thead	= UI_HTML_Tag::create( 'thead', $heads );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table not-table-striped table-condensed' ) );

$pagination	= new \CeusMedia\Bootstrap\PageControl( './work/mail/check', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
<div class="content-panel">
	<h3>Addresses</h3>
	<div class="content-panel-inner">
		'.$table.'
		'.$pagination.'
		<div class="buttonbar">
			<a href="./work/mail/check/checkAll" class="btn btn-primary" onclick="return confirm(\'Alle gefundenen Adressen werden zu einer Prüfung angemeldet und deren aktueller Prüfzustand wird zurück gesetzt.\n\nWeitere Arbeiten in dieser Gruppe sind nicht möglich, bis die Prüfung vollzogen ist.\n\nDie automatische Prüfung läuft im Hintergrund und beginnt innerhalb einer Minute.\n\nDiesen Schritt wirklich gehen?\')"><i class="fa fa-fw fa-asterisk"></i>&nbsp;alle Adressen prüfen</a>
			<a href="./work/mail/check/'.$page.'" class="btn btn-small"><i class="fa fa-fw fa-refresh"></i>&nbsp;Ansicht aktualisieren</a>
		</div>
	</div>
</div>';

$optGroup	= array();
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= UI_HTML_Elements::Options( $optGroup, $filterGroupId );

$panelAdd	= '
<div class="content-panel">
	<h3>Add</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_groupId">Gruppe</label>
					<select name="groupId" id="input_groupId" class="span12">'.$optGroup.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_address" class="mandatory required">Address</label>
					<input type="text" name="address" id="input_address" class="span12" required="required"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;hinzufügen</button>
				<a href="./work/mail/check/import" class="btn btn-small">Datei importieren</a>
			</div>
		</form>
	</div>
</div>';

$optGroup	= array();
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title.' ('.$countByGroup[$group->mailGroupId].')';
$optGroup	= UI_HTML_Elements::Options( $optGroup, $filterGroupId );

$statuses	= array(
	''	=> '- alle -',
	-2	=> 'nicht erreichbar ('.$countByStatus[-2].')',
	-1	=> 'abgelehnt ('.$countByStatus[-1].')',
	0	=> 'ungetestet ('.$countByStatus[0].')',
	1	=> 'wird getestet ('.$countByStatus[1].')',
	2	=> 'erreichbar ('.$countByStatus[2].')',
);

$optStatus	= array();
foreach( $statuses as $key => $label )
	$optStatus[$key]	= $label;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$panelFilter	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_groupId">Gruppe</label>
					<select name="groupId" id="input_groupId" class="span12" onchange="this.form.submit()">'.$optGroup.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">Zustand</label>
					<select name="status[]" id="input_status" class="span12" multiple="multiple" size="6">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">beinhaltet</label>
					<input type="text" name="query" id="input_query" class="span12" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ).'" onchange="this.form.submit()"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_limit">pro Seite</label>
					<input type="text" name="limit" id="input_limit" class="span12" value="'.$filterLimit.'"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-search"></i>&nbsp;filtern</button>
				<a href="./work/mail/check/filter/reset" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-search-minus"></i>&nbsp;leeren</a>
			</div>
		</form>
	</div>
</div>';

$tabs	= View_Work_Mail_Check::renderTabs( $env, '' );

return $tabs.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
		'.$panelAdd.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
<div class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 860px; margin-left: -430px;">
	<div class="modal-header">
		<a class="close" data-dismiss="modal">&times;</a>
		<h3>Informationen</h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer">
		<a class="btn" data-dismiss="modal">Okay</a>
	</div>
</div>

<script>
function startTest(elem){
	var icon = $(elem).children("i");
	icon.removeClass("fa-check-circle");
	icon.addClass("fa-spin fa-spinner");
	$(elem).attr("disabled", "disabled");
}
function editAddress(id, address){
	edited = prompt("What?", address);
	if(edited){
		$.ajax({
			url: "./work/mail/check/ajaxEditAddress",
			method: "POST",
			data: {id: id, address: edited},
			dataType: "JSON",
			success: function(json){
				document.location.reload();
			}
		});
		alert("save: "+output);
	}
}

$(document).ready(function() {
	$("a.modal-trigger").click(function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr("href"),
			dataType: "HTML",
			method: "GET",
 			success: function(data) {
				var div = $(".modal-body");
				div.html(data).parent().modal();
			}
		});
	});
});
</script>
<style>
/*  --  ADDRESS STAUS FILTER OPTIONS  --  */
body.moduleWorkMailCheck select#input_status{
	padding: 0;
	overflow: hidden;
	}
body.moduleWorkMailCheck select#input_status > option{
	padding: 2px 6px;
	}
body.moduleWorkMailCheck select#input_status > option[value="-2"]{
	background-color: #f2bdbd;
	}
body.moduleWorkMailCheck select#input_status > option[value="-1"]{
	background-color: #fce3c3;
	}
body.moduleWorkMailCheck select#input_status > option[value="0"]{
	background-color: #ffffff;
	}
body.moduleWorkMailCheck select#input_status > option[value="1"]{
	background-color: #e3e3e3;
	}
body.moduleWorkMailCheck select#input_status > option[value="2"]{
	background-color: rgba(159, 240, 168, 1);
	}

/*  --  ADDRESS TABLE BY STATUS  --  */
body.moduleWorkMailCheck table tr.mail-check-address-status--2 td{
	background-color: rgba(242, 189, 189, 0.5);
	}
body.moduleWorkMailCheck table tr.mail-check-address-status--1 td{
	background-color: rgba(252, 227, 195, 0.5);
	}
body.moduleWorkMailCheck table tr.mail-check-address-status-0 td{
	background-color: rgba(255, 255, 255, 0.5);
	}
body.moduleWorkMailCheck table tr.mail-check-address-status-1 td{
	background-color: rgba(227, 227, 227, 0.5);
	}
body.moduleWorkMailCheck table tr.mail-check-address-status-2 td{
	background-color: rgba(159, 240, 168, 0.5);
	}
</style>';
?>
