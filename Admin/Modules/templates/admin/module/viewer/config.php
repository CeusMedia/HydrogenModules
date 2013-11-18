<?php

$w	= (object) $words['tab-config'];

$count	= 0;
$list	= '-';
if( $module->config ){
	$rows	= array();
	foreach( $module->config as $item ){
		$count++;
		if( $item->type == 'boolean' )
			$item->value	= $words['boolean-values'][( $item->value ? 'yes' : 'no' )];
		
		if( preg_match( "/password/", $item->key ) )
			$item->value	= '<em class="muted">hidden</em>';
//		else if( $item->protected === "yes" )
//			$item->value	= '<em class="muted">protected</em>';
		
		$key		= UI_HTML_Tag::create( 'td', $item->key );
		$value		= UI_HTML_Tag::create( 'td', $item->value, array( 'class' => 'config-type-'.$item->type ) );
		$rows[$item->key]	= '<tr>'.$key.$value.'</tr>';
	}
	natcasesort( $rows );
	$heads	= UI_HTML_Elements::TableHeads( array( $w->headKey, $w->headValue ) );
	$list	= '<table>'.$heads.join( $rows ).'</table>';
}

return $list.'<div class="clearfix"></div>
<script>
/*
$(document).ready(function(){
	$("dd").each(function(){
		$(this).click(function(){
			var dd = $(this);
			if(!dd.data("state")){
				dd.data("key",dd.prev().html());
				dd.data("type",dd.attr("rel"));
				dd.data("value",dd.html());
				dd.data("state",1);
			}
			if(dd.data("state") == 1){
				$("dd>input").each(function(){
					var dd = $(this).parent();
					dd.html(dd.data("value"));
				});
				if(dd.data("type") == "string")
					var input = $("<input/>").val(dd.data("value")).attr("type","text");
				else if(type == "boolean"){
					var input = $("<input/>").attr("type","checkbox");
					var checked = value == "yes" || value == "1";
					input.attr("checked",checked ? "checked" : "");
					input.val(1).attr("name",key).data("value",value);
				}
				$(this).html(input);
				dd.data("state",2);
			}
			else if(!dd.data("state") == 2){
				dd.data("state",1);
			}
		});
	});
});*/
</script>
';
?>