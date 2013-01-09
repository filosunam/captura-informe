<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initRequest()
    {
    	
		$front  = Zend_Controller_Front::getInstance(); 
		$router = $front->getRouter();
		
		$router->addRoute(
			'file_params',
		    new Zend_Controller_Router_Route(
		        '/:controller/:action/:params'
		    )
		);
		$router->addRoute(
			'id',
		    new Zend_Controller_Router_Route(
		        '/:controller/:action/:id',
		        array('id' => 0),
		        array('id' => '\d+')
		    )
		);
		
		
		
	}

	protected function _initDoctype()
	{
    	
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT'); //HTML5


		$this->view->user = Zend_Auth::getInstance();

	}
	
}