<?php

namespace Drupal\legislative_bills\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LegislativeBillsController extends ControllerBase {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  private function getBills(): array {
    $query = $this->database
      ->select('legislative_bills', 'b')
      ->fields('b')
      ->orderBy('bill_number', 'ASC');

    $results = $query->execute()->fetchAllAssoc('bill_number');

    $bills = [];

    foreach ($results as $bill) {
      $bills[] = (array) $bill;
    }

    return $bills;
  }

  public function list(Request $request): array {
    $search = trim((string) $request->query->get('search', ''));
    $chamber = trim((string) $request->query->get('chamber', ''));
    $status = trim((string) $request->query->get('status', ''));

    $bills = $this->filterBills(
      $this->getBills(),
      $search,
      $chamber,
      $status
    );

    $rows = [];

    foreach ($bills as $bill) {
      $rows[] = [
        [
          'data' => [
            '#type' => 'link',
            '#title' => $bill['display_number'],
            '#url' => Url::fromRoute(
              'legislative_bills.detail',
              ['bill_number' => $bill['bill_number']]
            ),
          ],
        ],
        $bill['title'],
        $bill['author'],
        $bill['chamber'],
        $bill['status'],
        $bill['session'],
        [
          'data' => [
            '#type' => 'container',
            'edit' => [
              '#type' => 'link',
              '#title' => $this->t('Edit'),
              '#url' => Url::fromRoute(
                'legislative_bills.edit',
                ['bill_number' => $bill['bill_number']]
              ),
            ],
            'separator' => [
              '#markup' => ' | ',
            ],
            'delete' => [
              '#type' => 'link',
              '#title' => $this->t('Delete'),
              '#url' => Url::fromRoute(
                'legislative_bills.delete',
                ['bill_number' => $bill['bill_number']]
              ),
            ],
          ],
        ],
      ];
    }

    return [
      '#attached' => [
        'library' => [
          'legislative_bills/portal',
        ],
      ],

      '#prefix' => '<div class="legislative-portal">',
      '#suffix' => '</div>',

      'intro' => [
        '#markup' => '
          <div class="legislative-intro">
            <h2>Legislative Bill Information Portal</h2>
            <p>Search and review legislative bill information by chamber, status, title, or author.</p>
          </div>
        ',
      ],

      'filters' => [
        '#type' => 'inline_template',
        '#template' => '
          <form method="get" class="legislative-filters">
            <label>
              Search
              <input
                type="text"
                name="search"
                value="{{ search }}"
                placeholder="Bill number, title, author..."
              >
            </label>

            <label>
              Chamber
              <select name="chamber">
                <option value="">All</option>
                <option value="Assembly" {{ chamber == "Assembly" ? "selected" : "" }}>Assembly</option>
                <option value="Senate" {{ chamber == "Senate" ? "selected" : "" }}>Senate</option>
              </select>
            </label>

            <label>
              Status
              <select name="status">
                <option value="">All</option>
                <option value="Introduced" {{ status == "Introduced" ? "selected" : "" }}>Introduced</option>
                <option value="In Committee" {{ status == "In Committee" ? "selected" : "" }}>In Committee</option>
                <option value="Passed" {{ status == "Passed" ? "selected" : "" }}>Passed</option>
              </select>
            </label>

            <button type="submit">Filter Bills</button>
            <a href="{{ reset_url }}">Reset</a>
          </form>
        ',
        '#context' => [
          'search' => $search,
          'chamber' => $chamber,
          'status' => $status,
          'reset_url' => Url::fromRoute('legislative_bills.list')->toString(),
        ],
      ],

      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Bill'),
          $this->t('Title'),
          $this->t('Author'),
          $this->t('Chamber'),
          $this->t('Status'),
          $this->t('Session'),
          $this->t('Actions'),
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No legislative bills found.'),
      ],
    ];
  }

  public function detail(string $bill_number): array {
    $bill = $this->database
      ->select('legislative_bills', 'b')
      ->fields('b')
      ->condition('bill_number', $bill_number)
      ->execute()
      ->fetchAssoc();

    if (!$bill) {
      return [
        '#markup' => '<p>Bill not found.</p>',
      ];
    }

    return [
      '#attached' => [
        'library' => [
          'legislative_bills/portal',
        ],
      ],

      '#prefix' => '<div class="bill-detail-card">',
      '#suffix' => '</div>',

      'bill_number' => [
        '#markup' => '<h2>' . $bill['display_number'] . '</h2>',
      ],

      'title' => [
        '#markup' => '<p><strong>Title:</strong> ' . $bill['title'] . '</p>',
      ],

      'author' => [
        '#markup' => '<p><strong>Author:</strong> ' . $bill['author'] . '</p>',
      ],

      'chamber' => [
        '#markup' => '<p><strong>Chamber:</strong> ' . $bill['chamber'] . '</p>',
      ],

      'status' => [
        '#markup' => '<p><strong>Status:</strong> ' . $bill['status'] . '</p>',
      ],

      'session' => [
        '#markup' => '<p><strong>Session:</strong> ' . $bill['session'] . '</p>',
      ],

      'summary' => [
        '#markup' => '<p><strong>Summary:</strong> ' . $bill['summary'] . '</p>',
      ],

      'actions' => [
        '#prefix' => '<div class="bill-actions">',
        '#suffix' => '</div>',

        'back' => [
          '#type' => 'link',
          '#title' => $this->t('Back to legislation'),
          '#url' => Url::fromRoute('legislative_bills.list'),
        ],
      ],
    ];
  }

  public function api(Request $request): JsonResponse {
    $search = trim((string) $request->query->get('search', ''));
    $chamber = trim((string) $request->query->get('chamber', ''));
    $status = trim((string) $request->query->get('status', ''));

    $bills = $this->filterBills(
      $this->getBills(),
      $search,
      $chamber,
      $status
    );

    return new JsonResponse([
      'count' => count($bills),
      'data' => array_values($bills),
    ]);
  }

  public function apiDetail(string $bill_number): JsonResponse {
    $bill = $this->database
      ->select('legislative_bills', 'b')
      ->fields('b')
      ->condition('bill_number', $bill_number)
      ->execute()
      ->fetchAssoc();

    if (!$bill) {
      return new JsonResponse([
        'error' => 'Bill not found.',
      ], 404);
    }

    return new JsonResponse([
      'data' => $bill,
    ]);
  }

  private function filterBills(
    array $bills,
    string $search,
    string $chamber,
    string $status
  ): array {
    return array_filter(
      $bills,
      function (array $bill) use ($search, $chamber, $status): bool {
        if ($search !== '') {
          $haystack = strtolower(
            $bill['display_number'] . ' ' .
            $bill['title'] . ' ' .
            $bill['author'] . ' ' .
            $bill['summary']
          );

          if (!str_contains($haystack, strtolower($search))) {
            return false;
          }
        }

        if ($chamber !== '' && $bill['chamber'] !== $chamber) {
          return false;
        }

        if ($status !== '' && $bill['status'] !== $status) {
          return false;
        }

        return true;
      }
    );
  }

}
