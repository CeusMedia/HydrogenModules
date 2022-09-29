<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Novelty_DashboardPanel extends Abstraction
{
	protected $env;
	protected $news		= [];

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function add( $item ): self
	{
		$this->news[]	= $item;
		return $this;
	}

	public function render(): string
	{
		$options	= [];
		$this->env->getCaptain()->callHook( 'Novelties', 'collect', $this, $options );
		if( !$this->news )
			return '';
		$list	= [];
		$userId		= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$model		= new Model_Novelty( $this->env );
		$iconAck	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
		foreach( $this->news as $item ){
			$conditions	= array(
				'userId'	=> $userId,
				'type'		=> $item->type,
				'entryId'	=> $item->id,
				'timestamp'	=> $item->timestamp,
			);
			if( $model->count( $conditions ) )
				continue;
			$key	= $item->timestamp.'.'.microtime( TRUE );
			$link	= HtmlTag::create( 'a', $item->title, ['href' => $item->url] );
			$date	= View_Helper_Work_Time::formatSeconds( time() - $item->timestamp, '&nbsp;', TRUE );

			$badgeUnit	= substr( $date, -1 );
			$badgeClass	= $badgeUnit == 'm' ? 'important' : ( $badgeUnit == 'h' ? 'info' : '' );


			$date		= HtmlTag::create( 'small', $date, array(
				'class' => 'label label-'.$badgeClass,
				'style'	=> 'font-weight: normal'
			) );
			$buttons	= array(
				HtmlTag::create( 'button', $iconAck, array(
					'class'				=> 'btn btn-mini',
					'title'				=> 'ausblenden',
					'data-type'			=> $item->type,
					'data-id'			=> $item->id,
					'data-timestamp'	=> $item->timestamp,
				) ),
			);
			$type		= isset( $item->typeLabel ) ? $item->typeLabel : $item->type;
			$type		= HtmlTag::create( 'small', $type, ['class' => 'muted'] );
			$list[$key]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $date, ['style' => 'text-align: right'] ),
				HtmlTag::create( 'td', $type.'&nbsp;'.$link, ['class' => 'autocut'] ),
				HtmlTag::create( 'td', $buttons, ['style' => 'text-align: right'] ),
			) );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, $this->limit );
		$colgroup	= HtmlElements::ColumnGroup( "45", "", "50" );
		$tbody	= HtmlTag::create( 'tbody', $list );
		$list	= HtmlTag::create( 'table', $colgroup.$tbody, array(
			'class'		=> 'table not-table-striped table-condensed table-fixed',
		) );
$script	= '
<script>
var InfoNoveltyDashboardPanel = {
	init: function(){
		jQuery("#dashboard-panel-info-novelty button.btn").on("click", function(){
			var that = jQuery(this);
			that.parent().parent().fadeOut();
			jQuery.ajax({
				url:  "./info/novelty/ajax/dismiss/",
				data: jQuery(this).data(),
				method: "POST",
				dataType: "json",
				success: function(json){
//					console.log(json);
				}
			});
		});
	}
};
InfoNoveltyDashboardPanel.init();
</script>
';
		return $list.$script;
	}

	public function setLimit( int $limit ): self
	{
		$this->limit	= min( 100, max( 0, abs( $limit ) ) );
		return $this;
	}
}
