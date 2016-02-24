<?php
namespace Mouf\Security\Controllers;

use Mouf\Mvc\Splash\HtmlResponse;
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
use Zend\Diactoros\Response\RedirectResponse;

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
	public function defaultAction($login = null, $redirect = null, $responseCode = 200) {
        if($this->userService->isLogged()) {
            if(!$redirect) {
                header("Location: " . ROOT_URL . $this->ifLoggedRedirectUrl);
            } else {
                header("Location: ".$redirect);
            }
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
		
        return new HtmlResponse($this->template, $responseCode);
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

			$this->messageService->setMessage(ValueUtils::val($this->badCredentialsLabel), UserMessageInterface::ERROR);
			return $this->defaultAction($login, $redirect, 403);
		} else {
			if (!empty($redirect)) {
				return new RedirectResponse($redirect);
			} else {
                return new RedirectResponse(ROOT_URL.$this->defaultRedirectUrl);
			}
		}
	}
	
	/**
	 * Logs the user out.
	 *
	 * @Action
	 */
	public function logout($redirect = null) {
		$this->userService->logoff();
		if($redirect) {
			return new RedirectResponse($redirect);
		}
		else {
            return new RedirectResponse(ROOT_URL.$this->logoutRedirectUrl);

        }
	}
	

	
}