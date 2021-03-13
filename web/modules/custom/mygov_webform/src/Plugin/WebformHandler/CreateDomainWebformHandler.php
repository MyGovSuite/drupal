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

    $hostname = $list['state'] . '.' . $list['county'] . '.' . 'mygovcms.com';
    $hostname = str_replace(' ', '_', $hostname);

  }
}
