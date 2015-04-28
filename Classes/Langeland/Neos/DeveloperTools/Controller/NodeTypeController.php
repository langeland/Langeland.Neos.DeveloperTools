<?php
namespace Langeland\Neos\DeveloperTools\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Langeland.Neos.DeveloperTools".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class NodeTypeController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

	/**
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 * @Flow\Inject
	 */
	protected $nodeTypeManager;

	/**
	 * @return void
	 */
	public function indexAction() {
		$nodeTypesGroups = array(
			'document' => array(
				'name' => 'Document Node Type',
				'nodeTypes' => array()
			),
			'content' => array(
				'name' => 'Content Node Type',
				'nodeTypes' => array()
			),
			'other' => array(
				'name' => 'Other',
				'nodeTypes' => array()
			),
		);

		/** @var \TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType */
		foreach ($this->nodeTypeManager->getNodeTypes() as $nodeType) {
			if ($nodeType->getName() === 'unstructured') {
				continue;
			}

			if ($nodeType->isOfType('TYPO3.Neos:Document')) {
				$type = 'document';
			} elseif ($nodeType->isOfType('TYPO3.Neos:Content')) {
				$type = 'content';
			} else {
				$type = 'other';
			}

			$nodeTypesGroups[$type]['nodeTypes'][$nodeType->getName()] = $nodeType;






		}

		$this->view->assign('nodeTypesGroups', $nodeTypesGroups);
	}

	/**
	 * @param string $nodeTypeName
	 * @return void
	 */
	public function showAction($nodeTypeName) {
		$node = $this->nodeTypeManager->getNodeType($nodeTypeName);
		$this->view->assign('nodeTypeKey', $nodeTypeName);
		$this->view->assign('node', $node);
	}
}

?>