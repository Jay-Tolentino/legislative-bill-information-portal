# Legislative Bill Information Portal

A Drupal 11 and PHP web application for browsing, searching, filtering, and managing legislative bill information.

## Overview

This project was built to practice modern PHP and Drupal development in a legislative web services context.

The application provides a public-facing bill information portal, administrative bill management, PostgreSQL-backed storage, REST API endpoints, responsive styling, and automated API smoke testing.

## Features

- Drupal 11 custom module
- PHP-based controllers and forms
- PostgreSQL-backed legislative bill storage
- Bill search and filtering
- Bill detail pages
- Create, edit, and delete administrative forms
- REST API for bill data
- Individual bill API endpoints
- HTTP 404 handling for invalid bill requests
- Responsive web interface
- Automated API smoke testing
- Docker-based local development with DDEV

## Technology Stack

- PHP
- Drupal 11
- PostgreSQL
- HTML
- CSS
- REST APIs
- Docker
- DDEV
- Git

## Public Routes

### Bill Listing

`/legislation`

Supports search and filtering by chamber and status.

### Bill Detail

`/legislation/{bill_number}`

Example:

`/legislation/AB2306`

## API Endpoints

### Get All Bills

`GET /api/bills`

### Get Single Bill

`GET /api/bills/{bill_number}`

Example:

`GET /api/bills/AB2306`

### Search Bills

`GET /api/bills?search=digital`

### Filter by Chamber

`GET /api/bills?chamber=Assembly`

### Filter by Status

`GET /api/bills?status=Introduced`

## Administrative Features

Authenticated administrators can:

- create legislative bills
- edit existing bill information
- delete bills
- manage structured legislative data

Example create route:

`/admin/legislation/add`

## Local Development

The project uses DDEV for its containerized local environment.

Start the project:

`ddev start`

Clear Drupal cache:

`ddev drush cr`

Stop the project:

`ddev stop`

## Automated Testing

Run the API smoke tests:

`./tests/api_smoke_test.sh`

The script verifies:

- bill collection endpoint
- individual bill endpoint
- chamber filtering
- keyword searching
- HTTP 404 behavior

## Project Structure

```text
web/modules/custom/legislative_bills/
├── css/
│   └── legislative_bills.css
├── src/
│   ├── Controller/
│   │   └── LegislativeBillsController.php
│   └── Form/
│       ├── BillForm.php
│       ├── BillEditForm.php
│       └── BillDeleteForm.php
├── legislative_bills.info.yml
├── legislative_bills.install
├── legislative_bills.libraries.yml
└── legislative_bills.routing.yml
'''

## Purpose

This project demonstrates exposure to:

- PHP web development
- Drupal custom module development
- REST API development
- database-backed web applications
- CRUD operations
- form handling and validation
- responsive web development
- testing and documentation

## Notes

The legislative records currently included in the project are sample data used for development and demonstration purposes.
