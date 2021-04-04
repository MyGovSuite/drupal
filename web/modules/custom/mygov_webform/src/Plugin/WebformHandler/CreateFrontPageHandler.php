<?php

namespace Drupal\mygov_webform\Plugin\WebformHandler;

use Drupal\Core\Entity\Entity;
use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Creates a new Front Page node.
 *
 * @WebformHandler(
 *   id = "Create a Front Page",
 *   label = @Translation("Create a Front Page"),
 *   category = @Translation("Action"),
 *   description = @Translation("Creates a new Front Page node."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class CreateFrontPageHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */

  // Function to be fired after submitting the Webform.
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();

    // Get the user who submitted the form.
    $current_user = \Drupal::currentUser()->id();

    $create_node = Node::create(array(
      'type' => 'front_page',
      'title' => $values['site_name'],
      'langcode' => 'en',
      'uid' => $current_user,
      'status' => 1,
    ));

    $create_node->save();

  }
}

