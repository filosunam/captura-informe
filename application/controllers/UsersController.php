<?php
// @author: Marco Godínez
// @date: 30/12/2011

class UsersController extends Zend_Controller_Action
{
	/*
	 * users Zend Table
	 * 
	 * @var Application_Model_DbTable_Users
	 *  
	 */
	protected $users;
	protected $usergroup;
    	
	
    public function init()
    {
    	
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('list', 'html')
        			->addActionContext('add', 'html')
					->addActionContext('edit', 'html')
					->addActionContext('save', 'html')
					->addActionContext('trash', 'html')
					->addActionContext('delete', 'html')
					->addActionContext('send', 'html')
					->addActionContext('mail', 'html')
                    ->initContext();
		
    	$this->usergroup = $this->view->user->getIdentity()->group;
        $this->users 	 = new Application_Model_DbTable_Users;
		$request 		 = Zend_Controller_Front::getInstance()->getRequest();
		
		$this->view->status = array(
									'true' => array(),
									'false' => array()
							  );
		
		$notallowed = array(
			'index',
			'list',
			'add',
			'edit',
			'save',
			'trash',
			'delete'
		);
		
		if(in_array($request->getActionName(), $notallowed)
			&& $this->usergroup > 1)
		{
			$this->_redirect('/');			
		}

    }
	
	public function indexAction()
    {
		
	}
	
	public function saveAction()
	{
		
		$id   = $this->_getParam('id');
		$form = $this->getForm();
		
		if ($this->getRequest()->isPost())
		{
			
			if($form->isValid($this->getRequest()->getPost()))
			{
				
				$date = new Zend_Date;
				$now  = $date->get('YYYY-MM-dd HH:mm:ss');
				
				$data = array(
				    'username'   => $form->getValue('username'),
				    'area'	 	 => $form->getValue('area'),
				    'group' 	 => $form->getValue('group'),
				    'email' 	 => $form->getValue('email'),
				    'updated_at' => $now
				);
				
				$passw1 = $form->getValue('password1');
				$passw2 = $form->getValue('password2'); 
				
				if(($passw1 && $passw2) && ($passw1 == $passw2))
					$data['password'] = sha1($passw1); 
				
				
				if($id > 0)
				{
					try {
				       
						$where = $this->users->getAdapter()
											 ->quoteInto('id = ?', $id);
						$this->users->update($data, $where);
						$this->view->status['true'][] = 'Se ha actualizado el usuario correctamente';
					   
				    } catch (Zend_Db_Exception $e) {
				    	
				    	$this->view->status['false'][] = 'Ha ocurrido un error al actualizar';
				    
					}
					
				} else
				{
					try {
						
						$data['created_at'] = $now; 	
						$new = $this->users->createRow($data);
						$new->save();
						$this->view->status['true'][] = 'Se ha creado el nuevo usuario correctamente';
						
					} catch (Zend_Db_Exception $e) {
				    	
				    	$this->view->status['false'][] = 'Ha ocurrido un error al insertar';
				    
					}
					
				}
				
				$this->listAction();
				$this->render('list');
				
			} else
			{
			
				$this->render('edit');
				
			}
			
			
		} else
		{
			
			$this->listAction();
			$this->render('list');
			
		}
	
	
		
		
	}
	
	public function listAction()
	{
		$result = $this->users->select()
							  ->order('area ASC')
							  ->query()
							  ->fetchAll();
							  
		$this->view->list = $result;
	}
	
