<?php

namespace Drupal\mygov_webform\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Create a new node entity from a webform submission.
 *
 * @WebformHandler(
 *   id = "Create a domain",
 *   label = @Translation("Create a domain"),
 *   category = @Translation("Action"),
 *   description = @Translation("Creates a new node from Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class CreateDomainWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */

  // Function to be fired after submitting the Webform.
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();

    // Get the user who submitted the form.
    $current_user = \Drupal::currentUser()->id();

    // Load parents of taxonomy term.
    $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($values['county_name']);
    $list = [];
    foreach ($ancestors as $term) {
      if ($term->id() === $values['county_name']) {
        $list['county'] = $term->label();
      }
      else {
        $list['state'] = $term->label();
      }
    }

    $host = \Drupal::request()->getHost();
    $domain_name = $list['state'] . '.' . $list['county'] . '.' . $host;
    $domain_name = str_replace(' ', '_', $domain_name);
    // Remove repetitive county term imported from csv.
    $domain_name = str_ireplace('_county', '', $domain_name);

    /** @var \Drupal\domain\DomainStorageInterface $domain_storage */
    $domain_storage = \Drupal::entityTypeManager()->getStorage('domain');
    $records_count = $domain_storage->getQuery()->count()->execute();
    $start_weight = $records_count + 1;
    $hostname = mb_strtolower($domain_name);
    $values = [
      'hostname' => $hostname,
      'name' => $list['state'] . ' - ' . $values['site_name'],
      'status' => 1,
      'scheme' => 'http',
      'weight' => $start_weight + 1,
      'is_default' => 0,
      'id' => $domain_storage->createMachineName($hostname),
      'validate_url' => 0,
    ];
    $domain = $domain_storage->create($values);
    $domain->save();
  }
}

