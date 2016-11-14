<?php
$rows	= array();

$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconTest	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-circle' ) );
$iconInfo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-question-circle' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times-circle' ) );

foreach( $addresses as $address ){
	$timestamp	= $address->checkedAt ? date( "Y-m-d H:i:s", $address->checkedAt ) : '-';
	$buttonTest	= UI_HTML_Tag::create( 'a', $iconTest.'&nbsp;testen', array(
		'class'		=> 'btn btn-mini btn-primary',
		'href'		=> './work/mail/check/check?addressId='.$address->mailAddressId.'&from=./work/mail/check/'.$page
	) );
	$buttonEdit	= UI_HTML_Tag::create( 'a', $iconEdit, array(
		'class'		=> 'btn btn-mini',
		'onclick'	=> 'editAddress('.$address->mailAddressId.', \''.htmlentities( $address->address, ENT_QUOTES, 'UTF-8' ).'\')'
	) );

	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'class'		=> 'btn btn-mini btn-inverse',
		'title'		=> 'entfernen',
		'href'		=> './work/mail/check/remove?addressId='.$address->mailAddressId
	) );

	$rowClass	= '';
	$status		= '-';
	if( $address->status == 2 ){
		$rowClass	= 'success';
		$status		= 'OK';
	}
	else if( $address->status == 1 ){
		$rowClass		= 'warning';
		$status			= '<small class="muted">waiting</small>';
		$buttonTest	= UI_HTML_Tag::create( 'a', $iconTest.'&nbsp;testen', array(
			'class'		=> 'btn btn-mini btn-primary disabled',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
			'class'		=> 'btn btn-mini btn-inverse disabled',
		) );
	}
	else if( $address->status < 0 ){
		$rowClass		= 'error';
		$description	= \CeusMedia\Mail\Transport\SMTP\Code::getText( $address->check->code );
		$status		 	= UI_HTML_Tag::create( 'abbr', $address->check->code, array( 'title' => $description ) );
		$buttonInfo		= UI_HTML_Tag::create( 'a', $iconInfo, array(
			'class'		=> 'btn btn-mini not-btn-info',
			'title'		=> 'info',
			'onclick'	=> 'alert("'.htmlentities( $address->check->message, ENT_QUOTES, 'UTF-8' ).'")',
		) );
		$status			.= '&nbsp;'.$buttonInfo;
	}
	$buttons	= UI_HTML_Tag::create( 'div', array( $buttonEdit, $buttonTest, $buttonRemove ), array( 'class' => 'btn-group' ) );
	$rows[]		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $address->address, array( 'class' => 'cell-address' ) ),
		UI_HTML_Tag::create( 'td', $status ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $timestamp ) ),
		UI_HTML_Tag::create( 'td', $buttons ),
	), array( 'class' => $rowClass, 'data-id' => $address->mailAddressId ) );
}

$colgroup	= UI_HTML_Elements::ColumnGroup( array( "", "10%", "20%", "20%" ) );
$heads	= UI_HTML_Elements::TableHeads( array( 'Adresse', 'Status', 'Zeitpunkt', '' ) );

$thead	= UI_HTML_Tag::create( 'thead', $heads );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-condensed' ) );

$pagination	= new \CeusMedia\Bootstrap\PageControl( './work/mail/check', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
<div class="content-panel">
	<h3>Addresses</h3>
	<div class="content-panel-inner">
		'.$table.'
		'.$pagination.'
		<div class="buttonbar">
			<a href="./work/mail/check/checkAll" class="btn btn-primary">testAll</a>
			<a href="./work/mail/check/'.$page.'" class="btn btn-small"><i class="fa fa-fw fa-refresh"></i>&nbsp;refresh</a>
		</div>
	</div>
</div>';

$panelAdd	= '
<div class="content-panel">
	<h3>Add</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/add" method="post">
			<label for="input_address">Address</label>
			<input type="text" name="address" id="input_address" class="span12"/>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;add</button>
			<a href="./work/mail/check/import" class="btn btn-small">Datei importieren</a>
		</form>
	</div>
</div>';

$optGroup	= array();
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= UI_HTML_Elements::Options( $optGroup, $filterGroupId );

$panelFilter	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_groupId">Gruppe</label>
					<select name="groupId" id="input_groupId">'.$optGroup.'</select>
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
<script>
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
	else{
		alert("cancelled");
	}
}
</script>';
?>
