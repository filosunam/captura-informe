<?php

class Application_Form_Guia extends Zend_Form
{

    public function init()
    {
        $this->setMethod(self::METHOD_POST)
			 ->setAction('/guias/save')
			 ->setAttrib('id', 'guia')
			 ->setAttrib('class', 'form form1');
		
		$id 	 = new Zend_Form_Element_Hidden(array(
			'name'     => 'id'
		));
		$controller = new Zend_Form_Element_Text(array(
			'name'     => 'controller',
			'label'    => 'Controlador:',
			'required' => true
		));
		$action = new Zend_Form_Element_Text(array(
			'name'     => 'action',
			'label'    => 'Acción:',
			'required' => true
		));
		$user_id = new Application_Form_Element_UserSelect(array(
			'name'     => 'user_id',
			'label'    => 'Área:',
			'required' => true
		));		
		$commit = new Zend_Form_Element_Textarea(array(
			'name'     => 'commit',
			'label'    => 'Instrucción:',
			'class'	   => 'ckeditor',
			'required' => true
		));
		$submit = new Zend_Form_Element_Submit(array(
			'name'     => 'Enviar',
			'ignore'   => true
		));
		
		$elements 	= array(
			$id,
			$user_id,
			$controller,
			$action,
			$commit,
			$submit
		);
		
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$legend	 = $request->getActionName() == 'edit'
				 ? 'Editar'
				 : 'Añadir';
		
		$this->addDisplayGroup(
			$elements,
			'add',
			array( "legend" => $legend . ' instrucción' )
		);
		
		$this->addElements($elements);
		
    }


}

class Application_Form_Element_UserSelect extends Zend_Form_Element_Select {
    public function init() {
        $users = new Application_Model_DbTable_Users();
        $this->addMultiOption(0, 'Selecciona un área');
        foreach ($users->fetchAll() as $users) {
            $this->addMultiOption($users['id'], $users['area']);
        }
    }
}

