<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Admin_Mail_Queue extends AjaxController
{
	protected array $statuses	= [
		Model_Mail::STATUS_SENT		=> 'versendet',
		Model_Mail::STATUS_FAILED	=> 'fehlgeschlagen',
		Model_Mail::STATUS_ABORTED	=> 'gescheitert',
	];

	protected array $ranges		= [
		1		=> 'Tag',
		7		=> 'Woche',
		30		=> 'Monat',
		365		=> 'Jahr',
	];

	/**
	 *	@param		?string		$panelId
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function renderDashboardPanel( string $panelId = NULL ): int
	{
		$model	= new Model_Mail( $this->env );

		$data	= [];
		foreach( $this->statuses as $statusKey => $statusLabel ){
			$data[$statusKey]	= [];
			foreach( $this->ranges as $rangeKey => $rangeLabel ){
				$data[$statusKey][$rangeKey]	= $model->count( [
					'status'		=> $statusKey,
					'enqueuedAt'	=> '>= '.( time() - $rangeKey * 24 * 3600 ),
				] );
			}
		}

		$tableHeads		= [''];
		foreach( $this->ranges as $rangeLabel )
			$tableHeads[]	= HtmlTag::create( 'small', $rangeLabel, ['class' => 'pull-right'] );

		$lengthKeys			= array_keys( $this->ranges );
		$lastRangeLength	= @array_pop( $lengthKeys );

		$rows	= [];
		foreach( $this->statuses as $statusKey => $statusLabel ){
			$row	= [];
			foreach( array_reverse( $this->ranges, TRUE ) as $days => $label ){
				$lastRange	= (object) [
					'key'		=> $days,
					'value'		=> $data[$statusKey][$days],
					'label'		=> $label,
				];
				break;
			}
			foreach( array_reverse( $this->ranges, TRUE ) as $rangeKey => $rangeLabel ){
				$label	= $data[$statusKey][$rangeKey];
				if( $rangeKey !== $lastRange->key && $data[$statusKey][$lastRange->key] > 10 ){
					$average	= $lastRange->value ? $data[$statusKey][$lastRange->key] / $lastRange->key : 0;
					$capacity	= $data[$statusKey][$rangeKey] / $rangeKey;
					$change		= $average ? round( ( ( $capacity / $average ) - 1 ) * 100 ) : 0;
					$diff		= $change > 0 ? '+'.$change : $change;
					$label		.= '&nbsp;<small class="muted">'.$diff.'</small>';
				}
				$row[]	= HtmlTag::create( 'td', $label, ['style' => 'text-align: right'] );
			}
			$row[]	= HtmlTag::create( 'th', $statusLabel );
			$rows[]	= HtmlTag::create( 'tr', array_reverse( $row ) );
		}
		$table2	= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '', '15%', '15%', '15%', '15%' ),
			HtmlTag::create( 'thead', HtmlElements::TableHeads( $tableHeads ) ),
			HtmlTag::create( 'tbody', $rows ),
		], [
			'class'		=> 'table table-condensed table-fixed',
		] );
		$table1	= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '20%', '80%' ),
			HtmlTag::create( 'tbody', HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', '<span style="font-size: 3em">'.$model->count( ['status' => 0] ).'</span>', ['style' => 'text-align: right; vertical-align: bottom'] ),
				HtmlTag::create( 'td', '<span>Mails in der<br/>Warteschlange</span>', ['style' => 'vertical-align: bottom'] ),
			] ) ),
		], [
			'class'		=> 'table table-fixed',
		] );
		return $this->respondData( $table1.'<br/>'.$table2 );
	}
}