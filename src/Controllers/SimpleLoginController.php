<?php

namespace Mouf\Security\Controllers;

use Mouf\Mvc\Splash\HtmlResponse;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Security\Views\SimpleLoginView;
use Mouf\Html\Widgets\MessageService\Service\SessionMessageService;
use Mouf\Html\Widgets\MessageService\Service\UserMessageInterface;
use Mouf\Utils\Value\ValueInterface;
use Mouf\Utils\Value\ValueUtils;
use Mouf\Security\UserService\UserServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

/**
 * A simple controller that provides basic login features.
 * Very useful to get quickly started, although you might want to develop your own or extend it to add custom features.
 * 
 * @author David
 * @Component
 */
class SimpleLoginController implements LoginController
{
    /**
     * The template to use to display.
     * 
     * @Property
     * @Compulsory
     *
     * @var TemplateInterface
     */
    private $template;

    /**
     * The user service.
     *
     * @Property
     * @Compulsory
     *
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * Unless a redirecturl parameter is passed in the request, after login, the user will be redirected to this URL.
     * It is relative to the ROOT_URL and should NOT start with a "/".
     * 
     * @Property
     *
     * @var string
     */
    private $defaultRedirectUrl;

    /**
     * The URL to redirect to on logout.
     * It is relative to the ROOT_URL and should NOT start with a "/".
     * 
     * @Property
     *
     * @var string
     */
    private $logoutRedirectUrl;

    /**
     * The URL to redirect if user is already logged and tries to access the login page.
     * It is relative to the ROOT_URL and should NOT start with a "/".
     * 
     * @Property
     *
     * @var string
     */
    private $ifLoggedRedirectUrl;

    /**
     * The HTML elements that will be displayed before the login box.
     *
     * @Property
     *
     * @var array<HtmlElementInterface>
     */
    private $contentBeforeLoginBox;

    /**
     * The HTML elements that will be displayed after the login box.
     *
     * @Property
     *
     * @var array<HtmlElementInterface>
     */
    private $contentAfterLoginBox;

    /**
     * The view object for the login screen.
     * 
     * @var SimpleLoginView
     */
    private $simpleLoginView;

    /**
     * The service used to display the authentication error message.
     *
     * @var SessionMessageService
     */
    private $messageService;

    /**
     * The content block the template will be writting into.
     *
     * @Property
     * @Compulsory
     *
     * @var HtmlBlock
     */
    private $contentBlock;

    /**
     * Actions to be performed before displaying the view.
     *
     * @var array<ActionInterface>
     */
    private $actions = array();

    /**
     * The label for the error message if login credentials are wrong.
     *
     * @Property
     *
     * @var string|ValueInterface
     */
    private $badCredentialsLabel;

    /**
     * @var string
     */
    private $rootUrl;

    /**
     * @var string
     */
    private $baseUrl = 'login';

    /**
     * SimpleLoginController constructor.
     *
     * @param TemplateInterface           $template
     * @param UserServiceInterface        $userService
     * @param string                      $defaultRedirectUrl
     * @param string                      $logoutRedirectUrl
     * @param string                      $ifLoggedRedirectUrl
     * @param array<HtmlElementInterface> $contentBeforeLoginBox
     * @param array<HtmlElementInterface> $contentAfterLoginBox
     * @param SimpleLoginView             $simpleLoginView
     * @param SessionMessageService       $messageService
     * @param HtmlBlock                   $contentBlock
     * @param array<ActionInterface>      $actions
     * @param string|ValueInterface       $badCredentialsLabel
     * @param string                      $rootUrl
     * @param string                      $baseUrl
     */
    public function __construct(TemplateInterface $template, UserServiceInterface $userService, string $defaultRedirectUrl, string $logoutRedirectUrl,
                                string $ifLoggedRedirectUrl, array $contentBeforeLoginBox, array $contentAfterLoginBox, SimpleLoginView $simpleLoginView,
                                SessionMessageService $messageService, HtmlBlock $contentBlock, array $actions, $badCredentialsLabel, string $rootUrl, string $baseUrl)
    {
        $this->template = $template;
        $this->userService = $userService;
        $this->defaultRedirectUrl = $defaultRedirectUrl;
        $this->logoutRedirectUrl = $logoutRedirectUrl;
        $this->ifLoggedRedirectUrl = $ifLoggedRedirectUrl;
        $this->contentBeforeLoginBox = $contentBeforeLoginBox;
        $this->contentAfterLoginBox = $contentAfterLoginBox;
        $this->simpleLoginView = $simpleLoginView;
        $this->messageService = $messageService;
        $this->contentBlock = $contentBlock;
        $this->actions = $actions;
        $this->badCredentialsLabel = $badCredentialsLabel;
        $this->rootUrl = $rootUrl;
        $this->baseUrl = $baseUrl;
    }

    /**
     * The index page will display the login form.
     *
     * @URL("{$this->baseUrl}/")
     *
     * @param string $login       The login to fill by default.
     * @param string $redirecturl The URL to redirect to when login is done. If not specified, the default login URL defined in the controller will be used instead.
     */
    public function index(string $login, string $redirect):ResponseInterface
    {
        $this->loginPage($login, new Uri($redirect));
    }

    /**
     * @param string|null       $login
     * @param UriInterface|null $redirect
     *
     * @return ResponseInterface
     */
    public function loginPage(string $login = null, UriInterface $redirect = null):ResponseInterface
    {
        if ($this->userService->isLogged()) {
            if (!$redirect) {
                return new RedirectResponse($this->rootUrl.$this->ifLoggedRedirectUrl);
            } else {
                return new RedirectResponse($redirect);
            }
        }
        $responseCode = 200;
        if ($redirect) {
            $responseCode = 401;
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
     *
     * @param string $login
     * @param string $password
     * @param string $redirect
     */
    public function login($login, $password, $redirect = null)
    {
        $result = $this->userService->login($login, $password);
        if ($result == false) {
            // Access forbidden:

            $this->messageService->setMessage(ValueUtils::val($this->badCredentialsLabel), UserMessageInterface::ERROR);

            return $this->loginPage($login, $this->rootUrl.$this->defaultRedirectUrl);
        } else {
            if (!empty($redirect)) {
                return new RedirectResponse($redirect);
            } else {
                return new RedirectResponse($this->rootUrl.$this->defaultRedirectUrl);
            }
        }
    }

    /**
     * Logs the user out.
     *
     * @Action
     */
    public function logout($redirect = null):ResponseInterface
    {
        $this->userService->logoff();
        if ($redirect) {
            return new RedirectResponse($redirect);
        } else {
            return new RedirectResponse($this->rootUrl.$this->logoutRedirectUrl);
        }
    }
}
