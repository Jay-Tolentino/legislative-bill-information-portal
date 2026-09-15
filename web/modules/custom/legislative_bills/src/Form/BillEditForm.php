<?php

namespace Drupal\legislative_bills\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BillEditForm extends FormBase {

  protected Connection $database;

  protected array $bill = [];

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId(): string {
    return 'legislative_bills_edit_form';
  }

  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    ?string $bill_number = NULL
  ): array {
    $bill = $this->database
      ->select('legislative_bills', 'b')
      ->fields('b')
      ->condition('bill_number', $bill_number)
      ->execute()
      ->fetchAssoc();

    if (!$bill) {
      $form['message'] = [
        '#markup' => '<p>Bill not found.</p>',
      ];

      return $form;
    }

    $this->bill = $bill;

    $form['bill_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bill Number'),
      '#default_value' => $bill['bill_number'],
      '#disabled' => TRUE,
    ];

    $form['display_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display Number'),
      '#default_value' => $bill['display_number'],
      '#required' => TRUE,
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $bill['title'],
      '#required' => TRUE,
    ];

    $form['author'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Author'),
      '#default_value' => $bill['author'],
      '#required' => TRUE,
    ];

    $form['chamber'] = [
      '#type' => 'select',
      '#title' => $this->t('Chamber'),
      '#options' => [
        'Assembly' => $this->t('Assembly'),
        'Senate' => $this->t('Senate'),
      ],
      '#default_value' => $bill['chamber'],
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
      '#default_value' => $bill['status'],
      '#required' => TRUE,
    ];

    $form['session'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Session'),
      '#default_value' => $bill['session'],
      '#required' => TRUE,
    ];

    $form['summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#default_value' => $bill['summary'],
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Changes'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->database
      ->update('legislative_bills')
      ->fields([
        'display_number' => $form_state->getValue('display_number'),
        'title' => $form_state->getValue('title'),
        'author' => $form_state->getValue('author'),
        'chamber' => $form_state->getValue('chamber'),
        'status' => $form_state->getValue('status'),
        'session' => $form_state->getValue('session'),
        'summary' => $form_state->getValue('summary'),
      ])
      ->condition('bill_number', $this->bill['bill_number'])
      ->execute();

    $this->messenger()->addStatus(
      $this->t('Bill updated successfully.')
    );

    $form_state->setRedirect('legislative_bills.list');
  }

}
