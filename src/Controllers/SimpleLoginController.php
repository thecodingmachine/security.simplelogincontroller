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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TheCodingMachine\Splash\Annotations\Get;
use TheCodingMachine\Splash\Annotations\Post;
use TheCodingMachine\Splash\Annotations\URL;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * A simple controller that provides basic login features.
 * Very useful to get quickly started, although you might want to develop your own or extend it to add custom features.
 * 
 * @author David Négrier
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
     * @param HtmlBlock                   $contentBlock
     * @param UserServiceInterface        $userService
     * @param SimpleLoginView             $simpleLoginView
     * @param string                      $defaultRedirectUrl Unless a redirecturl parameter is passed in the request, after login, the user will be redirected to this URL. It is relative to the ROOT_URL and should NOT start with a "/".

     * @param string                      $logoutRedirectUrl
     * @param string                      $ifLoggedRedirectUrl The URL to redirect if user is already logged and tries to access the login page. It is relative to the ROOT_URL and should NOT start with a "/".
     * @param array<HtmlElementInterface> $contentBeforeLoginBox
     * @param array<HtmlElementInterface> $contentAfterLoginBox
     * @param array<ActionInterface>      $actions
     * @param string|ValueInterface       $badCredentialsLabel
     * @param string                      $rootUrl
     * @param string                      $baseUrl
     */
    public function __construct(TemplateInterface $template, HtmlBlock $contentBlock, SimpleLoginView $simpleLoginView, UserServiceInterface $userService, string $rootUrl, string $baseUrl = 'login', string $defaultRedirectUrl = '', string $logoutRedirectUrl = '/',
                                string $ifLoggedRedirectUrl = '/', array $contentBeforeLoginBox = array(), array $contentAfterLoginBox = array(),
                                array $actions = array())
    {
        $this->template = $template;
        $this->userService = $userService;
        $this->defaultRedirectUrl = $defaultRedirectUrl;
        $this->logoutRedirectUrl = $logoutRedirectUrl;
        $this->ifLoggedRedirectUrl = $ifLoggedRedirectUrl;
        $this->contentBeforeLoginBox = $contentBeforeLoginBox;
        $this->contentAfterLoginBox = $contentAfterLoginBox;
        $this->simpleLoginView = $simpleLoginView;
        $this->contentBlock = $contentBlock;
        $this->actions = $actions;
        $this->rootUrl = $rootUrl;
        $this->baseUrl = $baseUrl;
    }

    /**
     * The index page will display the login form.
     *
     * @URL("{$this->baseUrl}/")
     * @Get()
     *
     * @param string $login       The login to fill by default.
     * @param string $redirecturl The URL to redirect to when login is done. If not specified, the default login URL defined in the controller will be used instead.
     */
    public function index(string $login = '', string $redirect = ''):ResponseInterface
    {
        if (!empty($redirect)) {
            $redirectUrl = $redirect;
        } else {
            $redirectUrl = null;
        }
        return $this->displayLoginPage($login, $redirectUrl);
    }

    /**
     * @param string|null       $login
     * @param UriInterface|null $redirect
     *
     * @return ResponseInterface
     */
    public function loginPage(ServerRequestInterface $request):ResponseInterface
    {
        if ($this->isJson($request)) {
            return new JsonResponse([
                "success" => false,
                "error" => "Unauthorized access. Please login."
            ]);
        }

        return $this->displayLoginPage(null, $request->getRequestTarget());
    }
    
    public function displayLoginPage(string $login = null, string $redirect = null):ResponseInterface
    {
        if ($this->userService->isLogged()) {
            if (!$redirect) {
                return new RedirectResponse($this->rootUrl.$this->ifLoggedRedirectUrl);
            } else {
                return new RedirectResponse($redirect);
            }
        }
        
        $this->simpleLoginView->setLogin($login);
        $this->simpleLoginView->setRedirecturl($redirect);
        $this->simpleLoginView->setLoginActionUrl($this->rootUrl.$this->baseUrl.'/');

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

        return new HtmlResponse($this->template);
    }

    /**
     * Logs the user in.
     *
     * @URL("{$this->baseUrl}/")
     * @Post()
     *
     * @param string $login
     * @param string $password
     * @param string $redirect
     */
    public function login(ServerRequestInterface $request, $login, $password, $redirect = null)
    {
        $result = $this->userService->login($login, $password);
        if ($result === false) {
            // Access forbidden:

            if ($this->isJson($request)) {
                $response = new JsonResponse([
                    "success" => false,
                    "error" => "Unauthorized access. Please login."
                ]);
                return $response->withStatus(401);
            }

            $this->simpleLoginView->enableBadCredentialsMessage();

            if (!empty($redirect)) {
                $redirectUrl = new Uri($redirect);
            } else {
                $redirectUrl = null;
            }

            return $this->displayLoginPage($login, $redirectUrl)->withStatus(401);
        } else {
            if ($this->isJson($request)) {
                return new JsonResponse([
                    "success" => true,
                    "message" => "You are logged as '".$login."'"
                ]);
            }

            if (!empty($redirect)) {
                return new RedirectResponse($redirect);
            } else {
                return new RedirectResponse($this->normalizeUrl($this->defaultRedirectUrl));
            }
        }
    }

    /**
     * Logs the user out.
     *
     * @URL("{$this->baseUrl}/logout")
     */
    public function logout(ServerRequestInterface $request, $redirect = null):ResponseInterface
    {
        $this->userService->logoff();

        if ($this->isJson($request)) {
            return new JsonResponse([
                "success" => true,
                "message" => "You are logged out."
            ]);
        }

        if ($redirect) {
            return new RedirectResponse($redirect);
        } else {
            return new RedirectResponse($this->normalizeUrl($this->logoutRedirectUrl));
        }
    }

    private function isJson(ServerRequestInterface $request){
        return stripos($request->getHeaderLine('Content-Type'), "application/json") === 0;
    }

    /**
     * Analyzes a URL. If it does not start with http:// or https://, let's make it relative to root_url, unless it starts with /
     *
     * @param $url
     * @return string
     */
    private function normalizeUrl($url) : string
    {
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        if (strpos($url, '/') === 0) {
            return $url;
        }
        return $this->rootUrl.$url;
    }
}
