<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */


namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\EnvironmentConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ProjectConfiguration;

/**
 * @internal
 */
class ProjectConfigurationDataMapper implements ProjectConfigurationDataMapperInterface
{
    /**
     * @var ShopConfigurationDataMapperInterface
     */
    private $shopConfigurationDataMapper;

    /**
     * ProjectConfigurationDataMapper constructor.
     * @param ShopConfigurationDataMapperInterface $shopConfigurationDataMapper
     */
    public function __construct(ShopConfigurationDataMapperInterface $shopConfigurationDataMapper)
    {
        $this->shopConfigurationDataMapper = $shopConfigurationDataMapper;
    }

    /**
     * @param ProjectConfiguration $configuration
     * @return array
     */
    public function toData(ProjectConfiguration $configuration): array
    {
        return [
            'environments' => $this->getEnvironmentConfigurationsData($configuration),
        ];
    }

    /**
     * @param array $data
     * @return ProjectConfiguration
     */
    public function fromData(array $data): ProjectConfiguration
    {
        $projectConfiguration = new ProjectConfiguration();
        $this->setEnvironmentConfigurations($projectConfiguration, $data['environments']);

        return $projectConfiguration;
    }

    /**
     * @param ProjectConfiguration $projectConfiguration
     * @param array                $environmentsData
     */
    private function setEnvironmentConfigurations(
        ProjectConfiguration $projectConfiguration,
        array $environmentsData
    ) : void {
        foreach ($environmentsData as $environmentName => $environmentData) {
            $environmentConfiguration = new EnvironmentConfiguration();

            if (isset($environmentData['shops'])) {
                $this->setShopsConfiguration($environmentConfiguration, $environmentData['shops']);
            }

            $projectConfiguration->addEnvironmentConfiguration(
                $environmentName,
                $environmentConfiguration
            );
        }
    }

    /**
     * @param EnvironmentConfiguration $environmentConfiguration
     * @param array                    $shopsData
     */
    private function setShopsConfiguration(EnvironmentConfiguration $environmentConfiguration, array $shopsData) : void
    {
        foreach ($shopsData as $shopId => $shopData) {
            $environmentConfiguration->addShopConfiguration(
                $shopId,
                $this->shopConfigurationDataMapper->fromData($shopData)
            );
        }
    }

    /**
     * @param ProjectConfiguration $configuration
     * @return array
     */
    private function getEnvironmentConfigurationsData(ProjectConfiguration $configuration): array
    {
        $data = [];

        foreach ($configuration->getEnvironmentConfigurations() as $environmentName => $environmentConfiguration) {
            $data[$environmentName]['shops'] = $this->getShopsConfigurationData($environmentConfiguration);
        }

        return $data;
    }

    /**
     * @param EnvironmentConfiguration $environmentConfiguration
     * @return array
     */
    private function getShopsConfigurationData(EnvironmentConfiguration $environmentConfiguration): array
    {
        $data = [];

        foreach ($environmentConfiguration->getShopConfigurations() as $shopId => $shopConfiguration) {
            $data[$shopId] = $this->shopConfigurationDataMapper->toData($shopConfiguration);
        }

        return $data;
    }
}
