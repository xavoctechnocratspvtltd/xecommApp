<?php

namespace xecommApp;

class View_Server_SearchFilter extends \View{
	function init(){
		parent::init();
		
		$form = $this->add('Form');

		$element = $form->add('HtmlElement');
		$slider = $form->addField('hidden','price_range')->set('0:5000000');
		$slider_div = $form->add('View');

		$slider_value_div = $form->add('View');
		$this->js(true)->univ()->AmountRangeSlider($slider_div,$slider,$slider_value_div,0,5000000);

		$form->addSubmit('Filter');
		if($form->isSubmitted()){	
			$range = explode(":", $form['price_range']);
			$form->api->redirect($this->api->url(null,array('subpage'=>'xecomm-dashboard','min'=>$range[0],'max'=>$range[1])));		
		}
	}
}