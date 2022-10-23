<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconOpen		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-folder-open"] );
$iconView		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-eye"] );
$iconExists		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-check"] );
$iconMissing	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-warning"] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-remove"] );

$modalImage		= new View_Helper_Input_Resource( $env );
$modalImage->setModalId( 'modal-admin-mail-template-select-image' );
$modalImage->setInputId( 'input_template_image' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-admin-mail-template-select-image' );
$trigger->setInputId( 'input_template_image' );

$listImages	= '<em class="muted">'.$words['edit-image-list']['empty'].'</em>';
if( $template->images ){
	$list	= [];
	foreach( json_decode( $template->images, TRUE ) as $nr => $item ){
		$image		= '';
		$rowClass	= 'error';
		$buttonOpen	= HtmlTag::create( 'button', $iconView, array(
			'type'		=> 'button',
			'class'		=> 'btn btn-info disabled',
			'title'		=> 'Bild-Datei existiert nicht im angegebenen Pfad (in Frontend-Applikation).',
			'disabled'	=> 'disabled',
		) );
		if( file_exists( $appPath.$item ) ){
			$image		= HtmlTag::create(' img', NULL, array(
				'src' 	=> $appUrl.$item,
				'style'	=> 'max-height: 40px',
			) );
			$image		= HtmlTag::create(' a', $image, array(
				'href'		=> $appUrl.$item,
				'title'		=> 'Bildverweis in neuem Browser-Tab anzeigen',
				'target'	=> '_blank',
			) );
			$rowClass	= 'success';
			$buttonOpen	= HtmlTag::create( 'a', $iconView, array(
				'href'		=> $appUrl.$item,
				'class'		=> 'btn btn-info',
				'title'		=> 'Bildverweis in neuem Browser-Tab anzeigen',
				'target'	=> '_blank',
			) );
		}
		$itemFile		= HtmlTag::create( 'big', pathinfo( $item, PATHINFO_BASENAME ) );
		$itemPath		= HtmlTag::create( 'small', pathinfo( $item, PATHINFO_DIRNAME ), ['class' => 'not-muted'] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'class'	=> 'btn btn-inverse pull-right',
			'href'	=> './admin/mail/template/removeImage/'.$template->mailTemplateId.'/'.base64_encode( $item ),
			'title'	=> 'Bildverweis entfernen',
		) );
		$buttons	= HtmlTag::create( 'div', array(
			$buttonOpen,
			$buttonRemove,
		), ['class' => 'btn-group'] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $image ),
			HtmlTag::create( 'td', '<strong><kbd>image'.( $nr + 1).'</kbd></strong>' ),
			HtmlTag::create( 'td', $itemFile.'<br/>'.$itemPath ),
			HtmlTag::create( 'td', $buttons, array( 'style' => 'text-align: right') ),
		), ['class' => $rowClass] );
	}
	$listImages	= HtmlTag::create( 'table', array(
		HtmlElements::ColumnGroup( array(
			'120px',
			'100px',
			'',
			'120px'
		) ),
		HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
			$words['edit-image-list']['headImage'],
			$words['edit-image-list']['headId'],
			$words['edit-image-list']['headPath'],
			$words['edit-image-list']['headActions'],
		) ) ),
		HtmlTag::create( 'tbody', $list ),
	), array(
		'class'	=> 'table table-fixed table-striped',
	) );
}

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h4>'.$words['edit-image-list']['heading'].'</h4>
			<div class="content-panel-inner">
				'.$listImages.'
				'.HtmlTag::create( 'div', $buttonList, ['class' => 'buttonbar'] ).'
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h4>'.$words['edit-image-add']['heading'].'</h4>
			<div class="content-panel-inner">
				<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post" class="not-form-changes-auto">
					<div class="row-fluid">
						<div class="span9">
							<label for="input_template_image">'.$words['edit-image-add']['labelPath'].'</label>
							<input type="text" name="template_image" id="input_template_image" class="span12" required="required"/>
						</div>
						<div class="span3">
							<label>&nbsp;</label>
							<div class="btn-group">
								'.$trigger.'
								<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$words['edit-image-add']['buttonSave'].'</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>'.$modalImage;


?>
