<?php

//  --  LOAD STATIC HTML CONTENT FILE ...

//  --  ... BY REQUESTED PATH  --  //
if( !empty( $path ) ){
	if( $view->hasContentFile( 'html/'.$path.'.html' ) )
		return $view->loadContentFile( 'html/'.$path.'.html' );
}


//  --  ... OR DEFAULT INDEX  --  //
if( $isInside ){
	if( $view->hasContentFile( 'html/index/index.inside.html' ) )
		if( $content = $view->loadContentFile( 'html/index/index.inside.html' ) )
			return $content;
}
if( $view->hasContentFile( 'html/index/index.html' ) )
	if( $content = $view->loadContentFile( 'html/index/index.html' ) )
		return $content;


//  --  ... OR DEFAULT INDEX THE OLD WAY  --  //
//  @todo	remove this deprecated fallback method
if( $view->hasContentFile( 'html/index.html' ) ){
	return $view->loadContentFile( 'html/index.html'/*, $data*/ );
}

//  --  ... OR RETURN PLACEHOLDER CONTENT --  //
return '
<h2>Hello World!</h2>
<p>
	It seems you just have installed the (rather empty) <cite>Hydrogen</cite> module <cite>App:Site</cite>.<br/>
	To go on, consider to install an application module or start creating HTML files in locale HTML folders.<br/>
</p>
<hr/>
<div class="row-fluid">
	<div class="span4">
		Bacon ipsum dolor sit amet sausage rump leberkas pork belly meatball, tri-tip doner strip steak shank landjaeger pork chop jowl spare ribs. Drumstick turducken venison jowl flank chicken. Pork loin fatback doner tail beef. Turkey tongue sausage spare ribs kevin frankfurter, bresaola tail shank fatback leberkas strip steak drumstick pig ham. Pork loin meatloaf bacon doner flank salami short ribs tail boudin beef ribs. Hamburger fatback ham tail porchetta jowl rump pork loin corned beef andouille filet mignon chuck. Boudin porchetta shankle pancetta doner andouille sausage.
	</div>
	<div class="span4">
		Drumstick venison ground round, t-bone andouille tri-tip sausage beef ribs beef rump porchetta hamburger frankfurter pancetta tail. Frankfurter porchetta leberkas, fatback ribeye rump pork loin sirloin ground round meatloaf. Pork belly bresaola pig pork chop venison cow strip steak tri-tip brisket beef. Porchetta flank ribeye t-bone tail pancetta. Pancetta spare ribs short ribs, prosciutto turducken pork loin doner short loin ground round capicola. Andouille meatball landjaeger biltong pork chop kielbasa.
	</div>
	<div class="span4">
		Spare ribs ham hock shankle ribeye biltong chicken, hamburger ham doner beef ribs tail kielbasa. Ham hock bacon spare ribs, tenderloin pork strip steak ground round. Bresaola biltong filet mignon pork loin swine jerky, venison fatback shoulder tenderloin strip steak tongue drumstick andouille ham. Tail short ribs rump pork belly sausage tongue tenderloin tri-tip pork boudin frankfurter meatball pastrami. Hamburger pork belly tenderloin, shankle shoulder ball tip brisket biltong tri-tip turkey swine beef filet mignon capicola pork.
	</div>
</div>
';
?>
