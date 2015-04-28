<?php
namespace Langeland\Neos\DeveloperTools\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Langeland.Neos.DeveloperTools".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Langeland\Neos\DeveloperTools\Domain\Model\Configuration;

class StyleController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

	/**
	 * @return void
	 */
	public function indexAction() {

		$this->view->assign('data', 'data');
	}

}

?>