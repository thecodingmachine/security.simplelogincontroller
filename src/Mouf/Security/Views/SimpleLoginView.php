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
class SimpleLoginView {
	use Renderable;
	
	public $login;
	
	public $redirecturl;
}