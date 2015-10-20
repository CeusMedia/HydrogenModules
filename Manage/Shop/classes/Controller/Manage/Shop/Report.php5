<?php
class Controller_Manage_Shop_Report extends Controller_Manage_Shop{

	public function index(){
		$dbc			= $this->env->getDatabase();
		$prefix			= $dbc->getPrefix();
//		$frontend		= Logic_Frontend::getInstance( $this->env );
		$logicBridge	= new Logic_ShopBridge( $this->env );
/*		$bridges		= array();
		foreach( $logicBridge->getBridges() as $bridge )
			$bridges[$bridge->data->bridgeId]	= $bridge->data->title;
		$bridgeId		= (int) $this->request->get( 'bridgeId' );
		if( count( $bridges ) == 1 )
			$bridgeId	= $bridge->data->bridgeId;
		$this->addData( 'bridges', $bridges );
		$this->addData( 'bridgeId', $bridgeId );
*/

		$modelOrder		= new Model_Shop_Order( $this->env );
		$modelPosition	= new Model_Shop_Order_Position( $this->env );
		$modelArticle	= new Model_Catalog_Article( $this->env );
		$orders			= $modelOrder->getAll( array(), array( 'orderId' => 'ASC' ) );
		foreach( $orders as $order ){
			if( (float) $order->price > 0 )
				continue;
			$sum		= 0;
			$tax		= 0;
			$positions	= $modelPosition->getAllByIndex( 'orderId', $order->orderId );
			if( !$positions )
				continue;
			foreach( $positions as $position ){
				if( (int) $position->quantity < 1 )
					continue;
				$article	= $modelArticle->get( $position->articleId ) ;
				if( !$article )
					continue;
				$sum		+= $position->quantity * (float) $article->price;
				$tax		+= round( $position->quantity * (float) $article->price * 1.07, 2 );
				$data		= array(
					'bridgeId'		=> $position->bridgeId ? $position->bridgeId : 1,
					'price'			=> $position->quantity * (float) $article->price,
					'priceTaxed'	=> round( $position->quantity * (float) $article->price * 1.07, 2 )
				);
				$modelPosition->edit( $position->positionId, $data );
			}
			$data	= array(
				'price' 		=> $sum,
				'priceTaxed'	=> $tax,
			);
			$modelOrder->edit( $order->orderId, $data );
		}
		$queryOrdersPerYear	= "
SELECT
	FROM_UNIXTIME(o.createdAt, '%Y') as year,
	COUNT(orderId) AS per_year
FROM ".$prefix."shop_orders AS o
WHERE FROM_UNIXTIME(o.createdAt, '%Y')>=2000
AND o.status >= 2
GROUP BY FROM_UNIXTIME(o.createdAt, '%Y')
ORDER BY year ASC";

		$years	= array();
		foreach( $dbc->query( $queryOrdersPerYear )->fetchAll( PDO::FETCH_OBJ ) as $row ){
			$queryYearTurnover	= "
SELECT
	SUM(o.price) AS turnover,
	SUM(o.priceTaxed) AS turnoverTaxed
FROM
	".$prefix."shop_orders AS o
WHERE
	FROM_UNIXTIME(o.createdAt, '%Y')=".(int)$row->year." AND
	o.status >= 2 AND
	o.customerId > 0";
			$turnover	= $dbc->query( $queryYearTurnover )->fetch( PDO::FETCH_OBJ )->turnover;
			$years[]	= (object) array(
				'year'		=> $row->year,
				'orders'	=> $row->per_year,
				'turnover'	=> $turnover,
			);
		}
		$this->addData( 'ordersPerYear', $years );
	}
}
?>
