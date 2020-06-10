<?php

namespace Drupal\aspambot\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aspambot.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $countries = CountryManager::getStandardList();
    $form['#attached']['library'][] = 'aspambot/aspambot';

    // Country filter.
    $form['message'] = [
      '#markup' => t('These filters will be automatically applied to <b>all</b> forms on the website.'),
    ];
    $form['cnt_countries'] = [
      '#type' => 'details',
      '#title' => t('Countries Filter'),
      '#open' => TRUE,
    ];
    $form['cnt_countries']['countries'] = [
      '#type' => 'select',
      '#title' => 'Selected countries',
      '#multiple' => TRUE,
      '#empty_value' => 'none',
      '#empty_option' => 'None',
      '#options' => $countries,
      '#default_value' => $this->config('aspambot.settings')->get('countries'),

    ];
    $form['cnt_countries']['reverse'] = [
      '#type' => 'checkbox',
      '#title' => 'Use as whitelist',
      '#default_value' => $this->config('aspambot.settings')->get('reverse'),
      '#description' => t('If selected, the list will be used as a whitelist rather than a blacklist.'),
    ];

    // AbuseIPDB key.
    $form['cnt_abuseipdb'] = [
      '#type' => 'details',
      '#title' => t('AbuseIPDB'),
      '#open' => TRUE,
    ];
    $form['cnt_abuseipdb']['abuseipdb_key'] = [
      '#type' => 'textfield',
      '#title' => 'Key',
      '#description' => t('If specified, will enable using the IPDB service.'),
      '#default_value' => $this->config('aspambot.settings')->get('abuseipdb_key'),

    ];

    // Submit.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('aspambot.settings');
    $values = $form_state->getValues();
    $countries = $values['countries'] ?? [];
    $reverse = $values['reverse'] ?? 0;
    $abuseipdb_key = $values['abuseipdb_key'] ?? '';
    $config->set('countries', $countries)->save();
    $config->set('reverse', $reverse)->save();
    $config->set('abuseipdb_key', $abuseipdb_key)->save();
  }

}
