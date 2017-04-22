<?php

/*
 *  Copyright notice
 *
 *  (c) 2015 Markus Blaschke <typo3@markus-blaschke.de> (metaseo)
 *  (c) 2013 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de> (tq_seo)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Metaseo\Metaseo\Hook\TCA;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * TCA Hook: Robots.txt default content
 */
class RobotsTxtDefault
{

    /**
     * TYPO3 Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * TYPO3 configuration manager
     *
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * TYPO3 Content object renderer
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Render default Robots.txt from TypoScript Setup
     *
     * @param  array $data TCE Information array
     *
     * @return string
     */
    public function main(array $data)
    {
        // ############################
        // Init
        // ############################

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager objectManager */
        $this->objectManager = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
        );

        /** @var ConfigurationManager configurationManager */
        $this->configurationManager = $this->objectManager->get(
            'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager'
        );

        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer cObj */
        $this->cObj = $this->objectManager->get('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');

        // ############################
        // Init TSFE
        // ############################
        $rootPageId = $data['row']['pid'];

        // Disables the time tracker to speed up TypoScript execution
        /** @var \TYPO3\CMS\Core\TimeTracker\TimeTracker $timeTracker */
        $timeTracker = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker', false);

        $GLOBALS['TT'] = $timeTracker;
        $GLOBALS['TT']->start();

        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $tsfeController */
        $tsfeController = $this->objectManager->get(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TYPO3_CONF_VARS'],
            $rootPageId,
            0
        );

        $GLOBALS['TSFE'] = $tsfeController;

        //allow dynamically robots.txt to be dynamically scripted via TS
        if ($GLOBALS['TSFE']->sys_page == null) {
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
            $GLOBALS['TSFE']->initTemplate();
        }

        // ############################
        // Render default robots.txt content
        // ############################

        // Fetch TypoScript setup
        $tsSetup = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'metaseo',
            'plugin'
        );

        $content = '';
        if (!empty($tsSetup['plugin.']['metaseo.']['robotsTxt.'])) {
            $content = $this->cObj->cObjGetSingle(
                $tsSetup['plugin.']['metaseo.']['robotsTxt.']['default'],
                $tsSetup['plugin.']['metaseo.']['robotsTxt.']['default.']
            );
        }

        $content = htmlspecialchars($content);
        $content = nl2br($content);

        return $content;
    }

    /**
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected function getPageRenderer()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Page\\PageRenderer');
    }
}
