<?php

class GuiasController extends Zend_Controller_Action
{
	/*
	 * guias Zend Table
	 * 
	 * @var Application_Model_DbTable_Guias
	 *  
	 */
	protected $guias;
	protected $usergroup;

    public function init()
    {
		$request 	= Zend_Controller_Front::getInstance()->getRequest();
		$actionName = $request->getActionName();
		
    	$this->usergroup = $this->view->user->getIdentity()->group;
		if($this->usergroup > 1 && $actionName != 'toggle')
			$this->_redirect("/");
		
		
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('toggle', 'json')
					->setAutoJsonSerialization(true)
                    ->initContext();
					
		$this->guias = new Application_Model_DbTable_Guias;
		$this->view->layout()->assign('nestedLayout', 'guias');
		
		$this->messages['correct']  = array();
		$this->messages['error']    = array();
    }

    public function indexAction()
    {
    	$result  = $this->guias->select()
							   ->order('id DESC')
							   ->query()
							   ->fetchAll();
							   				
											
		$this->view->list = $result;
		
    }
	
	public function listAction()
	{
		
	}
	
	public function saveAction()
	{
		
		$form 	 = $this->getForm();
		$request = $this->getRequest();
		
		if ($request->isPost() && $form->isValid($request->getPost()))
		{
				
				$id   = $form->getValue('id');
				
				$data = array(
				    'commit'  	 => $form->getValue('commit'),
				    'controller' => $form->getValue('controller'),
				    'action' 	 => $form->getValue('action'),
				    'user_id'    => $form->getValue('user_id')
				);
				
				if($id > 0)
				{
					try {
				        
						$where = $this->guias->getAdapter()
											 ->quoteInto('id = ?', $id);
						$this->guias->update($data, $where);
						$this->messages['correct'][] = 'Se ha actualizado la instrucción correctamente';
					   
				    } catch (Zend_Db_Exception $e) {
				    	
				    	$this->messages['error'][] = 'Ha ocurrido un error al actualizar';
				    
					}
				
				} else
				{
					try {
						
						$new = $this->guias->createRow($data);
						$new->save();
						$this->messages['correct'][] = 'Se ha creado la nueva instrucción correctamente';
						
					} catch (Zend_Db_Exception $e) {
				    	
				    	$this->messages['error'][] = 'Ha ocurrido un error al insertar';
				    
					}
					
				}
				
				
				$this->redirectWithMessages('/guias');
				
								
		}

		$this->render('edit');
		
	}

	public function redirectWithMessages($url)
	{
		$this->messages = urlencode(base64_encode(serialize($this->messages)));
		$this->_redirect($url . '?messages=' . $this->messages);
	}
	
	public function editAction()
	{
		
		$id   = $this->view->id = $this->_getParam('id');
		$form = $this->getForm();
		
		if($id > 0)
		{
			
			$result = $this->guias->select()
								  ->where('id = ?', $id)
								  ->query()
								  ->fetchAll();
			
			$form->populate($result[0]);
			
		}

	}
	
	public function addAction()
	{
		$this->editAction();
		$this->render('edit');
	}
	
	public function toggleAction()
	{
		
		$guias 	= new Zend_Session_Namespace('Guias');
		$guias->show = $guias->show > 0
					 ? 0
					 : 1;
		$this->view->guias->show = $guias->show;
		
	}
	
	private function getForm()
	{
		return $this->view->form = new Application_Form_Guia();
	}

	public function deleteAction()
	{
		$id = $this->_getParam('id');
		$result = $this->guias->select()
							  ->where('id = ?', $id)
							  ->query()
							  ->fetchAll();
								 
		$this->view->row = $result[0];
								 
	}
	public function trashAction()
	{
		$id = $this->_getParam('id');
		if($id > 0)
		{
			
			$where = array(
				$this->guias->getAdapter()
							->quoteInto('id = ?', $id)
			);
			
			$response = $this->guias->delete($where);
			$this->messages[($response > 0) ? 'correct': 'error'][] = $response > 0
																	? 'Se ha eliminado la instrucción correctamente'
																	: 'Ha ocurrido un error al eliminar la instrucción';
				   
		}
		
		$this->redirectWithMessages('/guias');
		
	}

}

