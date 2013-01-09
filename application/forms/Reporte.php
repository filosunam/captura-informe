<?php

class Application_Form_Reporte extends Zend_Form
{

    public function init()
    {
        
		
		$this->setMethod(self::METHOD_POST)
			 ->setAction('/reportes/save')
			 ->setAttrib('id', 'reporte')
			 ->setAttrib('class', 'form form1')
			 ->setAttrib('enctype', 'multipart/form-data');
		
		$id 	 = new Zend_Form_Element_Hidden(array(
			'name'     => 'id'
		));
		$periodo = new Zend_Form_Element_Text(array(
			'name'     => 'anualidad',
			'label'    => 'Año:',
			'required' => true
		));
		$resumen = new Zend_Form_Element_Textarea(array(
			'name'     => 'resumen',
			'label'    => 'Resumen:',
			'class'	   => 'ckeditor',
			'required' => true
		));
		$informe = new Zend_Form_Element_Textarea(array(
			'name'     => 'informe',
			'label'    => 'Informe completo:',
			'class'	   => 'ckeditor',
			'required' => true
		));
		
		
		$files = new Zend_Form_Element_File('attached');
		$files->setLabel('Archivos adjuntos (Permitidos: jpg,png,gif,tiff,txt,xls,doc,xlsx,docx,ppt,pptx,pps,pdf):')
			  ->setIgnore(true);
		$files->addValidator('Count', false, array('min' => 0, 'max' => 6));
		$files->addValidator('Size', false, 50120000); // 50MB
		$files->addValidator('Extension', false, 'jpg,png,gif,tiff,txt,xls,doc,xlsx,docx,ppt,pptx,pps,pdf');
		$files->setMultiFile(6);
		
		
		$submit = new Zend_Form_Element_Submit(array(
			'name'     => 'Enviar',
			'ignore'   => true
		));
		
		$elements 	= array(
			$id,
			$periodo,
			$resumen,
			$informe,
			$files,
			$submit
		);
		
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$legend	 = $request->getActionName() == 'edit'
				 ? 'Editar'
				 : 'Añadir';
		
		$this->addDisplayGroup(
			$elements,
			'add',
			array( "legend" => $legend . ' reporte' )
		);
		
		$this->addElements($elements);
		
    }


}

