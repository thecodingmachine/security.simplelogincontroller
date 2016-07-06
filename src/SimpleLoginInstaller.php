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
class SimpleLoginInstaller implements PackageInstallerInterface
{
    /**
     * (non-PHPdoc).
     *
     * @see \Mouf\Installer\PackageInstallerInterface::install()
     */
    public static function install(MoufManager $moufManager)
    {
        RendererUtils::createPackageRenderer($moufManager, 'mouf/security.simplelogincontroller');

        $moufManager = MoufManager::getMoufManager();

        // These instances are expected to exist when the installer is run.
        $bootstrapTemplate = $moufManager->getInstanceDescriptor('bootstrapTemplate');
        $userService = $moufManager->getInstanceDescriptor('userService');
        $userMessageService = $moufManager->getInstanceDescriptor('userMessageService');
        $block_content = $moufManager->getInstanceDescriptor('block.content');

        // Let's create the instances.
        $simpleLoginView = InstallUtils::getOrCreateInstance('simpleLoginView', 'Mouf\\Security\\Views\\SimpleLoginView', $moufManager);
        $simpleLoginController = InstallUtils::getOrCreateInstance('simpleLoginController', 'Mouf\\Security\\Controllers\\SimpleLoginController', $moufManager);

        // Let's bind instances together.
        if (!$simpleLoginController->getConstructorArgumentProperty('template')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('template')->setValue($bootstrapTemplate);
        }
        if (!$simpleLoginController->getConstructorArgumentProperty('contentBlock')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('contentBlock')->setValue($block_content);
        }
        if (!$simpleLoginController->getConstructorArgumentProperty('simpleLoginView')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('simpleLoginView')->setValue($simpleLoginView);
        }
        if (!$simpleLoginController->getConstructorArgumentProperty('userService')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('userService')->setValue($userService);
        }
        if (!$simpleLoginController->getConstructorArgumentProperty('rootUrl')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('rootUrl')->setValue('return ROOT_URL;');
            $simpleLoginController->getConstructorArgumentProperty('rootUrl')->setOrigin('php');
        }
        if (!$simpleLoginController->getConstructorArgumentProperty('baseUrl')->isValueSet()) {
            $simpleLoginController->getConstructorArgumentProperty('baseUrl')->setValue('login');
        }

// Let's rewrite the MoufComponents.php file to save the component
        $moufManager->rewriteMouf();
    }
}
