# The Brewing Bar — POS and Inventory Management System

A Laravel-based point-of-sale and inventory management system for The Brewing Bar. This README explains how to clone the repository and run the project locally.

## Getting Started

### Requirements

Install the following before setting up the project:

- Git
- PHP 8.2 or compatible with the project
- Composer
- Node.js and npm
- MySQL (XAMPP can be used on Windows)

### 1. Clone the GitHub repository

Open Git Bash, PowerShell, or a terminal and run:

```bash
git clone git@github.com:lzaragosa555459-eng/ProjectTBB.git
```

If you have not configured an SSH key for GitHub, you can use HTTPS instead:

```bash
git clone https://github.com/lzaragosa555459-eng/ProjectTBB.git
```

Enter the project folder:

```bash
cd ProjectTBB
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Create your environment file

Copy the example environment file:

**Windows PowerShell**

```powershell
Copy-Item .env.example .env
```

**Git Bash / macOS / Linux**

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 4. Create and configure the database

1. Start MySQL using XAMPP or your installed MySQL service.
2. Create a database named `thebrewingbar` (or choose another name).
3. Open the `.env` file and update the database settings to match your local MySQL configuration. For a typical local XAMPP setup, the values are:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thebrewingbar
DB_USERNAME=root
DB_PASSWORD=
```

If your MySQL account has a password or uses a different port, enter those values instead.

### 5. Run database migrations

```bash
php artisan migrate --seed
```

This creates the database tables and runs the seeders included in the repository. If you only want to create the tables without seed data, use:

```bash
php artisan migrate
```

### 6. Install frontend dependencies

```bash
npm install
```

For a production-style compiled frontend build:

```bash
npm run build
```

During development, you can instead keep Vite running in a separate terminal:

```bash
npm run dev
```

### 7. Start the Laravel development server

In another terminal, run:

```bash
php artisan serve
```

Open the local URL shown in the terminal (usually `http://127.0.0.1:8000`) in your browser.

## Common Setup Notes

- Do not commit your `.env` file or share its database credentials. It contains local environment settings and secrets.
- If you pull new changes that include dependency updates, run `composer install` and/or `npm install` again as needed.
- If you encounter cached configuration issues after editing `.env`, run:

```bash
php artisan config:clear
```

- If migrations fail because tables already exist, check whether you are using an existing database before trying to rerun or reset migrations. Avoid `php artisan migrate:fresh` on any database containing data you need; it drops all tables.

## Project Design and Documentation

## Database ERD

