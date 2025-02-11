<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string|int,string|int>> $words */
/** @var object $user */

$w		= (object) $words['account'];

return HTML::DivClass( 'content-panel content-panel-info', [
	HtmlTag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', [
		HTML::BR,
		HTML::DivClass( 'row-fluid', [
			HTML::DivClass( 'span12', [
				HTML::DivClass( 'row-fluid', [
					HTML::DivClass( 'span3', [
						HTML::Label( 'username', $w->labelUsername ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong', htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					] ),
					HTML::DivClass( 'span6', [
						HTML::Label( 'email', $w->labelEmail ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong', htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					] ),
					HTML::DivClass( 'span3', [
						HTML::Label( 'role', $w->labelRole ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong',
									HtmlTag::create( 'span', $user->role->title, ['class' => 'role role'.$user->role->roleId] )
								)
							)
						)
					] )
				] )
			] )
		] ),
		HTML::BR
	] )
] );
