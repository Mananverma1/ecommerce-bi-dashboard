# src/etl_pipeline.py
import mysql.connector  # Still used to read from your local web app source
import pyodbc           # NEW: Used to write to your destination MSSQL instance
import pandas as pd
from datetime import datetime

# Source configuration (XAMPP MySQL)
OLTP_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'ecommerce_oltp'
}

# Destination connection string (MSSQL Local Instance)
MSSQL_CONNECTION_STRING = (
    "DRIVER={ODBC Driver 17 for SQL Server};"
    "SERVER=MANANVERMA\\MSSQL;"
    "DATABASE=ecommerce_olap;"
    "Trusted_Connection=yes;"  # Uses your active Windows account credentials
)

def run_analytics_etl():
    print(f"[{datetime.now()}] Initializing Cross-Platform MySQL to MSSQL ETL Pipeline...")
    
    try:
        # Connect to source and destination
        oltp_conn = mysql.connector.connect(**OLTP_CONFIG)
        mssql_conn = pyodbc.connect(MSSQL_CONNECTION_STRING)
        mssql_cursor = mssql_conn.cursor()

        # ==========================================
        # 1. EXTRACT STEP (From MySQL)
        # ==========================================
        print("Extracting transactional elements from MySQL cluster...")
        
        df_users = pd.read_sql("SELECT id, name, email, role, created_at FROM users", oltp_conn)
        
        prod_query = """
            SELECT p.id, p.name, c.name as category_name, p.price 
            FROM products p 
            JOIN categories c ON p.category_id = c.id
        """
        df_products = pd.read_sql(prod_query, oltp_conn)
        
        # PATCHED: Swapped 'oi.price_at_purchase' out for your real 'oi.price' schema column
        sales_query = """
            SELECT o.id as order_id, o.user_id, oi.product_id, oi.quantity, 
                   (oi.quantity * oi.price) as total_volume, o.created_at
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.status != 'cancelled'
        """
        df_sales = pd.read_sql(sales_query, oltp_conn)

        # ==========================================
        # 2. TRANSFORM STEP (Using Pandas)
        # ==========================================
        print("Transforming structural records into warehouse-ready formats...")
        df_users['name'] = df_users['name'].str.title()
        df_users['email'] = df_users['email'].str.lower()
        df_products['name'] = df_products['name'].str.strip()

        # Convert date column formats cleanly into standard strings for seamless MSSQL parser ingestion
        df_users['created_at'] = df_users['created_at'].astype(str)
        df_sales['created_at'] = df_sales['created_at'].astype(str)

        # ==========================================
        # 3. LOAD STEP (Into MSSQL)
        # ==========================================
        print("Loading sync records into local MSSQL database tables...")

        # Sync Users Dimension into MSSQL (Using native '?' positional syntax markers)
        for _, row in df_users.iterrows():
            mssql_cursor.execute("""
                MERGE dim_users AS target
                USING (SELECT ? AS user_id) AS source
                ON (target.user_id = source.user_id)
                WHEN MATCHED THEN
                    UPDATE SET name = ?, email = ?, role = ?
                WHEN NOT MATCHED THEN
                    INSERT (user_id, name, email, role, account_created_at)
                    VALUES (?, ?, ?, ?, ?);
            """, (row['id'], row['name'], row['email'], row['role'], 
                  row['id'], row['name'], row['email'], row['role'], row['created_at']))

        # Sync Products Dimension into MSSQL
        for _, row in df_products.iterrows():
            mssql_cursor.execute("""
                MERGE dim_products AS target
                USING (SELECT ? AS product_id) AS source
                ON (target.product_id = source.product_id)
                WHEN MATCHED THEN
                    UPDATE SET name = ?, category_name = ?, unit_price = ?
                WHEN NOT MATCHED THEN
                    INSERT (product_id, name, category_name, unit_price)
                    VALUES (?, ?, ?, ?);
            """, (row['id'], row['name'], row['category_name'], row['price'],
                  row['id'], row['name'], row['category_name'], row['price']))

        # Sync Sales Fact Table
        # Clean down older metrics logs to prevent row collision on consecutive analytics testing runs
        mssql_cursor.execute("TRUNCATE TABLE fact_sales;")
        
        for _, row in df_sales.iterrows():
            mssql_cursor.execute("""
                INSERT INTO fact_sales (order_id, user_id, product_id, quantity_purchased, total_sales_volume, transaction_timestamp)
                VALUES (?, ?, ?, ?, ?, ?)
            """, (int(row['order_id']), int(row['user_id']), int(row['product_id']), 
                  int(row['quantity']), float(row['total_volume']), row['created_at']))

        # Commit and clean connection up
        mssql_conn.commit()
        print(f"[{datetime.now()}] ETL Execution complete! MSSQL Analytics tables populated successfully.")

    except Exception as e:
        print(f"Pipeline Execution Aborted: {str(e)}")
        
    finally:
        if 'oltp_conn' in locals() and oltp_conn.is_connected(): oltp_conn.close()
        if 'mssql_conn' in locals(): mssql_conn.close()

if __name__ == "__main__":
    run_analytics_etl()