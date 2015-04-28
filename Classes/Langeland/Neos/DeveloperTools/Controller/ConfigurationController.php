<?php
namespace Langeland\Neos\DeveloperTools\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Langeland.Neos.DeveloperTools".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Langeland\Neos\DeveloperTools\Domain\Model\Configuration;

class ConfigurationController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @param string $type Configuration type to show
	 * @param string $path path to subconfiguration separated by "." like "TYPO3.Flow"
	 * @return void
	 */
	public function indexAction($type = NULL, $path = NULL) {
		$availableConfigurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
		$this->view->assign('availableConfigurationTypes', $availableConfigurationTypes);
		$this->view->assign('type', $type);

		if (in_array($type, $availableConfigurationTypes)) {
			$configuration = $this->configurationManager->getConfiguration($type);
			if ($path !== NULL) {
				$configuration = \TYPO3\Flow\Utility\Arrays::getValueByPath($configuration, $path);
			}

			$typeAndPath = $type . ($path ? ': ' . $path : '');
			$this->view->assign('typeAndPath', $typeAndPath);

			if ($configuration !== NULL) {
				$this->view->assign('configuration', $configuration);
				$this->view->assign('yaml', \Symfony\Component\Yaml\Yaml::dump($configuration, 99, 2));
			}
		}
	}

}

?>