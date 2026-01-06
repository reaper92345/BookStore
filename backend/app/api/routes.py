from fastapi import APIRouter, Depends, HTTPException, File, UploadFile, Form
import shutil
import os
from sqlalchemy.orm import Session
from .. import crud, models, schemas
from ..database import get_db

router = APIRouter()

# Book endpoints
import fitz  # PyMuPDF

@router.post("/books/", response_model=schemas.Book)
def create_book(
    title: str = Form(...),
    author: str = Form(...),
    description: str = Form(None),
    price: float = Form(...),
    stock: int = Form(...),
    file: UploadFile = File(None),
    thumbnail: UploadFile = File(None),
    db: Session = Depends(get_db)
):
    upload_dir = "uploads"
    os.makedirs(upload_dir, exist_ok=True)
    
    file_path = None
    if file:
        file_path = f"{upload_dir}/{file.filename}"
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
            
    thumbnail_path = None
    if thumbnail:
        thumbnail_path = f"{upload_dir}/{thumbnail.filename}"
        with open(thumbnail_path, "wb") as buffer:
            shutil.copyfileobj(thumbnail.file, buffer)
    elif file_path and file_path.lower().endswith('.pdf'):
        try:
            # Generate thumbnail from PDF
            doc = fitz.open(file_path)
            if len(doc) > 0:
                page = doc.load_page(0)  # Get first page
                pix = page.get_pixmap()
                thumbnail_filename = f"{os.path.splitext(os.path.basename(file.filename))[0]}_thumb.png"
                thumbnail_path = f"{upload_dir}/{thumbnail_filename}"
                pix.save(thumbnail_path)
                doc.close()
        except Exception as e:
            print(f"Error generating thumbnail: {e}")
            # Continue without thumbnail if generation fails
            
    book_data = schemas.BookCreate(
        title=title,
        author=author,
        description=description,
        price=price,
        stock=stock,
        file_path=file_path,
        thumbnail_path=thumbnail_path
    )
    return crud.create_book(db=db, book=book_data)

@router.get("/books/", response_model=list[schemas.Book])
def read_books(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    books = crud.get_books(db=db, skip=skip, limit=limit)
    return books

@router.get("/books/{book_id}", response_model=schemas.Book)
def read_book(book_id: int, db: Session = Depends(get_db)):
    db_book = crud.get_book(db=db, book_id=book_id)
    if db_book is None:
        raise HTTPException(status_code=404, detail="Book not found")
    return db_book

@router.put("/books/{book_id}", response_model=schemas.Book)
def update_book(book_id: int, book: schemas.BookUpdate, db: Session = Depends(get_db)):
    db_book = crud.get_book(db=db, book_id=book_id)
    if db_book is None:
        raise HTTPException(status_code=404, detail="Book not found")
    return crud.update_book(db=db, book_id=book_id, book=book)

@router.delete("/books/{book_id}")
def delete_book(book_id: int, db: Session = Depends(get_db)):
    db_book = crud.get_book(db=db, book_id=book_id)
    if db_book is None:
        raise HTTPException(status_code=404, detail="Book not found")
    crud.delete_book(db=db, book_id=book_id)
    return {"message": "Book deleted successfully"}

# Cart endpoints
@router.post("/cart/", response_model=dict)
def add_to_cart(cart_item: schemas.CartItemCreate, db: Session = Depends(get_db)):
    return crud.add_to_cart(db=db, cart_item=cart_item)

@router.get("/cart/", response_model=list[schemas.CartItem])
def get_cart(cart_id: str, db: Session = Depends(get_db)):
    return crud.get_cart(db=db, cart_id=cart_id)

@router.put("/cart/items/{item_id}", response_model=schemas.CartItem)
def update_cart_item(item_id: int, item: schemas.CartItemUpdate, db: Session = Depends(get_db)):
    return crud.update_cart_item(db=db, item_id=item_id, quantity=item.quantity)

@router.delete("/cart/items/{item_id}")
def remove_from_cart(item_id: int, db: Session = Depends(get_db)):
    crud.remove_from_cart(db=db, item_id=item_id)
    return {"message": "Item removed from cart"}

# User endpoints
@router.get("/users/", response_model=list[schemas.User])
def read_users(db: Session = Depends(get_db)):
    users = crud.get_users(db=db)
    return users

@router.get("/users/{user_id}", response_model=schemas.User)
def read_user(user_id: int, db: Session = Depends(get_db)):
    db_user = crud.get_user(db=db, user_id=user_id)
    if db_user is None:
        raise HTTPException(status_code=404, detail="User not found")
    return db_user

@router.post("/users/", response_model=schemas.User)
def create_user(user: schemas.UserCreate, db: Session = Depends(get_db)):
    return crud.create_user(db=db, user=user)

# Auth endpoints
@router.post("/auth/login/", response_model=schemas.Token)
def login(user: schemas.UserLogin, db: Session = Depends(get_db)):
    db_user = crud.authenticate_user(db=db, username=user.username, password=user.password)
    if not db_user:
        raise HTTPException(status_code=400, detail="Invalid credentials")
    return {"access_token": db_user.username, "token_type": "bearer"}

# Order endpoints
@router.get("/orders/", response_model=list[schemas.Order])
def read_orders(db: Session = Depends(get_db)):
    orders = crud.get_orders(db=db)
    return orders

@router.post("/orders/", response_model=schemas.Order)
def create_order(order: schemas.OrderCreate, db: Session = Depends(get_db)):
    return crud.create_order(db=db, order=order)

# Comment endpoints
@router.get("/comments/", response_model=list[schemas.Comment])
def read_comments(db: Session = Depends(get_db)):
    comments = crud.get_comments(db=db)
    return comments

@router.post("/comments/", response_model=schemas.Comment)
def create_comment(comment: schemas.CommentCreate, db: Session = Depends(get_db)):
    return crud.add_comment(db=db, comment=comment)