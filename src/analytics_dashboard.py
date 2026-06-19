# src/analytics_dashboard.py
import pandas as pd
import dash
from dash import dcc, html
from dash.dependencies import Input, Output
import plotly.express as px
from sqlalchemy import create_engine

# Explicit SQLAlchemy connection string format targeting your local MSSQL Named Instance
CONNECTION_URL = (
    "mssql+pyodbc://@MANANVERMA\\MSSQL/ecommerce_olap"
    "?driver=ODBC+Driver+17+for+SQL+Server"
    "&trusted_connection=yes"
)

def fetch_warehouse_metrics():
    """Queries your data warehouse dynamically while bypassing connection pooling cache."""
    # PATCHED: Added execution options to completely kill caching mechanisms
    engine = create_engine(
        CONNECTION_URL,
        pool_size=1,          # Minimal footprint
        max_overflow=0,       # Force strict closure
        pool_recycle=1        # Recycle connection immediately
    )
    
    # Establish connection with no-cache isolation modifiers
    with engine.connect() as connection:
        # Query 1: Top performing product asset metrics
        product_query = """
            SELECT p.name AS Product, SUM(f.total_sales_volume) AS Revenue
            FROM fact_sales f
            JOIN dim_products p ON f.product_id = p.product_id
            GROUP BY p.name
        """
        df_products = pd.read_sql(product_query, connection)
        
        # Query 2: Aggregate revenue by operational categorization matrix
        category_query = """
            SELECT p.category_name AS Category, SUM(f.total_sales_volume) AS Revenue
            FROM fact_sales f
            JOIN dim_products p ON f.product_id = p.product_id
            GROUP BY p.category_name
        """
        df_categories = pd.read_sql(category_query, connection)
        
        # Query 3: Core KPIs summary calculations 
        kpi_query = "SELECT SUM(total_sales_volume) as Gross, COUNT(DISTINCT order_id) as Orders FROM fact_sales"
        df_kpi = pd.read_sql(kpi_query, connection)
    
    # Completely drop connection context assets back to the system engine
    engine.dispose()
    
    return df_products, df_categories, df_kpi


# ==========================================
# INITIALIZE DASHBOARD ENGINE INTERFACE
# ==========================================
app = dash.Dash(__name__)

# Layout hierarchy designed with an automated polling trigger element
app.layout = html.Div(style={'backgroundColor': '#131a2c', 'color': '#f1f5f9', 'padding': '15px', 'fontFamily': 'sans-serif'}, children=[
    
    # Interval Polling Loop component to trigger data refresh every 3 seconds automatically
    dcc.Interval(id='interval-component', interval=3000, n_intervals=0),

    # KPI metrics panel highlight cards block split
    html.Div(style={'display': 'flex', 'gap': '20px', 'marginBottom': '25px'}, children=[
        html.Div(style={'backgroundColor': '#0b0f19', 'border': '1px solid #222f47', 'padding': '20px', 'borderRadius': '10px', 'flex': '1'}, children=[
            html.H3("Aggregated Gross Revenue Value", style={'color': '#64748b', 'fontSize': '0.75rem', 'textTransform': 'uppercase', 'margin': '0', 'letterSpacing': '0.5px'}),
            html.Div(id='live-gross-revenue')
        ]),
        html.Div(style={'backgroundColor': '#0b0f19', 'border': '1px solid #222f47', 'padding': '20px', 'borderRadius': '10px', 'flex': '1'}, children=[
            html.H3("Processed Warehouse Orders Volume", style={'color': '#64748b', 'fontSize': '0.75rem', 'textTransform': 'uppercase', 'margin': '0', 'letterSpacing': '0.5px'}),
            html.Div(id='live-orders-volume')
        ])
    ]),
    
    # Visualization layout grids split row panels block
    html.Div(style={'display': 'grid', 'gridTemplateColumns': '1fr 1fr', 'gap': '20px'}, children=[
        html.Div(style={'backgroundColor': '#0b0f19', 'border': '1px solid #222f47', 'borderRadius': '10px', 'overflow': 'hidden'}, children=[
            dcc.Graph(id='live-product-bar')
        ]),
        html.Div(style={'backgroundColor': '#0b0f19', 'border': '1px solid #222f47', 'borderRadius': '10px', 'overflow': 'hidden'}, children=[
            dcc.Graph(id='live-category-pie')
        ])
    ])
])


# ==========================================
# REACTIVE DATA REFRESH CALLBACK CONTROL
# ==========================================
@app.callback(
    [Output('live-gross-revenue', 'children'),
     Output('live-orders-volume', 'children'),
     Output('live-product-bar', 'figure'),
     Output('live-category-pie', 'figure')],
    [Input('interval-component', 'n_intervals')]
)
def update_dashboard_metrics(n):
    # Fetch fresh data elements directly from hardware disk storage
    df_products, df_categories, df_kpi = fetch_warehouse_metrics()

    # Safely catch NaN values if warehouse has not been synchronized yet
    gross_rev = df_kpi['Gross'].iloc[0] if pd.notna(df_kpi['Gross'].iloc[0]) else 0.0
    total_orders = df_kpi['Orders'].iloc[0] if pd.notna(df_kpi['Orders'].iloc[0]) else 0

    # Build layout text blocks dynamically
    gross_element = html.P(f"${gross_rev:,.2f}", style={'fontSize': '1.8rem', 'fontWeight': 'bold', 'color': '#10b981', 'margin': '8px 0 0 0'})
    orders_element = html.P(f"{total_orders} Orders Logs", style={'fontSize': '1.8rem', 'fontWeight': 'bold', 'color': '#ffffff', 'margin': '8px 0 0 0'})

    # Dynamic updates for bar graph configurations
    fig_products = px.bar(
        df_products, x='Product', y='Revenue',
        title="Product Revenue Distribution Profile",
        template="plotly_dark",
        color_discrete_sequence=['#10b981']
    )
    fig_products.update_layout(plot_bgcolor='#131a2c', paper_bgcolor='#131a2c', margin=dict(l=40, r=40, t=50, b=40))

    # Dynamic updates for pie graph configurations
    fig_categories = px.pie(
        df_categories, names='Category', values='Revenue',
        title="Market Share Allocation by Department",
        template="plotly_dark",
        hole=0.4
    )
    fig_categories.update_layout(plot_bgcolor='#131a2c', paper_bgcolor='#131a2c', margin=dict(l=40, r=40, t=50, b=40))

    return gross_element, orders_element, fig_products, fig_categories


if __name__ == '__main__':
    print("Launching Local Business Intelligence Dashboard Matrix Server...")
    app.run(debug=True, port=8050)