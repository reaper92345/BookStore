from sqlalchemy import create_engine
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
import os

DATABASE_URL = os.getenv("DATABASE_URL", "mysql://user:password@db:3306/bookstore")

engine = create_engine(
    DATABASE_URL,
    pool_pre_ping=True,  # Enable automatic reconnection
    pool_recycle=3600   # Recycle connections after 1 hour
)

Base = declarative_base()

def init_db():
    import app.models  # Import all models here to ensure they are registered
    Base.metadata.create_all(bind=engine)