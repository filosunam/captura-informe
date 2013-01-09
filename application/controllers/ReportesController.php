<?php

class ReportesController extends Zend_Controller_Action {

	/*
	 * reportes Zend Table
	 *
	 * @var Application_Model_DbTable_Reportes
	 *
	 */
	protected $reportes;
	public $directory;

	public function init() {

		if ($this->view->user->getIdentity()->id < 1)
			$this->_redirect('/');

		$this->reportes = new Application_Model_DbTable_Reportes;
		$this->messages['correct'] = array();
		$this->messages['error'] = array();

		$this->view->layout()->assign('nestedLayout', 'reportes');
		$this->getCommits();

		$this->directory = APPLICATION_PATH . '/../public/upload' . '/users/' . $this->view->user->getIdentity()->id . '/reportes';

	}

	public function getCommits() {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_Guias;
		$result = $db->select()->where('user_id = ?', $this->view->user->getIdentity()->id)->where('controller = ?', $request->getControllerName())->where('action = ?', 'index')->query()->fetchAll();

		$this->view->commits = $result;
	}

	public function indexAction() {

		$select = $this->reportes->select();
		if ($this->view->user->getIdentity()->username != 'admin')
			$select->where('user_id = ?', $this->view->user->getIdentity()->id);
		$select->order('id DESC');
		$select->query();

		$this->view->list = $this->reportes->fetchAll($select);
		$this->view->directory = $this->directory;

	}

	public function saveAction() {

		$user_id = $this->view->user->getIdentity()->id;
		$form = $this->getForm();

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {

				$id = $form->getValue('id');
				$date = new Zend_Date;
				$now = $date->get('YYYY-MM-dd HH:mm:ss');

				$data = array('anualidad' => $form->getValue('anualidad'), 'resumen' => $form->getValue('resumen'), 'informe' => $form->getValue('informe'), 'updated_at' => $now, 'user_id' => $user_id);

				if ($id > 0) {
					try {

						$where = $this->reportes->getAdapter()->quoteInto('id = ?', $id);
						$this->reportes->update($data, $where);
						$this->messages['correct'][] = 'Se ha actualizado el reporte correctamente';

					} catch (Zend_Db_Exception $e) {

						$this->messages['error'][] = 'Ha ocurrido un error al actualizar';

					}

				} else {
					try {

						$data['created_at'] = $now;
						$new = $this->reportes->createRow($data);
						$last_id = $new->save();
						$this->messages['correct'][] = 'Se ha creado el nuevo reporte correctamente';

					} catch (Zend_Db_Exception $e) {

						$this->messages['error'][] = 'Ha ocurrido un error al insertar';

					}

				}

				$last_id = $last_id > 0 ? $last_id : $id;
				$files = $form->attached->getFileInfo();
				$destination = $this->directory . '/' . $last_id;

				foreach ($files as $file => $info) {
					if ($info['tmp_name']) {
						if (!file_exists($destination))
							mkdir($destination, 0777, true);

						if (!move_uploaded_file($info['tmp_name'], $destination . "/{$info['name']}"))
							$this->messages['error'][] = 'Ha ocurrido un error al subir los archivos adjuntos';
					}

				}
				$error = count($this->messages['error']) > 0 ? base64_encode(serialize($this->messages['error'])) : '';

				$this->redirectWithMessages('/reportes');

			} else {
				$this->getDir();
				$this->view->id = $form->getValue('id');
				$this->render('edit');

			}

		} else {

			$this->redirectWithMessages('/reportes');

		}

	}

	public function redirectWithMessages($url) {
		$this->messages = urlencode(base64_encode(serialize($this->messages)));
		$this->_redirect($url . '?messages=' . $this->messages);
	}

	public function editAction() {

		$id = $this->view->id = $this->_getParam('id');
		$form = $this->getForm();

		if ($id > 0) {

			$select = $this->reportes->select();
			$select->where('id = ?', $id);
			if ($this->view->user->getIdentity()->username != 'admin')
				$select->where('user_id = ?', $this->view->user->getIdentity()->id);
			$select->query();
			$result = $this->reportes->fetchAll($select)->toArray();

			if (count($result) > 0) {
				$form->populate($result[0]);
				$this->getDir();
			} else {
				$this->_redirect('/reportes');
			}

		}

	}

	public function addAction() {
		$this->editAction();
		$this->render('edit');
	}

	public function deleteAction() {
		$id = $this->_getParam('id');
		$result = $this->reportes->select()->where('id = ?', $id)->where('user_id = ?', $this->view->user->getIdentity()->id)->query()->fetchAll();

		$this->view->row = $result[0];

	}

	public function trashAction() {
		$id = $this->_getParam('id');
		if ($id > 0) {

			$where = array($this->reportes->getAdapter()->quoteInto('id = ?', $id), $this->reportes->getAdapter()->quoteInto('user_id = ?', $this->view->user->getIdentity()->id));

			$response = $this->reportes->delete($where);
			$this->messages['correct'][] = 'Se ha eliminado el reporte correctamente';

			$dir = $this->directory . '/' . $id;

			if ($response > 0) {
				if (is_dir($dir)) {
					$objects = scandir($dir);
					foreach ($objects as $object) {
						if ($object != "." && $object != "..") {
							if (filetype($dir . "/" . $object) == "dir")
								rrmdir($dir . "/" . $object);
							else
								unlink($dir . "/" . $object);
						}
					}

					reset($objects);
					rmdir($dir);
				}
			} else {

				$this->messages['error'][] = 'Ha ocurrido un error al eliminar el reporte';

			}

		}

		$this->redirectWithMessages('/reportes');

	}

	public function deletefileAction() {
		$params = $this->view->params = unserialize(base64_decode($this->_getParam('params')));
	}

	public function trashfileAction() {
		$params = $this->view->params = unserialize(base64_decode($this->_getParam('params')));

		$link = $this->directory . "/{$params['id']}/{$params['file']}";

		if (file_exists($link)) {
			if (unlink($link)) {
				$this->messages['correct'][] = "Se ha eliminado el archivo <em>{$params['file']}</em> correctamente";
			} else {
				$this->messages['error'][] = "Ha ocurrido un error al eliminar el archivo <em>{$params['file']}</em>";
			}
		}

		$this->redirectWithMessages('/reportes/edit/' . $params['id']);

	}

	private function getForm() {
		return $this->view->form = new Application_Form_Reporte();
	}

	private function getDir() {
		$this->view->directory = $this->directory;
	}

}
