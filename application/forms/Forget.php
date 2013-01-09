<?php

class Application_Form_Forget extends Zend_Form
{

    public function init()
    {
    	
		$this->setMethod(self::METHOD_POST)
			 ->setAttrib('id', 'forget');
		
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'label'    => 'Correo electrónico',
			'required' => true
		)));
		
		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'   => 'Enviar',
			'ignore' => true
		)));
		
    }


}

