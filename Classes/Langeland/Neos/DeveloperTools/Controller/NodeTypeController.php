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

			$nodeTypeConfiguration = $nodeType->getFullConfiguration();

			list($packageName, $shortName) = explode(':', $nodeType->getName());
			if ($nodeType->getLabel() == '') {
				$label = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $shortName);
			} else {
				$label = $nodeType->getLabel();
			}

			$nodeTypesGroups[$type]['nodeTypes'][$nodeType->getName()] = array(
				'packageName' => $packageName,
				'icon' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.icon') ?: 'icon-file',
				'label' => $label,
				'name' => $nodeType->getName(),
				'group' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.group'),
				'abstract' => $nodeType->isAbstract(),
				'inlineEditable' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.inlineEditable'),
				'shortName' => $shortName,
				//'superTypes' => $this->getSuperTypeConfiguration($nodeType),
				//'chidrenTypes' => $this->getChildrenTypeConfiguration($nodeType),
				//'childNodes' => $this->getChildNodesConfiguration($nodeType),
				//'properties' => $this->getPropertiesConfiguration($nodeType)
			);
		}

		$this->view->assign('nodeTypesGroups', $nodeTypesGroups);
	}

	/**
	 * @param string $nodeTypeName
	 * @return void
	 */
	public function showAction($nodeTypeName) {
		$nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName);

		if ($nodeType->getLabel() == '') {
			$shortNameParts = explode(':', $nodeType->getName());
			$label = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $shortNameParts[1]);
		} else {
			$label = $nodeType->getLabel();
		}
		$this->setTitle($label);
		$nodeTypeConfiguration = $nodeType->getFullConfiguration();

		$this->view->assignMultiple(array(
			'nodeTypeKey' => $nodeTypeName,
			'nodeType' => $nodeType,
			'label' => $label,
			'icon' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.icon') ?: 'icon-file',
			'group' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.group'),

			'superTypes' => $this->getSuperTypeConfiguration($nodeType),
			'chidrenTypes' => $this->getChildrenTypeConfiguration($nodeType),
			//'childNodes' => $this->getChildNodesConfiguration($nodeType),
			//'properties' => $this->getPropertiesConfiguration($nodeType)
		));
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType
	 * @return array
	 */
	protected function getSuperTypeConfiguration(\TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType) {
		$configuration = array();
		foreach ($nodeType->getDeclaredSuperTypes() as $superType) {
			/** @var \TYPO3\TYPO3CR\Domain\Model\NodeType $superType */
			$configuration[$superType->getName()] = $this->getNodeTypeDescriptor($superType);
		}

		return $configuration;
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType
	 * @return array
	 */
	protected function getChildNodesConfiguration(\TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType) {
		$configuration = array();
		$localConfiguration = $nodeType->getLocalConfiguration();
		foreach ($nodeType->getAutoCreatedChildNodes() as $childNodeName => $childNode) {
			/** @var NodeType $childNode */
			list($packageName, $shortName) = explode(':', $childNode->getName());
			$nodeTypeConfiguration = $childNode->getFullConfiguration();
			$configuration[$childNodeName] = array(
				'packageName' => $packageName,
				'name' => $childNode->getName(),
				'shortName' => $shortName,
				'icon' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.icon') ?: 'icon-file',
				'inherited' => \TYPO3\Flow\Utility\Arrays::getValueByPath($localConfiguration, array('childNodes', $childNodeName)) === NULL,
			);
		}
		return $configuration;
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType
	 * @return array
	 */
	protected function getChildrenTypeConfiguration(\TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType) {
		$configuration = array();
		foreach ($this->nodeTypeManager->getSubNodeTypes($nodeType->getName()) as $superType) {
			/** @var NodeType $superType */
			list($packageName, $shortName) = explode(':', $superType->getName());
			$configuration[$shortName . ':' . $packageName] = $this->getNodeTypeDescriptor($superType);
		}

		ksort($configuration);

		return $configuration;
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType
	 * @return array
	 */
	protected function getNodeTypeDescriptor(\TYPO3\TYPO3CR\Domain\Model\NodeType $nodeType) {
		list($packageName, $shortName) = explode(':', $nodeType->getName());
		$nodeTypeConfiguration = $nodeType->getFullConfiguration();
		return array(
			'packageName' => $packageName,
			'icon' => \TYPO3\Flow\Utility\Arrays::getValueByPath($nodeTypeConfiguration, 'ui.icon') ?: 'icon-file',
			'name' => $nodeType->getName(),
			'shortName' => $shortName
		);
	}

}

?>