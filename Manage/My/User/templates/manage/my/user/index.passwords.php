<?php
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
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			UI_HTML_Tag::create( 'td', $labelStatus ),
//			UI_HTML_Tag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[(int) $password->algo] ) ),
//			UI_HTML_Tag::create( 'td', $password->failsTotal ),
		), array( 'class' => $rowClass ) );
	}
	$panelPasswords	= HTML::DivClass( 'content-panel content-panel-form', array(
		UI_HTML_Tag::create( 'h4', 'Passwörter' ),
		HTML::DivClass( 'content-panel-inner', array(
			UI_HTML_Tag::create( 'table', array(
				UI_HTML_Elements::ColumnGroup( array(
					'120px',
					'120px',
					'100px',
//					'',
//					'',
				) ),
				UI_HTML_Tag::create( 'thead', UI_HTML_Elements::tableHeads( array(
					'erstellt',
					'zuletzt genutzt',
					'Zustand',
//					'Kryptografie',
//					'gescheiterte Login',
				) ) ),
				UI_HTML_Tag::create( 'tbody', $rows )
			), array( 'class' => 'table table-condensed table-fixed' ) )
		) ),
	) );
}
return $panelPasswords;
