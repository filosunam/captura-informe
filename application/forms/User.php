<?php

class Application_Form_User extends Zend_Form
{

    public function init()
    {
    	$this->setMethod(self::METHOD_POST)
			 ->setAction('/users/save/format/html')
			 ->setAttrib('id', 'user')
			 ->setAttrib('class', 'freq form form1');
		
		$elements = array();
		
		$elements[] = new Zend_Form_Element_Hidden(array(
			'name'     => 'id'
		));
		$elements[] = new Zend_Form_Element_Text(array(
			'name'     => 'area',
			'label'    => 'Área:',
			'required' => true
		));
		$elements[] = new Zend_Form_Element_Text(array(
			'name'     => 'username',
			'label'    => 'Nombre de usuario:',
			'required' => true
		));
		$elements[] = new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'label'    => 'Correo electrónico:',
			'required' => true
		));
		$elements[] = new Zend_Form_Element_Text(array(
			'name'     => 'group',
			'label'    => 'Grupo:',
			'required' => true
		));
		$elements[] = new Zend_Form_Element_Password(array(
			'name'     => 'password1',
			'label'    => 'Contraseña:',
			'required' => false
		));
		$elements[] = new Zend_Form_Element_Password(array(
			'name'     => 'password2',
			'label'    => 'Repetir contraseña:',
			'required' => false
		));
		$elements[] = new Zend_Form_Element_Submit(array(
			'name'     => 'Enviar',
			'ignore'   => true
		));
		
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$legend	 = $request->getActionName() == 'edit'
				 ? 'Editar'
				 : 'Añadir';
		
		$this->addDisplayGroup(
			$elements,
			'add',
			array(
				"legend" => $legend . ' reporte'
			)
		);
		
		$this->addElements($elements);
		

    }


}

