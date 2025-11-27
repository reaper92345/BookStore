import sys
import os

# Add the backend directory to the python path
sys.path.append(os.path.join(os.getcwd(), 'backend'))

from backend.app.database import engine, Base
from backend.app import models

def init_db():
    print("Creating database tables...")
    Base.metadata.create_all(bind=engine)
    print("Tables created successfully.")

if __name__ == "__main__":
    init_db()
