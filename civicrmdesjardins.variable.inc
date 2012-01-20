<?php

/**
 * @file
 * Hooks provided by the Variable module.
 */

/**
 * Implements hook_variable_info().
 */
function civicrmdesjardins_variable_info($options) {
  $variables['civicrmdesjardins_orgname'] = array(
    'title' => t('Organisation name'),
    'description' => t('Organisation name for CiviCRM Desjardins receipts.'),
    'localize' => TRUE,
    'group' => 'civicrmdesjardins',
  );

  $variables['civicrmdesjardins_tos_text'] = array(
    'title' => t('Terms of service text'),
    'description' => t('Terms of service text on CiviCRM Desjardins receipts.'),
    'localize' => TRUE,
    'group' => 'civicrmdesjardins',
  );

  $variables['civicrmdesjardins_tos_url'] = array(
    'title' => t('Complete URL for the terms of service'),
    'description' => t('Complete URL for the terms of service, shown in the CiviCRM Desjardins receipts.'),
    'localize' => TRUE,
    'group' => 'civicrmdesjardins',
  );

  return $variables;
}

/**
 * Implements hook_variable_group_info().
 */
function civicrmdesjardins_variable_group_info() {
  $groups['civicrmdesjardins'] = array(
    'title' => t('CiviCRM desjardins receipt settings'),
    'access' => 'administer CiviCRM',
  );

  return $groups;
}


