from sqlalchemy.orm import Session
from . import models, schemas, auth

# Book operations
def get_book(db: Session, book_id: int):
    return db.query(models.Book).filter(models.Book.id == book_id).first()

def get_books(db: Session, skip: int = 0, limit: int = 10):
    return db.query(models.Book).order_by(models.Book.id.desc()).offset(skip).limit(limit).all()

def create_book(db: Session, book: schemas.BookCreate):
    db_book = models.Book(**book.dict())
    db.add(db_book)
    db.commit()
    db.refresh(db_book)
    return db_book

def update_book(db: Session, book_id: int, book: schemas.BookUpdate):
    db_book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if db_book:
        for key, value in book.dict(exclude_unset=True).items():
            setattr(db_book, key, value)
        db.commit()
        db.refresh(db_book)
    return db_book

def delete_book(db: Session, book_id: int):
    db_book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if db_book:
        db.delete(db_book)
        db.commit()
    return db_book

# User operations
def get_user(db: Session, user_id: int):
    return db.query(models.User).filter(models.User.id == user_id).first()

def get_users(db: Session):
    return db.query(models.User).all()

def create_user(db: Session, user: schemas.UserCreate):
    hashed_password = auth.get_password_hash(user.password)
    db_user = models.User(
        username=user.username,
        email=user.email,
        hashed_password=hashed_password
    )
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    return db_user

def authenticate_user(db: Session, username: str, password: str):
    user = db.query(models.User).filter(models.User.username == username).first()
    if user and auth.verify_password(password, user.hashed_password):
        return user
    return None

# Cart operations
def add_to_cart(db: Session, cart_item: schemas.CartItemCreate):
    # Check if cart exists
    cart = db.query(models.Cart).filter(models.Cart.id == cart_item.cart_id).first()
    if not cart:
        cart = models.Cart(id=cart_item.cart_id)
        db.add(cart)
        db.commit()
        db.refresh(cart)

    # Check if item exists in cart
    db_item = db.query(models.CartItem).filter(
        models.CartItem.cart_id == cart_item.cart_id,
        models.CartItem.book_id == cart_item.book_id
    ).first()

    if db_item:
        db_item.quantity += cart_item.quantity
        db.commit()
        db.refresh(db_item)
        return {"message": "Item quantity updated", "item": db_item}
    else:
        db_item = models.CartItem(**cart_item.dict())
        db.add(db_item)
        db.commit()
        db.refresh(db_item)
        return {"message": "Item added to cart", "item": db_item}

def update_cart_item(db: Session, item_id: int, quantity: int):
    db_item = db.query(models.CartItem).filter(models.CartItem.id == item_id).first()
    if db_item:
        db_item.quantity = quantity
        db.commit()
        db.refresh(db_item)
    return db_item

def remove_from_cart(db: Session, item_id: int):
    db_item = db.query(models.CartItem).filter(models.CartItem.id == item_id).first()
    if db_item:
        db.delete(db_item)
        db.commit()
    return db_item

def get_cart(db: Session, cart_id: str):
    cart = db.query(models.Cart).filter(models.Cart.id == cart_id).first()
    if not cart:
        return []
    return cart.items

# Order operations
def get_orders(db: Session):
    return db.query(models.Order).all()

def create_order(db: Session, order: schemas.OrderCreate):
    db_order = models.Order(user_id=order.user_id)
    db.add(db_order)
    db.commit()
    db.refresh(db_order)
    return db_order

# Comment operations
def add_comment(db: Session, comment: schemas.CommentCreate):
    db_comment = models.Comment(**comment.dict())
    db.add(db_comment)
    db.commit()
    db.refresh(db_comment)
    return db_comment

def get_comments(db: Session):
    return db.query(models.Comment).all()