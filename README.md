# Enterprise Cross-Platform E-Commerce BI System

An end-to-end data engineering pipeline and real-time Business Intelligence dashboard. This system bridges an operational transactional storefront built on **MySQL (OLTP)** to a structured analytical data warehouse hosted on **Microsoft SQL Server (OLAP)** via an automated **Python ETL Pipeline**, complete with a self-refreshing data visualization container.

## 📊 Architecture Ecosystem Blueprint

The enterprise data workflow follows a strict decoupled operational-to-analytical progression pattern:

[Storefront App (PHP/HTML)] 
       │
       ▼ (Real-time Writes)
┌──────────────────────────────────────┐
│  MySQL Database (ecommerce_oltp)    │ ◄─── Operational Layer (OLTP)
└──────────────────────────────────────┘
       │
       ▼ (Scheduled/Triggered Batch Extract)
┌──────────────────────────────────────┐
│  Python ETL Pipeline (Pandas Engine) │ ◄─── Transformation & Aggregation Layer
└──────────────────────────────────────┘
       │
       ▼ (Bulk Loading & Upsert/Merge)
┌──────────────────────────────────────┐
│  MSSQL Data Warehouse (ecommerce_olap)│ ◄─── Analytical Layer (OLAP Star Schema)
└──────────────────────────────────────┘
       │
       ▼ (Direct Live DB Reads)
┌──────────────────────────────────────┐
│ Plotly Dash App (analytics_dashboard)│ ◄─── Business Intelligence View Layer
└──────────────────────────────────────┘

---

## 🛠️ Tech Stack & Systems Infrastructure

* **Operational Front-End Layer:** PHP 8.x, Apache Server (XAMPP Framework), HTML5, CSS3 Media-Layouts.
* **Operational DBMS Engine (OLTP):** MySQL (InnoDB Storage Engine, optimized for high concurrency transactional operations).
* **Pipeline & Middleware Layer:** Python 3.x, Pandas DataFrame Processor, SQLAlchemy Object-Relational Model Core, `pyodbc` database gateway connectivity interface.
* **Data Warehouse Architecture (OLAP):** Microsoft SQL Server (MSSQL Named Local Instance), organized using an enterprise Star Schema model.
* **Business Intelligence Rendering Engine:** Plotly Dash Web Core Application framework, Flask Micro-server runner.

---

## 💾 Database Schema Topology

### 1. Operational Layer (`ecommerce_oltp` - MySQL)
Highly normalized design focused on structural integrity and rapidly processing individual user interactions:
* `users`: Stores client profile classifications (`id`, `name`, `email`, `password`, `role`).
* `categories`: Groups products into operational departments (`id`, `name`).
* `products`: Stores items inventory status, cost thresholds, and relational mappings.
* `cart`: Volatile temporary cache tracking active sessions user-item selections.
* `orders` / `order_items`: Permanent point-in-time snapshot records locking transaction totals, historical checkout item prices, and volume counts.

### 2. Analytical Warehouse Layer (`ecommerce_olap` - MSSQL Star Schema)
De-normalized architecture built explicitly to run fast aggregations over massive data loads:
* **`dim_products` (Dimension Table):** Holds master attributes of tracked retail goods (`product_id`, `name`, `price`, `category_name`).
* **`fact_sales` (Fact Table):** Houses numeric operations metrics for business evaluation, tracking operational foreign keys alongside analytical measures: `order_id`, `product_id`, `quantity_purchased`, `total_sales_volume`, and `transaction_timestamp`.

---

## 🚀 Execution Lifecycle Protocol

Follow these setup steps to run the complete data pipeline sequence locally:

### 1. Clone & Set Up Directory Architecture
Ensure your local project directory inside your web server root path matches this workspace layout:
```text
ecommerce-bi/
│
├── config/
│   └── database.php              # Shared connection parameters for the OLTP backend
├── public/
│   ├── admin/                    # Store administration catalog controls
│   └── assets/css/style.css      # Shared storefront styling profiles
├── src/
│   ├── analytics_dashboard.py    # Plotly BI visualization application core
│   ├── etl_pipeline.py           # Automated Python data synchronization pipeline
│   ├── checkout-handler.php      # Transaction recording module
│   └── product-handler.php       # Administrative catalog controller
└── .gitignore
