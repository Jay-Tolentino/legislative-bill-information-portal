<?php

namespace Drupal\legislative_bills\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BillDeleteForm extends ConfirmFormBase {

  protected Connection $database;

  protected string $billNumber = '';

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId(): string {
    return 'legislative_bills_delete_form';
  }

  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    ?string $bill_number = NULL
  ): array {
    $this->billNumber = $bill_number ?? '';

    $form['bill_number'] = [
      '#type' => 'hidden',
      '#value' => $this->billNumber,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function getQuestion() {
    return $this->t(
      'Are you sure you want to delete @bill?',
      ['@bill' => $this->billNumber]
    );
  }

  public function getCancelUrl(): Url {
    return Url::fromRoute('legislative_bills.list');
  }

  public function getConfirmText() {
    return $this->t('Delete Bill');
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $bill_number = (string) $form_state->getValue('bill_number');

    $deleted = $this->database
      ->delete('legislative_bills')
      ->condition('bill_number', $bill_number)
      ->execute();

    if ($deleted) {
      $this->messenger()->addStatus(
        $this->t('Bill @bill deleted successfully.', [
          '@bill' => $bill_number,
        ])
      );
    }
    else {
      $this->messenger()->addWarning(
        $this->t('No bill was deleted.')
      );
    }

    $form_state->setRedirect('legislative_bills.list');
  }

}
