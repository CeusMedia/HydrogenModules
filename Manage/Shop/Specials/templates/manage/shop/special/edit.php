<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconSelect	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
$iconOpen	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconList	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$bridge	= $catalogs[$special->bridgeId];

$script	= '';

$modalStyle		= new View_Helper_Input_Resource( $env );
$modalStyle->setModalId( 'modal-manage-shop-special-select-style' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconSelect );
$trigger->setModalId( 'modal-manage-shop-special-select-style' );
$trigger->setInputId( 'input_styleFile' );
$trigger->setMode( View_Helper_Input_ResourceTrigger::MODE_STYLE );

$listStyleFiles	= array( HtmlTag::create( 'li', HtmlTag::create( 'em', '- keine -', array( 'class' => 'muted' ) ) ) );
if( $special->styleFiles ){
	$listStyleFiles	= [];
	foreach( $special->styleFiles as $nr => $styleFile ){
		$itemFile		= HtmlTag::create( 'big', pathinfo( $styleFile, PATHINFO_BASENAME ) );
		$itemPath		= HtmlTag::create( 'small', pathinfo( $styleFile, PATHINFO_DIRNAME ), array( 'class' => 'muted' ) );
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
				), array( 'class' => 'btn-group pull-right' ) ),
			) )
		) );
	}
}
$listStyleFiles	= HtmlTag::create( 'table', $listStyleFiles, array( 'class' => 'table table-condensed table-striped' ) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Spezialität bearbeiten' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Katalog', array( 'for' => 'input_bridgeId' ) ),
					HtmlTag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'bridgeId',
						'id'		=> 'input_bridgeId',
						'class'		=> 'span12',
						'value'		=> $bridge->data->title,
						'readonly'	=> 'readonly',
					) ),
				), array( 'class' => 'span3' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Artikel', array( 'for' => 'input_articleId' ) ),
					HtmlTag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'articleId',
						'id'		=> 'input_articleId',
						'class'		=> 'span12',
						'value'		=> $special->article->title,
						'readonly'	=> 'readonly',
					) ),
				), array( 'class' => 'span9' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
						'value'	=> $special->title,
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'hr', NULL ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Style-Angaben', array( 'for' => 'input_styleRules' ) ),
					HtmlTag::create( 'textarea', $special->styleRules, array(
						'name'			=> 'styleRules',
						'id'			=> 'input_styleRules',
						'class'			=> 'ace-auto',
						'data-ace-mode'	=> 'css',
						'rows'			=> '20',
					) ),
				), array( 'class' => 'span6' ) ),
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
						), array( 'class' => 'span10' ) ),
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'div', array(
								HtmlTag::create( 'label', '&nbsp;' ),
								$trigger
							), array( 'class' => '' ) ),
						), array( 'class' => 'span2' ) ),
					), array( 'class' => 'row-fluid' ) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
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
			) ), array( 'class' => 'buttonbar' ) ),
		), array(
			'action'	=> './manage/shop/special/edit/'.$special->shopSpecialId,
			'method'	=> 'POST',
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$script.$modalStyle;
