<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconSelect	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-folder-open'] );
$iconOpen	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );

$bridge	= $catalogs[$special->bridgeId];

$script	= '';

$modalStyle		= new View_Helper_Input_Resource( $env );
$modalStyle->setModalId( 'modal-manage-shop-special-select-style' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconSelect );
$trigger->setModalId( 'modal-manage-shop-special-select-style' );
$trigger->setInputId( 'input_styleFile' );
$trigger->setMode( View_Helper_Input_ResourceTrigger::MODE_STYLE );

$listStyleFiles	= [HtmlTag::create( 'li', HtmlTag::create( 'em', '- keine -', ['class' => 'muted'] ) )];
if( $special->styleFiles ){
	$listStyleFiles	= [];
	foreach( $special->styleFiles as $nr => $styleFile ){
		$itemFile		= HtmlTag::create( 'big', pathinfo( $styleFile, PATHINFO_BASENAME ) );
		$itemPath		= HtmlTag::create( 'small', pathinfo( $styleFile, PATHINFO_DIRNAME ), ['class' => 'muted'] );
		$listStyleFiles[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $itemFile.'<br/>'.$itemPath ),
			HtmlTag::create( 'td', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'a', $iconOpen, array(
						'href'		=> $appUrl.$styleFile,
						'class'		=> 'btn btn-small btn-info',
						'target'	=> '_blank',
					) ),
					HtmlTag::create( 'a', $iconRemove, array(
						'href'	=> './manage/shop/special/removeStyleFile/'.$special->shopSpecialId.'/'.$nr,
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), ['class' => 'btn-group pull-right'] ),
			) )
		) );
	}
}
$listStyleFiles	= HtmlTag::create( 'table', $listStyleFiles, ['class' => 'table table-condensed table-striped'] );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Spezialität bearbeiten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Katalog', ['for' => 'input_bridgeId'] ),
					HtmlTag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'bridgeId',
						'id'		=> 'input_bridgeId',
						'class'		=> 'span12',
						'value'		=> $bridge->data->title,
						'readonly'	=> 'readonly',
					) ),
				), ['class' => 'span3'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Artikel', ['for' => 'input_articleId'] ),
					HtmlTag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'articleId',
						'id'		=> 'input_articleId',
						'class'		=> 'span12',
						'value'		=> $special->article->title,
						'readonly'	=> 'readonly',
					) ),
				), ['class' => 'span9'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', ['for' => 'input_title'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
						'value'	=> $special->title,
					) ),
				), ['class' => 'span6'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'hr', NULL ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Style-Angaben', ['for' => 'input_styleRules'] ),
					HtmlTag::create( 'textarea', $special->styleRules, array(
						'name'			=> 'styleRules',
						'id'			=> 'input_styleRules',
						'class'			=> 'ace-auto',
						'data-ace-mode'	=> 'css',
						'rows'			=> '20',
					) ),
				), ['class' => 'span6'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Style-Dateien' ),
					$listStyleFiles,
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'label', 'Datei hinzufügen' ),
							HtmlTag::create( 'input', NULL, array(
								'type'		=> 'text',
								'name'		=> 'styleFile',
								'id'		=> 'input_styleFile',
								'class'		=> 'span12',
							) ),
						), ['class' => 'span10'] ),
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'div', array(
								HtmlTag::create( 'label', '&nbsp;' ),
								$trigger
							), ['class' => ''] ),
						), ['class' => 'span2'] ),
					), ['class' => 'row-fluid'] ),
				), ['class' => 'span6'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', array(
				HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
					'href'	=> './manage/shop/special',
					'class'	=> 'btn',
				) ),
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				) ),
				HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
					'href'	=> './manage/shop/special/remove/'.$special->shopSpecialId,
					'class'	=> 'btn btn-danger',
				) ) ,
			) ), ['class' => 'buttonbar'] ),
		), array(
			'action'	=> './manage/shop/special/edit/'.$special->shopSpecialId,
			'method'	=> 'POST',
		) ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] ).$script.$modalStyle;
