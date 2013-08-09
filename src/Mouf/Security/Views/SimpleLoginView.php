<?php
namespace Mouf\Security\Views;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\HtmlElement\HtmlElementInterface;

use Mouf\Security\UserService\UserService;

use Mouf\Html\Template\TemplateInterface;

use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Renderer\Renderable;



/**
 * The view for the login screen.
 * 
 * @author David
 * @Component
 */
class SimpleLoginView implements HtmlElementInterface {
	use Renderable;
	
	/**
	 * The label for the "login" field.
	 *
	 * @Property
	 * @var string|ValueInterface
	 */
	public $loginLabel = "Login";
	
	/**
	 * The label for the "password" field.
	 *
	 * @Property
	 * @var string|ValueInterface
	 */
	public $passwordLabel = "Password";
	
	/**
	 * The label for the "login" submit button.
	 *
	 * @Property
	 * @var string|ValueInterface
	 */
	public $loginSubmitLabel = "Login";
	
	/**
	 * The label for the error message if login credentials are wrong.
	 *
	 * @Property
	 * @var string|ValueInterface
	 */
	public $badCredentialsLabel = "Invalid login or password, please try again.";
	
	public $login;
	
	public $redirecturl;
}