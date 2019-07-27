<?php

$iconOpen		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-folder-open" ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-eye" ) );
$iconExists		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-check" ) );
$iconMissing	= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-2x fa-warning" ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => "fa fa-fw fa-remove" ) );

$modalStyle		= new View_Helper_Input_Resource( $env );
$modalStyle->setModalId( 'modal-admin-mail-template-select-style' );
//$modalStyle->setInputId( 'input_template_css' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-admin-mail-template-select-style' );
$trigger->setInputId( 'input_template_style' );
$trigger->setMode( View_Helper_Input_ResourceTrigger::MODE_STYLE );

$listStyles	= '<em class="muted">Keine.</em>';
if( $template->styles ){
	$list	= array();
	foreach( json_decode( $template->styles, TRUE ) as $item ){
		$rowClass	= 'error';
		$buttonOpen	= UI_HTML_Tag::create( 'button', $iconView, array(
			'type'		=> 'button',
			'class'		=> 'btn btn-info disabled',
			'title'		=> 'Style-Datei existiert nicht im angegebenen Pfad (in Frontend-Applikation).',
			'disabled'	=> 'disabled',
		) );
		if( file_exists( $appPath.$item ) ){
			$rowClass	= 'not-success';
			$buttonOpen	= UI_HTML_Tag::create( 'a', $iconView, array(
				'href'		=> $appUrl.$item,
				'class'		=> 'btn btn-info',
				'target'	=> '_blank',
			) );
		}
		$itemFile		= UI_HTML_Tag::create( 'big', pathinfo( $item, PATHINFO_BASENAME ) );
		$itemPath		= UI_HTML_Tag::create( 'small', pathinfo( $item, PATHINFO_DIRNAME ), array( 'class' => 'muted' ) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'class'	=> 'btn btn-inverse pull-right',
			'href'	=> './admin/mail/template/removeStyle/'.$template->mailTemplateId.'/'.base64_encode( $item ),
			'title'	=> 'Style-Verweis entfernen',
		) );
		$buttons	= UI_HTML_Tag::create( 'div', array(
			$buttonOpen,
			$buttonRemove,
		), array( 'class' => 'btn-group' ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $itemFile.'<br/>'.$itemPath ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
		), array( 'class' => $rowClass ) );
	}
	$listStyles	= UI_HTML_Tag::create( 'table', array(
		UI_HTML_Elements::ColumnGroup( array(
			'',
			'120px'
		) ),
		UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
			$words['edit-image-list']['headPath'],
			$words['edit-image-list']['headActions'],
		) ) ),
		UI_HTML_Tag::create( 'tbody', $list ),
	), array(
		'class'	=> 'table table-fixed table-striped',
	) );
}


return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h4>'.$words['edit-style-list']['heading'].'</h4>
			<div class="content-panel-inner">
				'.$listStyles.'
				'.UI_HTML_Tag::create( 'div', $buttonList, array( 'class' => 'buttonbar' ) ).'
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post">
			<div class="content-panel">
				<h4>'.$words['edit-style-add']['heading'].'</h4>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_template_style">'.$words['edit-style-add']['labelPath'].'</label>
							<input type="text" name="template_style" id="input_template_style" class="span12"/>
						</div>
						<div class="span4">
							<label>&nbsp;</label>
							<div class="btn-group">
								'.$trigger.'
								<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$words['edit-style-add']['buttonSave'].'</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
'.$modalStyle;

?>
