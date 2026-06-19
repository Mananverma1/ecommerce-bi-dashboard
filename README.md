# Enterprise Cross-Platform E-Commerce BI System

An end-to-end data engineering pipeline and real-time Business Intelligence dashboard. This system bridges an operational transactional storefront built on **MySQL (OLTP)** to a structured analytical data warehouse hosted on **Microsoft SQL Server (OLAP)** via an automated **Python ETL Pipeline**, complete with a self-refreshing data visualization container.

---

## 📊 Architecture Ecosystem Blueprint

The enterprise data workflow follows a strict decoupled operational-to-analytical progression pattern:

```mermaid
graph TD
    %% Styling Definitions
    classDef operational fill:#1e293b,stroke:#3b82f6,stroke-width:2px,color:#f8fafc;
    classDef pipeline fill:#1e293b,stroke:#10b981,stroke-width:2px,color:#f8fafc;
    classDef warehouse fill:#1e293b,stroke:#8b5cf6,stroke-width:2px,color:#f8fafc;
    classDef view fill:#1e293b,stroke:#f59e0b,stroke-width:2px,color:#f8fafc;

    %% Workflow Nodes
    A[Storefront Web App<br>PHP / HTML5 / CSS3] -->|Real-time Writes| B[(MySQL Production DB<br>ecommerce_oltp)]
    B -->|Scheduled Batch Extract| C[Python ETL Pipeline<br>Pandas Data Transformation Engine]
    C -->|Bulk Loading & Upsert/Merge| D[(MSSQL Data Warehouse<br>ecommerce_olap - Star Schema)]
    D -->|Direct Live DB Reads| E[Plotly Dash Interface<br>Business Intelligence Application]

    %% Assigning Classes
    class A,B operational;
    class C pipeline;
    class D warehouse;
    class E view;
