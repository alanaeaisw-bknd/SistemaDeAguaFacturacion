import os
from pymysql
import dotenv import load_dotenv
load_dotenv()
def get_conn():
    return pymysql.connect(
        host=os.getenv("DB_HOST","localhost"),
        port=os.getenv("DB_PORT","3307"), 
        user=os.getenv("",""), 
        password = os.getenv("DB_PASSWORD"),
        database = os.getenv("DB_NAME"),
        charset = "utf8mb4",
        cursorclass = pymysql.cursors.DictCursor,
        autocommit = True
    )