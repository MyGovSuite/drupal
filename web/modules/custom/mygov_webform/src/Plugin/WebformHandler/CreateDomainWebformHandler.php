<?php

namespace Drupal\mygov_webform\Plugin\WebformHandler;

use Drupal\domain_access\DomainAccessManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Create a new node entity from a webform submission.
 *
 * @WebformHandler(
 *   id = "Create a domain",
 *   label = @Translation("Create a domain"),
 *   category = @Translation("Action"),
 *   description = @Translation("Creates a new domain from Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class CreateDomainWebformHandler extends WebformHandlerBase {

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

    // Validate if the same hostname exists.
    $existing =  $domain_storage->loadByProperties(['hostname' => $hostname]);
    $existing = reset($existing);

    // Add domain if not exists.
    $messenger = \Drupal::messenger();
    if ($existing) {
      $messenger->addError('The hostname is already registered.', $messenger::TYPE_ERROR);
    }
    else {
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

      // Add domain access to current user for new site.
      // Skip for administrator.
      if ($current_user !== '1') {
        $user_entity = \Drupal\user\Entity\User::load($current_user);
        $id = $domain->id();
        $user_domains = \Drupal::service('domain_access.manager')->getAccessValues($user_entity);
        $user_domains[$id] = $id;
        $user_entity->set(DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD, array_keys($user_domains));
        $user_entity->save();
      }

      // Creates a new Front Page node.
      $front_page = Node::create(array(
        'type' => 'front_page',
        'title' => $values['name'],
        'langcode' => 'en',
        'uid' => $current_user,
        'status' => 1,
      ));

      $front_page->save();

      // Create a configuration to set the Front Page as the new domain.
      $domain_config = \Drupal::service('config.factory')->getEditable('domain.config.' . $values['hostname'] . '.system.site')->setData(array(
        'name' => $values['name'],
        'page' => array(
          'front' => $front_page->id(),
        ),
      ));

      $domain_config->save();

      $messenger->addMessage('Your site has been created!', $messenger::TYPE_STATUS);
    }
  }
}