```mermaid
erDiagram
    ROLES ||--o{ USERS : assigns
    CATEGORIES ||--o{ INVENTORY_ITEMS : categorizes
    INVENTORY_LOCATIONS ||--o{ INVENTORY_ITEMS : stores
    UNITS o|--o{ INVENTORY_ITEMS : measures

    INVENTORY_ITEMS ||--o{ INVENTORY_ITEM_OPTION_GROUPS : has
    OPTION_GROUPS ||--o{ INVENTORY_ITEM_OPTION_GROUPS : assigned_to
    OPTION_GROUPS ||--o{ OPTION_VALUES : contains

    ORDERS ||--|{ ORDER_ITEMS : contains
    INVENTORY_ITEMS ||--o{ ORDER_ITEMS : ordered_as
    ORDER_ITEMS ||--o| KITCHEN_ORDER_ITEMS : prepared_as
    ORDER_ITEMS ||--o{ ORDER_ITEM_OPTIONS : has
    OPTION_VALUES ||--o{ ORDER_ITEM_OPTIONS : selected_as

    USERS ||--o{ ORDERS : processes
    ORDERS ||--o| PAYMENTS : paid_by
    USERS ||--o{ PAYMENTS : receives

    SUPPLIERS ||--o{ PURCHASES : supplies
    PURCHASES ||--o{ PURCHASE_ITEMS : contains
    INVENTORY_ITEMS ||--o{ PURCHASE_ITEMS : purchased_item
    STOCK_INS ||--o{ PURCHASE_ITEMS : recorded_in

    INVENTORY_ITEMS ||--o{ STOCK_INS : stocked_in
    INVENTORY_ITEMS ||--o{ STOCK_OUTS : stocked_out
    SUPPLIERS o|--o{ STOCK_INS : supplied_by
    USERS ||--o{ STOCK_INS : records
    USERS ||--o{ STOCK_OUTS : records

    PURCHASES o|--o{ EXPENSES : associated_with

    USERS ||--o{ PROFILE_REQUESTS : submits
    USERS o|--o{ PROFILE_REQUESTS : reviews

    CATEGORIES {
        bigint id PK
        varchar name UK
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    INVENTORY_LOCATIONS {
        bigint id PK
        varchar name UK
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    UNITS {
        bigint id PK
        varchar name UK
        varchar abbreviation UK
        timestamp created_at
        timestamp updated_at
    }

    INVENTORY_ITEMS {
        bigint id PK
        varchar name
        bigint category_id FK
        bigint inventory_location_id FK
        bigint unit_id FK
        varchar inventory_type
        decimal price
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    INVENTORY_ITEM_OPTION_GROUPS {
        bigint id PK
        bigint inventory_item_id FK
        bigint option_group_id FK
        boolean is_required
    }

    OPTION_GROUPS {
        bigint id PK
        varchar name UK
        text description
    }

    OPTION_VALUES {
        bigint id PK
        bigint option_group_id FK
        varchar name
        decimal price_adjustment
        boolean is_active
    }

    ORDERS {
        bigint id PK
        bigint cashier_id FK
        varchar order_number UK
        date queue_date
        int queue_number
        varchar order_type
        varchar status
        decimal subtotal
        decimal discount_amount
        varchar discount_type
        decimal total_amount
        timestamp ordered_at
        timestamp completed_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint inventory_item_id FK
        int quantity
        decimal unit_price
        decimal subtotal
        text notes
    }

    ORDER_ITEM_OPTIONS {
        bigint id PK
        bigint order_item_id FK
        bigint option_value_id FK
    }

    KITCHEN_ORDER_ITEMS {
        bigint id PK
        bigint order_item_id FK
        bigint prepared_by FK
        varchar status
        timestamp started_at
        timestamp completed_at
    }

    PAYMENTS {
        bigint id PK
        bigint order_id FK
        bigint received_by FK
        varchar payment_method
        decimal amount
        decimal amount_received
        decimal change_amount
        varchar reference_number
        varchar proof_path
        timestamp paid_at
    }

    SUPPLIERS {
        bigint id PK
        varchar name
        varchar contact_person
        varchar phone
        varchar email
        text address
        boolean is_active
    }

    PURCHASES {
        bigint id PK
        bigint supplier_id FK
        date purchase_date
        varchar reference_number
        text notes
    }

    PURCHASE_ITEMS {
        bigint id PK
        bigint purchase_id FK
        bigint stock_in_id FK
        bigint inventory_item_id FK
        decimal quantity
        decimal unit_cost
        decimal subtotal
    }

    STOCK_INS {
        bigint id PK
        bigint inventory_item_id FK
        decimal quantity
        bigint supplier_id FK
        bigint recorded_by FK
        datetime recorded_at
        varchar remarks
    }

    STOCK_OUTS {
        bigint id PK
        bigint inventory_item_id FK
        decimal quantity
        bigint recorded_by FK
        varchar reason
        datetime recorded_at
        varchar remarks
    }

    EXPENSES {
        bigint id PK
        bigint purchase_id FK
        varchar description
        varchar category
        decimal amount
        date expense_date
        text notes
    }

    ROLES {
        bigint id PK
        varchar name UK
        varchar description
    }

    USERS {
        bigint id PK
        bigint role_id FK
        varchar name
        varchar email UK
        timestamp email_verified_at
        timestamp deleted_at
    }

    PROFILE_REQUESTS {
        bigint id PK
        bigint user_id FK
        varchar type
        varchar name
        varchar email
        text reason
        varchar status
        bigint reviewed_by FK
        timestamp reviewed_at
        text review_note
    }
```

## System Process

```mermaid
flowchart TD

    A[Supplier] --> B[Stock In]
    B --> C[Inventory]
    C --> C1[Bar Area / Kitchen Area Stock]

    C --> D{Inventory Level}
    D -->|Low Stock| E[Manager Reviews Stock]
    E --> F[Restock]

    G[Customer] --> H[Cashier Creates Order]

    H --> I[Select Menu Item]
    I --> J[Select Options]

    J --> J1[Size]
    J --> J2[Temperature]
    J --> J3[Flavor]

    J1 --> K[Calculate Order Price]
    J2 --> K
    J3 --> K

    K --> L[Apply Discount if Applicable]
    L --> M[Process Payment]

    M --> M1[Cash]
    M --> M2[GCash]

    M1 --> N[Order Confirmed]
    M2 --> N

    N --> O[Send Order to Kitchen / Bar]

    O --> P[Cook Receives Order]
    P --> Q[Prepare Order]
    Q --> R[Update Order Status]
    R --> S[Order Ready]

    S --> T[Calculate Recipe Usage]

    T --> T1[Base Recipe]
    T --> T2[Option Adjustments]

    T1 --> U[Calculate Ingredients]
    T2 --> U

    U --> V[Deduct Inventory]
    V --> W[Record Inventory Transaction]
    W --> C

    N --> Z{Prepped Food?}
    Z -->|Yes| Z1[Deduct 1 Serving / Unit]
    Z1 --> W

    N --> X[Record Sale]
    X --> Y[Dashboard / Sales Summary]

    C --> Y
```

## UI DESIGN

[View the current UI interface in Figma](https://www.figma.com/design/gMAAZ9Zmgib7U5i35lr7Ni/The-Brewing-Bar-Wirefram?node-id=0-1&t=m43ypRmgkDD7Qbh2-1)
