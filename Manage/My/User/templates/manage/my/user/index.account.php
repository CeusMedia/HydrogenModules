<?php
$w		= (object) $words['account'];

return HTML::DivClass( 'content-panel content-panel-info', array(
	UI_HTML_Tag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::BR,
		HTML::DivClass( 'row-fluid', array(
			HTML::DivClass( 'span12', array(
				HTML::DivClass( 'row-fluid', array(
					HTML::DivClass( 'span3', array(
						HTML::Label( 'username', $w->labelUsername ),
						UI_HTML_Tag::create( 'div',
							UI_HTML_Tag::create( 'big',
								UI_HTML_Tag::create( 'strong', htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					) ),
					HTML::DivClass( 'span6', array(
						HTML::Label( 'email', $w->labelEmail ),
						UI_HTML_Tag::create( 'div',
							UI_HTML_Tag::create( 'big',
								UI_HTML_Tag::create( 'strong', htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ) )
							)
						)
					) ),
					HTML::DivClass( 'span3', array(
						HTML::Label( 'role', $w->labelRole ),
						UI_HTML_Tag::create( 'div',
							UI_HTML_Tag::create( 'big',
								UI_HTML_Tag::create( 'strong',
									UI_HTML_Tag::create( 'span', $user->role->title, array( 'class' => 'role role'.$user->role->roleId ) )
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
