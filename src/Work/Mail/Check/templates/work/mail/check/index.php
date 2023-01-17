<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$rows	= [];

$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconTest	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check-circle'] );
$iconInfo	= HtmlTag::create( 'i', '', ['class' => 'fa not-fa-fw fa-question-circle'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-times-circle'] );

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
	return HtmlTag::create( 'span', $label, ['class' => 'label '.$labelCode] );
}

foreach( $addresses as $address ){
	$timestamp	= $address->checkedAt ? $address->checkedAt : $address->createdAt;
	$time		= HtmlTag::create( 'small', date( "H:i:s", $timestamp ), ['class' => 'muted'] );
	$date		= date( "Y-m-d", $timestamp );
	$buttonTestEnabled	= HtmlTag::create( 'a', $iconTest.'&nbsp;testen', array(
		'class'		=> 'btn btn-mini btn-primary',
		'onclick'	=> 'startTest(this)',
		'href'		=> './work/mail/check/check?addressId='.$address->mailAddressId.'&from=./work/mail/check/'.$page
	) );
	$buttonTestDisabled	= HtmlTag::create( 'a', $iconTest.'&nbsp;testen', [
		'class'		=> 'btn btn-mini btn-primary disabled',
	] );
	$buttonEditEnabled	= HtmlTag::create( 'a', $iconEdit, array(
		'class'		=> 'btn btn-mini',
		'title'		=> 'bearbeiten',
		'onclick'	=> 'editAddress('.$address->mailAddressId.', \''.htmlentities( $address->address, ENT_QUOTES, 'UTF-8' ).'\')'
	) );
	$buttonEditDisabled	= HtmlTag::create( 'a', $iconEdit, [
		'class'		=> 'btn btn-mini disabled',
		'title'		=> 'bearbeiten',
	] );
	$buttonRemoveEnabled	= HtmlTag::create( 'a', $iconRemove, [
		'class'		=> 'btn btn-mini btn-inverse',
		'title'		=> 'entfernen',
		'href'		=> './work/mail/check/remove?addressId='.$address->mailAddressId
	] );
	$buttonRemoveDisabled	= HtmlTag::create( 'a', $iconRemove, [
		'class'		=> 'btn btn-mini btn-inverse disabled',
		'title'		=> 'entfernen',
	] );
	$buttonInfoEnabled		= HtmlTag::create( 'a', $iconInfo, [
		'class'			=> 'btn btn-mini not-btn-info modal-trigger',
		'title'			=> 'info',
		'href'			=> './work/mail/check/ajaxAddress/'.$address->mailAddressId,
	] );

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
		$status		 	= HtmlTag::create( 'abbr', renderCodeBadge( $address->check ), ['title' => $description] );
	}
	$status		.= '&nbsp;'.$buttonInfo;
	$buttons	= HtmlTag::create( 'div', [$buttonEdit, $buttonTest, $buttonRemove], ['class' => 'btn-group'] );
	$rows[]		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $address->address, ['class' => 'cell-address-title'] ),
		HtmlTag::create( 'td', $status, ['class' => 'cell-address-status'] ),
		HtmlTag::create( 'td', $date.' '.$time, ['class' => 'cell-address-datetime'] ),
		HtmlTag::create( 'td', $buttons ),
	), [
		'class'		=> 'mail-check-address-status-'.$address->status,
		'data-id'	=> $address->mailAddressId
	] );
}

$colgroup	= HtmlElements::ColumnGroup( ["", "15%", "140px", "130px"] );
$heads	= HtmlElements::TableHeads( ['Adresse', 'Status', 'Zeitpunkt', ''] );

$thead	= HtmlTag::create( 'thead', $heads );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table not-table-striped table-condensed'] );

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

$optGroup	= [];
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= HtmlElements::Options( $optGroup, $filterGroupId );

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

$optGroup	= [];
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title.' ('.$countByGroup[$group->mailGroupId].')';
$optGroup	= HtmlElements::Options( $optGroup, $filterGroupId );

$statuses	= array(
	''	=> '- alle -',
	-2	=> 'nicht erreichbar ('.$countByStatus[-2].')',
	-1	=> 'abgelehnt ('.$countByStatus[-1].')',
	0	=> 'ungetestet ('.$countByStatus[0].')',
	1	=> 'wird getestet ('.$countByStatus[1].')',
	2	=> 'erreichbar ('.$countByStatus[2].')',
);

$optStatus	= [];
foreach( $statuses as $key => $label )
	$optStatus[$key]	= $label;
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

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
</script>';
