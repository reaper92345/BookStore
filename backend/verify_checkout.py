import sys
import os

# Add the parent directory to sys.path to import the app
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from app import models, crud, schemas

# Try to connect to localhost if 'db' fails (common in local vs docker)
DATABASE_URL = "mysql+pymysql://root:@localhost/bookstore"

try:
    engine = create_engine(DATABASE_URL)
    SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
    db = SessionLocal()
    print("Successfully connected to the database.")
except Exception as e:
    print(f"Failed to connect to database: {e}")
    sys.exit(1)

def test_checkout():
    # 1. Pick a book
    book = db.query(models.Book).filter(models.Book.id == 1).first()
    if not book:
        print("Book 1 not found. Please ensure seed data is present.")
        return
    
    initial_stock = book.stock
    print(f"Initial stock for '{book.title}': {initial_stock}")

    # 2. Pick a user
    user = db.query(models.User).first()
    if not user:
        print("No user found. Please register a user first.")
        return

    # 3. Test successful checkout (1 book)
    print("\nTesting successful checkout of 1 book...")
    order_in = schemas.OrderCreate(
        user_id=user.id,
        items=[schemas.OrderItemCreate(book_id=book.id, quantity=1)]
    )
    
    try:
        order = crud.create_order(db, order_in)
        db.refresh(book)
        print(f"Checkout successful. New stock: {book.stock}")
        if book.stock == initial_stock - 1:
            print("SUCCESS: Stock decreased correctly.")
        else:
            print(f"FAILURE: Stock did not decrease correctly. Expected {initial_stock - 1}, got {book.stock}")
    except Exception as e:
        print(f"FAILURE: Checkout failed: {e}")

    # 4. Test insufficient stock (trying to buy more than available)
    print(f"\nTesting insufficient stock (trying to buy {book.stock + 10} books)...")
    order_fail_in = schemas.OrderCreate(
        user_id=user.id,
        items=[schemas.OrderItemCreate(book_id=book.id, quantity=book.stock + 10)]
    )
    
    try:
        crud.create_order(db, order_fail_in)
        print("FAILURE: Checkout should have failed but succeeded.")
    except ValueError as e:
        print(f"SUCCESS: Checkout failed as expected with message: {e}")
    except Exception as e:
        print(f"FAILURE: Checkout failed with unexpected exception type: {type(e).__name__}: {e}")

if __name__ == "__main__":
    test_checkout()
    db.close()
