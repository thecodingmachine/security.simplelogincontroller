Simple login controller
=======================

What is this package?
---------------------
This package is part of [Mouf](http://mouf-php.com) login system.
It provides a simple [Splash](http://mouf-php.com/packages/mouf/mvc.splash/index.md) controller that displays
a simple login page.

The controller is using the [UserService](http://mouf-php.com/packages/mouf/security.userservice/README.md) system
to log the user in your application.

If you are using [Mouf](http://mouf-php.com), [Splash MVC](http://mouf-php.com/packages/mouf/mvc.splash/index.md)
(or a Splash compatible system like [Druplash](http://mouf-php.com/packages/mouf/integration.drupal.druplash/README.md)
 or [Moufpress](http://mouf-php.com/packages/mouf/integration.wordpress.moufpress/README.md)) 
and the [UserService](http://mouf-php.com/packages/mouf/security.userservice/README.md), you can use this controller to get a login page for free :)

Installation
------------
Start by adding the package to your dependencies:

```
{
	"require" : {
		"mouf/security.simplelogincontroller" : "^6.0"
	}
}
```

Run `php composer.phar update`, then install the package using Mouf UI.

The install process will create a `login` instance representing the controller.

How to use it?
--------------
Easy! Just point your browser to `http://[server]/[app]/login/`.
You should see a login screen.

<div class="alert">Be sure not to forget the trailing / in the URL. It is really important for the
controller to work correctly.</div>

In order to logout, use the `http://[server]/[app]/login/logout` URL.

How to customize?
-----------------
###Customizing labels
You can customize this instance as you like. In particular, you can edit the attached `SimpleLoginView` object
that will let you edit each piece of text displayed on the login screen.

###Customizing design
If you need more control on the design of the login screen, the `SimpleLoginView` is using 
[Mouf's rendering system](http://mouf-php.com/packages/mouf/html.renderer/README.md).
Therefore, you can override the whole design of the page in your application.

To do this, simply copy the file `/vendor/mouf/security.simplelogincontroller/src/templates/Mouf/Security/Views/SimpleLoginView.twig`
into '/src/templates/Mouf/Security/Views/SimpleLoginView.twig'. Here you can create your own version of the view.
Do not forget to purge your cache in Mouf UI after copying the files for your new template file to be detected.

Alternatively, if you only need to display a few HTML elements before or after the login box, you can use the
`contentBeforeLoginBox` and `contentAfterLoginBox` properties of the `login` instance. 

###Customizing behaviour
You can register a number of [actions](http://mouf-php.com/packages/mouf/utils.action.action-interface/README.md)
that will be performed before displaying the view by adding those actions in the `actions` property
of the `login` instance. 

###Customizing URL
By default the URL of the login screen if `/login/`.
This is actually the name of the controller's instance. If you want to change this URL, you just have to change the
name of the `SimpleLoginContoller` instance in Mouf UI.
