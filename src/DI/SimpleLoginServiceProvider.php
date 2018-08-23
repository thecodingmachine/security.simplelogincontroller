<?php
namespace Mouf\Security\DI;


use Interop\Container\Factories\Alias;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Security\Controllers\SimpleLoginController;
use Mouf\Security\UserService\UserServiceInterface;
use Mouf\Security\Views\SimpleLoginView;
use Psr\Container\ContainerInterface;
use TheCodingMachine\Funky\Annotations\Extension;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\ServiceProvider;

class SimpleLoginServiceProvider extends ServiceProvider
{
    /**
     * @Factory()
     */
    public static function createSimpleLoginController(ContainerInterface $container): SimpleLoginController
    {
        return new SimpleLoginController($container->get('simpleLoginControllerTemplate'),
            $container->get('block.content'),
            $container->get(SimpleLoginView::class),
            $container->get(UserServiceInterface::class),
            $container->get('ROOT_URL')
            );
    }

    /**
     * simpleLoginControllerTemplate is a alias of the default template.
     *
     * @Factory(name="simpleLoginControllerTemplate")
     */
    public static function createSimpleLoginControllerTemplate(TemplateInterface $template): TemplateInterface
    {
        return $template;
    }

    /**
     * @Factory()
     */
    public static function createSimpleLoginView(ContainerInterface $container): SimpleLoginView
    {
        return new SimpleLoginView();
    }

    /**
     * @Extension(name="thecodingmachine.splash.controllers")
     */
    public static function extendControllers(array $controllers): array
    {
        $controllers[] = SimpleLoginController::class;
        return $controllers;
    }
}
