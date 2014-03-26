<?php
namespace Mouf\Security\Controllers;

use Mouf\Utils\Action\ActionInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Security\Views\SimpleLoginView;
use Mouf\Html\Widgets\MessageService\Service\SessionMessageService;
use Mouf\Html\Widgets\MessageService\Service\UserMessageInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Security\UserService\UserServiceInterface;

/**
 * A simple controller that provides basic login features.
 * Very useful to get quickly started, although you might want to develop your own or extend it to add custom features.
 * 
 * @author David
 * @Component
 */
class SimpleLoginController extends Controller {
	
	/**
	 * The template to use to display.
	 * 
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * The user service
	 *
	 * @Property
	 * @Compulsory
	 * @var UserServiceInterface
	 */
	public $userService;
	
	/**
	 * Unless a redirecturl parameter is passed in the request, after login, the user will be redirected to this URL.
	 * It is relative to the ROOT_URL and should NOT start with a "/".
	 * 
	 * @Property
	 * @var string
	 */
	public $defaultRedirectUrl;

	/**
	 * The URL to redirect to on logout.
	 * It is relative to the ROOT_URL and should NOT start with a "/".
	 * 
	 * @Property
	 * @var string
	 */
	public $logoutRedirectUrl;
	
	/**
	 * The URL to redirect if user is already logged and tries to access the login page.
	 * It is relative to the ROOT_URL and should NOT start with a "/".
	 * 
	 * @Property
	 * @var string
	 */
	public $ifLoggedRedirectUrl;
	
	/**
	 * The HTML elements that will be displayed before the login box.
	 *
	 * @Property
	 * @var array<HtmlElementInterface>
	 */
	public $contentBeforeLoginBox;
	
	/**
	 * The HTML elements that will be displayed after the login box.
	 *
	 * @Property
	 * @var array<HtmlElementInterface>
	 */
	public $contentAfterLoginBox;
	
	/**
	 * The view object for the login screen.
	 * 
	 * @var SimpleLoginView
	 */
	public $simpleLoginView;

	/**
	 * The service used to display the authentication error message.
	 *
	 * @var SessionMessageService
	 */
	public $messageService;
	
	/**
	 * The content block the template will be writting into.
	 *
	 * @Property
	 * @Compulsory
	 * @var HtmlBlock
	 */
	public $contentBlock;
	
	/**
	 * Actions to be performed before displaying the view
	 * @var array<ActionInterface>
	 */
	public $actions = array();
	
	/**
	 * The label for the error message if login credentials are wrong.
	 *
	 * @Property
	 * @var string|ValueInterface
	 */
	public $badCredentialsLabel;
	
	/**
	 * The index page will display the login form.
	 * 
	 * @Action
	 * @param string $login The login to fill by default.
	 * @param string $redirecturl The URL to redirect to when login is done. If not specified, the default login URL defined in the controller will be used instead.
	 */
	public function defaultAction($login = null, $redirect = null) {
		if ($this->ifLoggedRedirectUrl && $this->userService->isLogged()){
			header("Location:".ROOT_URL.$this->ifLoggedRedirectUrl);
			exit;
		}		
		
		$this->simpleLoginView->login = $login;
		$this->simpleLoginView->redirecturl = $redirect;
		
		
		if (is_array($this->contentBeforeLoginBox)) {
			foreach ($this->contentBeforeLoginBox as $element) {
				$this->contentBlock->addHtmlElement($element);
			}
		}
		
		foreach ($this->actions as $action) {
			$action->run();
		}
		
		$this->contentBlock->addHtmlElement($this->simpleLoginView);
		
		if (is_array($this->contentAfterLoginBox)) {
			foreach ($this->contentAfterLoginBox as $element) {
				$this->contentBlock->addHtmlElement($element);
			}
		}
		
		$this->template->toHtml();
	}
	
	/**
	 * Logs the user in.
	 *
	 * @Action
	 * @param string $login
	 * @param string $password
	 * @param string $redirect
	 */
	public function login($login, $password, $redirect = null) {
		$result = $this->userService->login($login, $password);
		if ($result == false) {
			// Access forbidden:
			header('HTTP/1.1 403 Forbidden');

			$this->messageService->setMessage(ValueUtils::val($this->badCredentialsLabel), UserMessageInterface::ERROR);
			$this->defaultAction($login, $redirect);
			return;
		} else {
			if (!empty($redirect)) {
				header('Location: '.$redirect);
				return;
			} else {
				header('Location: '.ROOT_URL.$this->defaultRedirectUrl);
				return;
			}
		}
	}
	
	/**
	 * Logs the user out.
	 *
	 * @Action
	 */
	public function logout() {
		$this->userService->logoff();
		header("Location: ".ROOT_URL.$this->logoutRedirectUrl);
	}
	
	/**
	 * This function draws an array like $left, or $content.
	 * Those arrays can contain text to draw or function to call.
	 */
	protected function drawArray($array) {
		if (is_array($array)) {
			foreach ($array as $element) {
				$element->toHtml();
			}
		}
	}
	
}