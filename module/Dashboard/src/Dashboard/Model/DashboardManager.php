<?php
/**
 * Main class responsible for rtm widgets management
 *
 * @author Konrad Turczynski <konrad.turczynski@schibsted.pl>
 */
namespace Dashboard\Model;

use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class DashboardManager {
    /**
     * Zend service locator
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator;
    /**
     * Rtm custom config
     *
     * @var array
     */
    private $rtmConfig = array();

    /**
     * Collection of dashboard's widget
     *
     * @var array
     */
    private $widgetsCollection = array();

    /**
     * Constructor
     *
     * @param string                  $rtmConfigName  Config name retrieved from URL.
     * @param ServiceLocatorInterface $serviceLocator Interface for retrieving services.
     * @internal param array $configName Dashboard's config
     */
    public function __construct($rtmConfigName, ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        $this->loadConfig($rtmConfigName);
        $this->init();
    }

    /**
     * Loads configuration array from rtm config file
     *
     * @param string $configName Config file name retrieved from url
     * @throws \Exception
     */
    public function loadConfig($configName) {
        $configFilePath = 'config/rtm/' . $configName . '.config.php';

        if (file_exists($configFilePath)) {
            $this->rtmConfig = include($configFilePath);
        } else {
            throw new \Exception('Cannot find config file');
        }
    }

    /**
     * Creates dashboard's widget collection based on the custom config file
     */
    public function init() {
        $widgetFactory = $this->getServiceLocator()->get('WidgetFactory');

        foreach ($this->rtmConfig['widgets'] as $widgetData) {

            $daoParams = null;
            if (isset($this->rtmConfig[$widgetData['params']['dao']])) {
                $daoParams = $this->rtmConfig[$widgetData['params']['dao']];
            }

            $widget = $widgetFactory->build($widgetData, $daoParams);
            $this->addWidget($widget);
        }
    }

    /**
     * Adds widget to widget collection
     *
     * @param Widget\AbstractWidget $widget Concrete widget object
     */
    public function addWidget(Widget\AbstractWidget $widget) {
        $this->widgetsCollection[$widget->getId()] = $widget;
    }

    /**
     * Returns concrete instance of the widget with the given identifier
     *
     * @param string $id Widget's id
     * @throws \Exception
     * @return mixed
     */
    public function getWidget($id) {
        if (isset($this->widgetsCollection[$id])) {
            return $this->widgetsCollection[$id];
        } else {
            throw new \Exception('Widget with ' . $id . ' id is not specified in rtm config');
        }
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
}
