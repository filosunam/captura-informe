<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setMethod(self::METHOD_POST)
			 ->setAttrib('id', 'login')
			 ->setAttrib('class', 'form form2');
		
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'username',
			'label'    => 'Nombre de usuario',
			'required' => true
		)));
		
		$this->addElement(new Zend_Form_Element_Password(array(
			'name'     => 'password',
			'label'    => 'Contraseña',
			'required' => true
		)));
		
		$this->addElement(new Zend_Form_Element_Checkbox(array(
			'name'   => 'remember',
			'ignore' => true,
			'label'  => 'No cerrar sesión'
		)));
		
		/*
		$this->addElement(new Zend_Form_Element_Captcha('captcha', array(
			'label'	  => 'Introduce el texto de la imagen',
			'captcha' => array( 'captcha' => 'Image',
								'name'    => 'myCaptcha',
								'wordLen' => 6,
								'timeout' => 10,
								'font'    => 'css/BebasNeue-webfont.ttf',  
								'imgDir'  => './img/captcha',
								'imgUrl'  => '/img/captcha/' )
		)));
		*/
		
		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'   => 'Entrar',
			'ignore' => true
		)));
		
    }


}

