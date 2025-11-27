from app.database import init_db, engine
from app import models

print("Creating tables...")
init_db()
print("Tables created.")
