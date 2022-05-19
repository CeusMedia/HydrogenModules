<?php
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconSelect	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
$iconOpen	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$bridge	= $catalogs[$special->bridgeId];

$script	= '';

$modalStyle		= new View_Helper_Input_Resource( $env );
$modalStyle->setModalId( 'modal-manage-shop-special-select-style' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconSelect );
$trigger->setModalId( 'modal-manage-shop-special-select-style' );
$trigger->setInputId( 'input_styleFile' );
$trigger->setMode( View_Helper_Input_ResourceTrigger::MODE_STYLE );

$listStyleFiles	= array( UI_HTML_Tag::create( 'li', UI_HTML_Tag::create( 'em', '- keine -', array( 'class' => 'muted' ) ) ) );
if( $special->styleFiles ){
	$listStyleFiles	= [];
	foreach( $special->styleFiles as $nr => $styleFile ){
		$itemFile		= UI_HTML_Tag::create( 'big', pathinfo( $styleFile, PATHINFO_BASENAME ) );
		$itemPath		= UI_HTML_Tag::create( 'small', pathinfo( $styleFile, PATHINFO_DIRNAME ), array( 'class' => 'muted' ) );
		$listStyleFiles[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $itemFile.'<br/>'.$itemPath ),
			UI_HTML_Tag::create( 'td', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'a', $iconOpen, array(
						'href'		=> $appUrl.$styleFile,
						'class'		=> 'btn btn-small btn-info',
						'target'	=> '_blank',
					) ),
					UI_HTML_Tag::create( 'a', $iconRemove, array(
						'href'	=> './manage/shop/special/removeStyleFile/'.$special->shopSpecialId.'/'.$nr,
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group pull-right' ) ),
			) )
		) );
	}
}
$listStyleFiles	= UI_HTML_Tag::create( 'table', $listStyleFiles, array( 'class' => 'table table-condensed table-striped' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Spezialität bearbeiten' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Katalog', array( 'for' => 'input_bridgeId' ) ),
					UI_HTML_Tag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'bridgeId',
						'id'		=> 'input_bridgeId',
						'class'		=> 'span12',
						'value'		=> $bridge->data->title,
						'readonly'	=> 'readonly',
					) ),
				), array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Artikel', array( 'for' => 'input_articleId' ) ),
					UI_HTML_Tag::create( 'input', '', array(
						'type'		=> 'text',
						'name'		=> 'articleId',
						'id'		=> 'input_articleId',
						'class'		=> 'span12',
						'value'		=> $special->article->title,
						'readonly'	=> 'readonly',
					) ),
				), array( 'class' => 'span9' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
						'value'	=> $special->title,
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'hr', NULL ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Style-Angaben', array( 'for' => 'input_styleRules' ) ),
					UI_HTML_Tag::create( 'textarea', $special->styleRules, array(
						'name'			=> 'styleRules',
						'id'			=> 'input_styleRules',
						'class'			=> 'ace-auto',
						'data-ace-mode'	=> 'css',
						'rows'			=> '20',
					) ),
				), array( 'class' => 'span6' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Style-Dateien' ),
					$listStyleFiles,
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', 'Datei hinzufügen' ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'text',
								'name'		=> 'styleFile',
								'id'		=> 'input_styleFile',
								'class'		=> 'span12',
							) ),
						), array( 'class' => 'span10' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'label', '&nbsp;' ),
								$trigger
							), array( 'class' => '' ) ),
						), array( 'class' => 'span2' ) ),
					), array( 'class' => 'row-fluid' ) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', join( ' ', array(
				UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
					'href'	=> './manage/shop/special',
					'class'	=> 'btn',
				) ),
				UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				) ),
				UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
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