	public function editAction()
	{
		
		$id   = $this->_getParam('id');
		$form = $this->getForm();
		
		if($id > 0)
		{
			
			$result = $this->users->select()
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
	
	public function trashAction()
	{
		$id 	= $this->_getParam('id');
		$result = $this->users->select()
							  ->where('id = ?', $id)
							  ->query()
							  ->fetchAll();
								 
		$this->view->row = $result[0];
								 
	}
	public function sendAction()
	{
		$id 	= $this->_getParam('id');
		$result = $this->users->select()
							  ->where('id = ?', $id)
							  ->query()
							  ->fetchAll();
								 
		$this->view->row = $result[0];
		
	}
	public function mailAction()
	{
		$id   	  = $this->_getParam('id');
		
		if($id > 1)
		{
			$user 	  = $this->users->find($id);
			$password = $this->return_password();
			
			$data = array('password' => sha1($password));
			$where = $this->users->getAdapter()
								 ->quoteInto('id = ?', $user[0]['id']);
			$response = $this->users->update($data, $where);
			
			if($response)
			{
				
				$mensaje = "Estimado\n\nPor este medio le hacemos llegar las contraseñas necesarias para ingresar al sistema de captura del informe de la Facultad. Esta es la dirección (URL) del sitio: http://informe.filos.unam.mx. En ella encontrará las instrucciones necesarias para realizar su informe.\n\nSu usuario es {$user[0]['username']}\nSu contraseña es $password\n\nDe antemano le extiendo un agradecimiento por su participación.\nReciba mis cordiales saludos,\nErnesto Priani Saisó\n\nP. S.: Para mejorar la herramienta –en su estructura o en las instrucciones–, por favor háganoslo saber por correo-e.";
				
				$mail = new Zend_Mail('UTF-8');
				$mail->setBodyText($mensaje);
				$mail->setFrom('sacadfyl@gmail.com', 'Secretaría Académica, FFyL');
				$mail->addTo($user[0]['email'], $user[0]['area']);
				$mail->addTo('sacadfyl@gmail.com', 'Secretaría Académica, FFyL');
				$mail->setSubject('Acceso al sistema de captura del informe anual');
				$mail->send();
				
				$this->view->status['true'][] = "Se ha generado la contraseña <em>$password</em> para el usuario <em>{$user[0]['username']}</em>";
			}
			
			$this->listAction();
			$this->render('list');
		}
		
	}
	public function deleteAction()
	{
		$id = $this->_getParam('id');
		if($id > 1)
		{
		
			try {
				
				$where =  $this->users->getAdapter()
								  	  ->quoteInto('id = ?', $id);
				
				$this->users->delete($where);
				$this->view->status['true'][] = 'Se ha eliminado el usuario correctamente';
				
						
			} catch (Zend_Db_Exception $e) {
			    	
			   	$this->view->status['false'][] = 'Ha ocurrido un error al eliminar el usuario';
				    
			}
			
			
		}
		
		$this->listAction();
		$this->render('list');
		
	}
	
    public function loginAction()
    {
		
		// Extraemos el formulario de acceso desde forms/Login.php
		$form = $this->view->form = new Application_Form_Login();
		
		// Comprobamos si se envió el formulario
		if ($this->getRequest()->isPost()
			&& $form->isValid($this->getRequest()->getPost()))
		{
			
			Zend_Auth::getInstance()->clearIdentity();
			
			$adapter = new Zend_Auth_Adapter_DbTable(
				$this->users->getDefaultAdapter(),
				'users',
				'username',
				'password'
			);
			
			$adapter->setIdentity($form->getValue('username'));
			$adapter->setCredential(sha1($form->getValue('password')));
			
			// Accedemos con el usuario y contraseña enviados
			$result = Zend_Auth::getInstance()->authenticate($adapter);
			
			// Si no resulta enviamos un mensaje de error
			if(!$result->isValid())
			{
				$this->view->messages = $result->getMessages();
				
			// Si resulta correcto...
			}
			else
			{
				
				// y si se quiso, creamos una cookie que dure 7 días
				if($form->getValue('remember') > 0)
				{
					$maxtime = 3600 * 24 * 7;
					Zend_Session::rememberMe($maxtime);
				}
				
            	Zend_Auth::getInstance()->getStorage()->write($adapter->getResultRowObject());
				
				
				$date  = new Zend_Date;
				$data  = array( 'last_login'   => $date->get('YYYY-MM-dd HH:mm:ss') );
				$where = $this->users->getAdapter()
									 ->quoteInto('id = ?', Zend_Auth::getInstance()->getIdentity()->id);
				$this->users->update($data, $where);
				
				// redirigimos a la página de inicio
				$this->_redirect('/reportes');
			}
			
				
		}
		
    }
	
	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/');
	}
	
	public function forgetAction()
	{
				
		$request = $this->view->request = $this->getRequest()->isPost();
		
		// Extraemos el formulario de recuperación forms/Forget.php
		$form 	 = $this->view->form = new Application_Form_Forget();
		
		// Comprobamos si se envió el formulario
		if ($request && $form->isValid($this->getRequest()->getPost()))
		{
		
			// Comprobamos existencia de correo
			
			// Restauramos contraseña y modificamos fecha de actualización
			
			// Enviamos correo electrónico al usuario

			// Enviando mensaje a la vista
			$this->view->message = $form->getValue('email');
		
				
		}
		
	}
	
	private function getForm()
	{
		return $this->view->form = new Application_Form_User();
	}
	
	private function return_password() {

	// set password length
		$pw_length = 8;
		// set ASCII range for random character generation
		$lower_ascii_bound = 50;          // "2"
		$upper_ascii_bound = 122;       // "z"
		           
		    $notuse = array (58,59,60,61,62,63,64,73,79,91,92,93,94,95,96,108,111);
			
			$i = 0;
			$password = '';
	        while ($i < $pw_length) {
	                mt_srand ((double)microtime() * 1000000);
	                // random limits within ASCII table
	                $randnum = mt_rand ($lower_ascii_bound, $upper_ascii_bound);
	                if (!in_array ($randnum, $notuse)) {
	                        $password = $password . chr($randnum);
	                        $i++;
	                }
	        }
	
		return $password;
	}


}

