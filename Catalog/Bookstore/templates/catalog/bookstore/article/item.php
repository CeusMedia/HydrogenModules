<?php
if( $thumb )
	return "
<div class='catalog-bookstore-article-item status_".$app_lan."_".$status." "./*$style.*/"'>
	<div class='image'>".$thumb."</div>
	<div class='data withImage'>
		<span class='volume'><em>".$volume."</em></span>
		<span class='author'>".$author."</span>
		<span class='title'><b>".$title."</b></span>
		<span class='subtitle'>".$text."</span>
		<span class='info'>".$info."</span>
		<span class='isbn'>".$isbn."</span>
	</div>
	<div style='clear: left'></div>
</div>";

return "
<div class='catalog-bookstore-article-item status_".$app_lan."_".$status." "./*$style.*/"'>
	<div class='data'>
		<span class='volume'><em>".$volume."</em></span>
		<span class='author'>".$author."</span>
		<span class='title'><b>".$title."</b></span>
		<span class='subtitle'>".$text."</span>
		<span class='info'>".$info."</span><br/>
		<span class='isbn'>".$isbn."</span><br/>
	</div>
</div>";
?>
