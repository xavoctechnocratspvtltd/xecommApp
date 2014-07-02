<?php

namespace xecommApp;

class View_Server_Checkout extends \View{
	function init(){
		parent::init();


		$cart_items=$this->api->recall('xecommApp_cart',array());

		// echo"<pre>";
		// print_r($cart_items);
		// echo"</pre>";
		$product=$this->add('xecommApp/Model_Product');

		$form=$this->add('Form');
		$form->addClass( 'stacked' );
		$form->setModel($this->api->xecommauth->model,array('first_name','address'));
		$name_field=$form->getElement('first_name');
		$name_field->setAttr('disabled','true');
		$form->addField('text','shipping_address');
		$i=1;
		$amount_fields_array=array();
		foreach ($cart_items as $ci) {		
			$product->load($ci['product_id']);
			$product_id=$form->addField('hidden','productid_'.$i,'')->set($ci['product_id']);
			

			$product_rate=$form->addField('hidden','productrate_'.$i,'')->set($ci['sale_price']);
			

			$form->addSeparator( 'atk-row noborder' );
			
			$product_field=$form->addField('Readonly','product_'.$i,'Product')->set($ci['product_name']);
			
			$product_field->template->set('row_class','span6');
			$qty_field=$form->addField('line','qty_'.$i,'Qty')->set($ci['qty']);
			$qty_field->template->set('row_class','span2');
			$qty_field->addClass('numberOnly');
			$rate_field=$form->addField('Readonly','rate_'.$i,'Rate')->set($ci['sale_price']);
			
			$rate_field->template->set('row_class','span2');
			
			$amount_field=$form->addField('line','amount_'.$i,'Amount')->set($ci['qty'] * $ci['sale_price']);
			$amount_field->template->set('row_class','span2');
			$amount_field->setAttr( 'disabled', 'true' )->addClass('disabled_input');	
			// $form->add('HR');
				
			$amount_fields_array[] =$amount_field;
			$i++;
		}


		$form->addSeparator( 'atk-row noborder' );
		$points_info_field = $form->addField('line','points_available');
		$points_info_field->setAttr('disabled',true);
		$points_info_field->template->set('row_class','offset8 span2');
		
		$social_user_for_points=$this->add('xsocialApp/Model_AllVerifiedMembers');
		$social_user_for_points->tryLoadBy('emailID',$this->api->xecommauth->model['emailID']);
		if($social_user_for_points->loaded()){
			$points=$social_user_for_points->ref('xsocialApp/PointTransaction')->sum('points');
			$points_info_field->set($points);
		}


		$form->addSeparator( 'atk-row noborder' );
		$total_field = $form->addField('line','total');
		$total_field->setAttr('disabled','true');
		$total_field->template->set('row_class','offset10 span2');

		$points_redeemed_field = $form->addField('line','points_redeemed');
		$points_redeemed_field->template->set('row_class','offset10 span2');		


		$net_amount_field = $form->addField('line','net_amount');
		$net_amount_field->template->set('row_class','offset10 span2');		
		$net_amount_field->setAttr('disabled',true);

 		$points_redeemed_field->js('change')->univ()->calculateNet($total_field,$points_redeemed_field, $net_amount_field, 10, $points, 1000);
		// $total_field->set();
		$i=1;
		$initial_total = 0;
		foreach ($cart_items as $ci) {
			$qty_field = $form->getElement('qty_'.$i);
			$amount_field = $form->getElement('amount_'.$i);
			$product_rate = $form->getElement('productrate_'.$i);


			$qty_field->js('change')->univ()
				->calculateRow($qty_field,$product_rate,$amount_field)
				->calculateTotal($amount_fields_array,$total_field)
				->calculateNet($total_field,$points_redeemed_field, $net_amount_field, 10, $points, 1000)
				;
			// $qty_field->js('change')->univ()

			$initial_total += ($ci['qty'] * $ci['sale_price']);
			$i++;
		}
		

		$total_field->set($initial_total);

		$form->addField('Checkbox','i_read',"I have Read All trems & Conditions")->validateNotNull();
		$form->addSubmit('Proceed');

		if($form->isSubmitted()){

			if(!$form['i_read'])
				$form->displayError('i_read','It is Must');

			if($points)

			$order=$this->add('xecommApp/Model_Order');
			//FILL OTHER VALUES
			$order->placeOrder($form->getAllFields());
			$order->processPayment();

			$this->js()->univ()->redirect($this->api->url(null,array('subpage'=>'xecomm-dashboard')))->execute();

			
		}
	}

	function render(){
		$l=$this->api->locate('addons',__NAMESPACE__, 'location');
		$this->api->pathfinder->addLocation(
			$this->api->locate('addons',__NAMESPACE__),
			array(
		  		'template'=>'templates',
		  		'css'=>'templates/css',
		  		'js'=>'templates/js'
				)
			)->setParent($l);

		$this->api->js()->_load('xecomm-checkout5');

		parent::render();
	}
}