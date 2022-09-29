<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelPasswords		= '';
$atLeastOne			= TRUE;
if( !$atLeastOne || count( $passwords ) > 1 ){
	$passwordCryptTypes = array_flip( ADT_Constant::getAll( 'PASSWORD_' ) );

	$rows	= [];
	foreach( $passwords as $password ){
		$rowClass	= 'info';
		if( $password->status == 0 )
			$rowClass	= 'warning';
		if( $password->status == 1 )
			$rowClass	= 'success';
		$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
		$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
		$labelStatus	= $words['password-statuses'][$password->status];
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			HtmlTag::create( 'td', $labelStatus ),
//			HtmlTag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[(int) $password->algo] ) ),
//			HtmlTag::create( 'td', $password->failsTotal ),
		), ['class' => $rowClass] );
	}
	$panelPasswords	= HTML::DivClass( 'content-panel content-panel-form', array(
		HtmlTag::create( 'h4', 'Passwörter' ),
		HTML::DivClass( 'content-panel-inner', array(
			HtmlTag::create( 'table', array(
				HtmlElements::ColumnGroup( array(
					'120px',
					'120px',
					'100px',
//					'',
//					'',
				) ),
				HtmlTag::create( 'thead', HtmlElements::tableHeads( array(
					'erstellt',
					'zuletzt genutzt',
					'Zustand',
//					'Kryptografie',
//					'gescheiterte Login',
				) ) ),
				HtmlTag::create( 'tbody', $rows )
			), ['class' => 'table table-condensed table-fixed'] )
		) ),
	) );
}
return $panelPasswords;
