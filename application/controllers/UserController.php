<?php

namespace application\controllers;

use application\core\Controller;

class UserController extends Controller
{
	public function registerAction()
	{
		if (!empty($_POST)) {
			if (!$this->model->validate(['email', 'login', 'password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			} elseif (!$this->model->checkEmailExists($_POST['email'])) {
				$this->view->message('error', $this->model->error);
			} elseif (!$this->model->checkLoginExists($_POST['login'])) {
				$this->view->message('error', $this->model->error);
			}
			$this->model->register($_POST);
			$this->view->message('success', 'Регистрация завершена, подтвердите свой E-mail');
		}
		$this->view->render('Регистрация');
	}

	public function confirmAction()
	{
		if (!$this->model->checkTokenExists($this->route['token'])) {
			$this->view->redirect('user/login');
		}
		$this->model->activate($this->route['token']);
		$this->view->render('Аккаунт активирован');
	}

	public function loginAction()
	{
		if (!empty($_POST)) {
			if (!$this->model->validate(['login', 'password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			} elseif (!$this->model->checkData($_POST['login'], $_POST['password'])) {
				$this->view->message('error', 'Логин или пароль указан неверно');
			} elseif (!$this->model->checkStatus('login', $_POST['login'])) {
				$this->view->message('error', $this->model->error);
			}
			$this->model->login($_POST['login']);
			$this->view->location('user/account');
		}
		$this->view->render('Вход');
	}

	public function accountAction()
	{
		if (!empty($_POST)) {
			if (!$this->model->validate(['email'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			$id = $this->model->checkEmailExists($_POST['email']);
			if ($id and $id != $_SESSION['user']['id']) {
				$this->view->message('error', 'Этот E-mail уже используется');
			}
			if (!empty($_POST['password']) and !$this->model->validate(['password'], $_POST)) {
				$this->view->message('error', $this->model->error);
			}
			$this->model->save($_POST);
			$this->view->message('error', 'Сохранено');
		}
		$this->view->render('Профиль');
	}

	public function logoutAction()
	{
		unset($_SESSION['user']);
		$this->view->redirect('user/login');
	}

	public function recoveryAction()
	{
		if (!empty($_POST)) {
			if (!$this->model->validate(['email'], $_POST)) {
				$this->view->message('error', $this->model->error);
			} elseif (!$this->model->checkEmailExists($_POST['email'])) {
				$this->view->message('error', 'Пользователь не найден');
			} elseif (!$this->model->checkStatus('email', $_POST['email'])) {
				$this->view->message('error', $this->model->error);
			}
			$this->model->recovery($_POST);
			$this->view->message('success', 'Запрос на восстановление пароля отправлен на E-mail');
		}
		$this->view->render('Восстановление пароля');
	}

	public function resetAction()
	{
		if (!$this->model->checkTokenExists($this->route['token'])) {
			$this->view->redirect('user/login');
		}
		$password = $this->model->reset($this->route['token']);
		$vars = [
			'password' => $password,
		];
		$this->view->render('Пароль сброшен', $vars);
	}
}
