<?php

namespace Drupal\heroku_rebuild_hook\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class HerokuRebuildHookForm extends ConfigFormBase {
    /** @var string Config settings */
    const SETTINGS = 'heroku_rebuild_hook.settings';

    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'heroku_rebuild_hook_settings';
    }

    /**
    * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
        return array(static::SETTINGS);
    }

    /**
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config(static::SETTINGS);

        $form['Github_tarball_url'] = [
            '#type' => 'textfield',
            '#description' => $this->t('URL in the form of: https://github.com/{user}/{repo}/archive/master.tar.gz'),
            '#title' => $this->t('Github Tarball URL'),
            '#default_value' => $config->get('github_tarball_url'),
        ];

        $form['Github_API_key'] = [
            '#type' => 'password',
            '#description' => $this->t('API key for Github'),
            '#title' => $this->t("Github API Key. Can be generated on the Settings page of your Github account "),
            '#default_value' => $config->get('github_API_key'),
        ];

        $form['Github_master_url'] = [
            '#type' => 'textfield',
            '#description' => $this->t('URL in the form of: https://api.github.com/repos/{user}/{repo}/commits/master'),
            '#title' => $this->t("Github API endpoint for master branch "),
            '#default_value' => $config->get('github_master_url'),
        ];

        $form['Heroku_build_url'] = [
            '#type' => 'textfield',
            '#description' => $this->t('URL in the form of: https://api.heroku.com/apps/{app-name}/builds'),
            '#title' => $this->t('Heroku Platform API Build URL'),
            '#default_value' => $config->get('heroku_build_url'),
        ];

        $form['Heroku_API_key'] = [
            '#type' => 'password',
            '#description' => $this->t('Key to your Heroku account. Can be found on the Heroku dashboard'),
            '#title' => $this->t('Heroku API Key'),
            '#default_value' => $config->get('heroku_API_key'),
        ];

        return parent::buildForm($form, $form_state);
    }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('github_tarball_url', $form_state->getValue('Github_tarball_url'))
      ->set('github_API_key', $form_state->getValue('Github_API_key'))
      ->set('github_master_url', $form_state->getValue('Github_master_url'))
      ->set('heroku_build_url', $form_state->getValue('Heroku_build_url'))
      ->set('heroku_API_key', $form_state->getValue('Heroku_API_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
