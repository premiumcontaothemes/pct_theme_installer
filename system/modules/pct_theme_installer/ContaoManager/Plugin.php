<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2018 Leo Feyer
 *
 * @copyright Tim Gatzky 2018, Premium Contao Themes
 * @author  Tim Gatzky <info@tim-gatzky.de>
 * @package  pct_theme_installer
 */
 

namespace Contao\PCT\ThemeInstaller\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 * */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoCalendarBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['calendar']),
        ];
    }
}
