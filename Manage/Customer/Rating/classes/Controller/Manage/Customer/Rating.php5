<?php
class Controller_Manage_Customer_Rating extends CMF_Hydrogen_Controller{

	protected $messenger;
	protected $modelCustomer;
	protected $modelRating;

	public function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelCustomer	= new Model_Customer( $this->env );
		$this->modelRating		= new Model_Customer_Rating( $this->env );
		$this->addData( 'useMap', $this->env->getModules()->has( 'UI_Map' ) );
		$this->addData( 'useProjects', TRUE );#$this->env->getModules()->has( 'Manage_Customer_Project' ) );
	}

	public static function ___onRegisterTab( CMF_Hydrogen_Environment_Abstract $env, $context ){
		View_Manage_Customer::registerTab( 'rating/%s', '-Bewertungen' );
	}
	
	public function add( $customerId ){
		$request		= $this->env->getRequest();
		$customer		= $this->modelCustomer->get( $customerId );
		if( $request->has( 'save' ) ){
			$data	= array(
				'affability'	=> $request->get( 'affability' ) >= 1 ? min( 5, $request->get( 'affability' ) ) : 0,
				'guidability'	=> $request->get( 'guidability' ) >= 1 ? min( 5, $request->get( 'guidability' ) ) : 0,
				'growthRate'	=> $request->get( 'growthRate' ) >= 1 ? min( 5, $request->get( 'growthRate' ) ) : 0,
				'profitability'	=> $request->get( 'profitability' ) >= 1 ? min( 5, $request->get( 'profitability' ) ) : 0,
				'paymentMoral'	=> $request->get( 'paymentMoral' ) >= 1 ? min( 5, $request->get( 'paymentMoral' ) ) : 0,
				'adherence'		=> $request->get( 'adherence' ) >= 1 ? min( 5, $request->get( 'adherence' ) ) : 0,
				'uptightness'	=> $request->get( 'uptightness' ) >= 1 ? min( 5, $request->get( 'uptightness' ) ) : 0,
				'customerId'	=> $customerId,
				'timestamp'		=> time()
			);
			$this->modelRating->add( $data );
			$this->env->getMessenger()->noteSuccess( 'Rating has been saved.' );
			$this->restart( './manage/customer/rating/'.$customerId );
		}
		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $customer );
	}

/*	public function index(){
		$modelCustomer	= new Model_Customer( $this->env );
		$modelRating	= new Model_Customer_Rating( $this->env );
		$customers		=  $modelCustomer->getAll();
		foreach( $customers as $nr => $customer ){
			$order		= array( 'timestamp' => 'DESC' );
			$limit		= array( 0, 1 );
			$rating		= $modelRating->getAllByIndex( 'customerId', $customer->customerId, $order, $limit );
			if( $rating ){
				$rating	= array_pop( $rating );
				$rating->index		= $this->modelRating->calculateCustomerIndex( $rating );
			}
			$customer->rating	= $rating;
		}
		$this->addData( 'customers', $customers );
	}
*/
	public function index( $customerId ){
		$modelCustomer	= new Model_Customer( $this->env );
		$modelRating	= new Model_Customer_Rating( $this->env );		

		$customer	= $modelCustomer->get( $customerId );
		$order		= array( 'timestamp' => 'DESC' );
		$limit		= array( 0, 10 );
		$ratings	= $this->modelRating->getAllByIndex( 'customerId', $customerId, $order, $limit );
		$ratings	= array_reverse( $ratings );
		$lastIndex	= 3;
		$totalIndex	= 0;
		$variance	= 0;
		$tendency	= 0;
		foreach( $ratings as $nr => $rating ){
			$rating->index		= $this->modelRating->calculateCustomerIndex( $rating );
			if( !is_null( $lastIndex ) )
				$variance			+= abs( $lastIndex - $rating->index );
			$tendency	+= $rating->index - 3;
			$lastIndex	= $rating->index;
			$totalIndex	+= $rating->index;
		}
		$customer->ratings	= $ratings;
		$customer->index	= $ratings ? $totalIndex / count( $ratings ) : NULL;
		$customer->variance	= count( $ratings ) > 1 ? $variance / ( count( $ratings ) - 1 ) : NULL;
		$customer->tendency	= $ratings ? $tendency / count( $ratings ) : NULL;
		$customer->lastRate	= $ratings ? $lastIndex : NULL;

		$this->addData( 'customerId', $customerId );
		$this->addData( 'customer', $customer );
	}
}
?>
