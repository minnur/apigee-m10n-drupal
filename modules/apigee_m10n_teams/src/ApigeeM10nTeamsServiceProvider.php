<?php

namespace Drupal\apigee_m10n_teams;

use Drupal\apigee_m10n_teams\Entity\ParamConverter\TeamSubscriptionConverter;
use Drupal\apigee_m10n_teams\Entity\Storage\Controller\TeamAcceptedRatePlanSdkControllerProxy;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Responsible for overriding `apigee_m10n` services.
 */
class ApigeeM10nTeamsServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('apigee_m10n.sdk_controller_proxy.subscription')) {
      $container->getDefinition('apigee_m10n.sdk_controller_proxy.subscription')
        ->setClass(TeamAcceptedRatePlanSdkControllerProxy::class);
    }
    if ($container->hasDefinition('apigee_m10n.sdk_controller_factory')) {
      $container->getDefinition('apigee_m10n.sdk_controller_factory')
        ->setClass(TeamSdkControllerFactory::class);
    }
    if ($container->hasDefinition('paramconverter.entity.subscription')) {
      $container->getDefinition('paramconverter.entity.subscription')
        ->setClass(TeamSubscriptionConverter::class);
    }
  }

}
