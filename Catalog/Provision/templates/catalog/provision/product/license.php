<?php

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $product->title.' Lizenzen' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'h4', $license->title ),
		UI_HTML_Tag::create( 'p', $license->description ),
		UI_HTML_Tag::create( 'a', 'in den Warenkorb', array(
			'href'		=> './shop/addArticle/1/'.$license->productLicenseId,
		 	'class'		=> 'btn',
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
