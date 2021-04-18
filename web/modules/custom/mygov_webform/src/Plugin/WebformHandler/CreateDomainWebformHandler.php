<?php

namespace Drupal\mygov_webform\Plugin\WebformHandler;

use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
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
        $list['state'] = $this->convertState($term->label());
      }
    }

    $host = \Drupal::request()->getHost();
    $domain_name = $list['state'] . '.' . $list['county'] . '.' . $host;
    $domain_name = str_replace(' ', '-', $domain_name);

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

      $url = Url::fromUri('http://' . $hostname);
      $link = Link::fromTextAndUrl($values['name'], $url)->toString();
      $text = new TranslatableMarkup("Your site has been created! Visit the page here - @link", ["@link" => $link]);
      $messenger->addMessage($text, $messenger::TYPE_STATUS);
    }
  }

  private function convertState($name) {
    $states = array(
      array('name'=>'Alabama', 'abbr'=>'AL'),
      array('name'=>'Alaska', 'abbr'=>'AK'),
      array('name'=>'Arizona', 'abbr'=>'AZ'),
      array('name'=>'Arkansas', 'abbr'=>'AR'),
      array('name'=>'California', 'abbr'=>'CA'),
      array('name'=>'Colorado', 'abbr'=>'CO'),
      array('name'=>'Connecticut', 'abbr'=>'CT'),
      array('name'=>'Delaware', 'abbr'=>'DE'),
      array('name'=>'Florida', 'abbr'=>'FL'),
      array('name'=>'Georgia', 'abbr'=>'GA'),
      array('name'=>'Hawaii', 'abbr'=>'HI'),
      array('name'=>'Idaho', 'abbr'=>'ID'),
      array('name'=>'Illinois', 'abbr'=>'IL'),
      array('name'=>'Indiana', 'abbr'=>'IN'),
      array('name'=>'Iowa', 'abbr'=>'IA'),
      array('name'=>'Kansas', 'abbr'=>'KS'),
      array('name'=>'Kentucky', 'abbr'=>'KY'),
      array('name'=>'Louisiana', 'abbr'=>'LA'),
      array('name'=>'Maine', 'abbr'=>'ME'),
      array('name'=>'Maryland', 'abbr'=>'MD'),
      array('name'=>'Massachusetts', 'abbr'=>'MA'),
      array('name'=>'Michigan', 'abbr'=>'MI'),
      array('name'=>'Minnesota', 'abbr'=>'MN'),
      array('name'=>'Mississippi', 'abbr'=>'MS'),
      array('name'=>'Missouri', 'abbr'=>'MO'),
      array('name'=>'Montana', 'abbr'=>'MT'),
      array('name'=>'Nebraska', 'abbr'=>'NE'),
      array('name'=>'Nevada', 'abbr'=>'NV'),
      array('name'=>'New Hampshire', 'abbr'=>'NH'),
      array('name'=>'New Jersey', 'abbr'=>'NJ'),
      array('name'=>'New Mexico', 'abbr'=>'NM'),
      array('name'=>'New York', 'abbr'=>'NY'),
      array('name'=>'North Carolina', 'abbr'=>'NC'),
      array('name'=>'North Dakota', 'abbr'=>'ND'),
      array('name'=>'Ohio', 'abbr'=>'OH'),
      array('name'=>'Oklahoma', 'abbr'=>'OK'),
      array('name'=>'Oregon', 'abbr'=>'OR'),
      array('name'=>'Pennsylvania', 'abbr'=>'PA'),
      array('name'=>'Rhode Island', 'abbr'=>'RI'),
      array('name'=>'South Carolina', 'abbr'=>'SC'),
      array('name'=>'South Dakota', 'abbr'=>'SD'),
      array('name'=>'Tennessee', 'abbr'=>'TN'),
      array('name'=>'Texas', 'abbr'=>'TX'),
      array('name'=>'Utah', 'abbr'=>'UT'),
      array('name'=>'Vermont', 'abbr'=>'VT'),
      array('name'=>'Virginia', 'abbr'=>'VA'),
      array('name'=>'Washington', 'abbr'=>'WA'),
      array('name'=>'West Virginia', 'abbr'=>'WV'),
      array('name'=>'Wisconsin', 'abbr'=>'WI'),
      array('name'=>'Wyoming', 'abbr'=>'WY'),
    );

    $return = false;
    $strlen = strlen($name);

    foreach ($states as $state) :
      if ($strlen < 2) {
        return false;
      } else if ($strlen == 2) {
        if (strtolower($state['abbr']) == strtolower($name)) {
          $return = $state['name'];
          break;
        }
      } else {
        if (strtolower($state['name']) == strtolower($name)) {
          $return = strtoupper($state['abbr']);
          break;
        }
      }
    endforeach;

    return $return;
  }
}

