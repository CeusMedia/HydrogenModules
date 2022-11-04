<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['account'];

return HTML::DivClass( 'content-panel content-panel-info', array(
	HtmlTag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::BR,
		HTML::DivClass( 'row-fluid', array(
			HTML::DivClass( 'span12', array(
				HTML::DivClass( 'row-fluid', array(
					HTML::DivClass( 'span3', array(
						HTML::Label( 'username', $w->labelUsername ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong', htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					) ),
					HTML::DivClass( 'span6', array(
						HTML::Label( 'email', $w->labelEmail ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong', htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					) ),
					HTML::DivClass( 'span3', array(
						HTML::Label( 'role', $w->labelRole ),
						HtmlTag::create( 'div',
							HtmlTag::create( 'big',
								HtmlTag::create( 'strong',
									HtmlTag::create( 'span', $user->role->title, ['class' => 'role role'.$user->role->roleId] )
								)
							)
						)
					) )
				) )
			) )
		) ),
		HTML::BR
	) )
) );
?>
