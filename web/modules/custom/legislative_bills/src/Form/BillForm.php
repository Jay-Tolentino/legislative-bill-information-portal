<?php

namespace Drupal\legislative_bills\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BillForm extends FormBase {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId(): string {
    return 'legislative_bills_bill_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['bill_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bill Number'),
      '#description' => $this->t('Example: AB1234'),
      '#required' => TRUE,
      '#maxlength' => 32,
    ];

    $form['display_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display Number'),
      '#description' => $this->t('Example: AB 1234'),
      '#required' => TRUE,
      '#maxlength' => 32,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['author'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Author'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['chamber'] = [
      '#type' => 'select',
      '#title' => $this->t('Chamber'),
      '#options' => [
        'Assembly' => $this->t('Assembly'),
        'Senate' => $this->t('Senate'),
      ],
      '#required' => TRUE,
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        'Introduced' => $this->t('Introduced'),
        'In Committee' => $this->t('In Committee'),
        'Passed' => $this->t('Passed'),
      ],
      '#required' => TRUE,
    ];

    $form['session'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Session'),
      '#default_value' => '2025-2026',
      '#required' => TRUE,
      '#maxlength' => 32,
    ];

    $form['summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Bill'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $bill_number = strtoupper(
      preg_replace('/\s+/', '', (string) $form_state->getValue('bill_number'))
    );

    if (!preg_match('/^(AB|SB)\d+$/', $bill_number)) {
      $form_state->setErrorByName(
        'bill_number',
        $this->t('Bill number must look like AB1234 or SB123.')
      );
    }

    $existing = $this->database
      ->select('legislative_bills', 'b')
      ->fields('b', ['id'])
      ->condition('bill_number', $bill_number)
      ->execute()
      ->fetchField();

    if ($existing) {
      $form_state->setErrorByName(
        'bill_number',
        $this->t('That bill number already exists.')
      );
    }

    $form_state->setValue('bill_number', $bill_number);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->database
      ->insert('legislative_bills')
      ->fields([
        'bill_number' => $form_state->getValue('bill_number'),
        'display_number' => $form_state->getValue('display_number'),
        'title' => $form_state->getValue('title'),
        'author' => $form_state->getValue('author'),
        'chamber' => $form_state->getValue('chamber'),
        'status' => $form_state->getValue('status'),
        'session' => $form_state->getValue('session'),
        'summary' => $form_state->getValue('summary'),
      ])
      ->execute();

    $this->messenger()->addStatus(
      $this->t('Bill @bill created successfully.', [
        '@bill' => $form_state->getValue('display_number'),
      ])
    );

    $form_state->setRedirect('legislative_bills.list');
  }

}
