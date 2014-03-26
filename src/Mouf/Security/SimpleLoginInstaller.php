<?php
/*
 * Copyright (c) 2014 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */

namespace Mouf\Security;

use Mouf\Installer\PackageInstallerInterface;
use Mouf\MoufManager;
use Mouf\Actions\InstallUtils;
use Mouf\Html\Renderer\RendererUtils;

/**
 * The installer for Moufpress.
 */
class SimpleLoginInstaller implements PackageInstallerInterface {

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Installer\PackageInstallerInterface::install()
	 */
	public static function install(MoufManager $moufManager) {
		RendererUtils::createPackageRenderer($moufManager, "mouf/security.simplelogincontroller");
		
		$template = null;
		// These instances are expected to exist when the installer is run.
		if ($moufManager->has('bootstrapTemplate')) {
			$template = $moufManager->getInstanceDescriptor('bootstrapTemplate');
		}
		$userService = $moufManager->getInstanceDescriptor('userService');
		$userMessageService = $moufManager->getInstanceDescriptor('userMessageService');
		$content_block = $moufManager->getInstanceDescriptor('content.block');
		
		// Let's create the instances.
		$login = InstallUtils::getOrCreateInstance('login', 'Mouf\\Security\\Controllers\\SimpleLoginController', $moufManager);
		$anonymousSimpleLoginView = $moufManager->createInstance('Mouf\\Security\\Views\\SimpleLoginView');
		
		// Let's bind instances together.
		if (!$login->getPublicFieldProperty('template')->isValueSet() && $template) {
			$login->getPublicFieldProperty('template')->setValue($template);
		}
		if (!$login->getPublicFieldProperty('userService')->isValueSet()) {
			$login->getPublicFieldProperty('userService')->setValue($userService);
		}
		if (!$login->getPublicFieldProperty('simpleLoginView')->isValueSet()) {
			$login->getPublicFieldProperty('simpleLoginView')->setValue($anonymousSimpleLoginView);
		}
		if (!$login->getPublicFieldProperty('messageService')->isValueSet()) {
			$login->getPublicFieldProperty('messageService')->setValue($userMessageService);
		}
		if (!$login->getPublicFieldProperty('contentBlock')->isValueSet()) {
			$login->getPublicFieldProperty('contentBlock')->setValue($content_block);
		}
		if (!$login->getPublicFieldProperty('badCredentialsLabel')->isValueSet()) {
			$login->getPublicFieldProperty('badCredentialsLabel')->setValue('Sorry, your login or password seem to be incorrect');
		}
		
		// Let's rewrite the MoufComponents.php file to save the component
		$moufManager->rewriteMouf();
	}
}
