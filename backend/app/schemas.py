from pydantic import BaseModel
from typing import List, Optional

class BookBase(BaseModel):
    title: str
    author: str
    description: Optional[str] = None
    price: float
    stock: int
    file_path: Optional[str] = None
    thumbnail_path: Optional[str] = None

class BookCreate(BookBase):
    pass

class BookUpdate(BaseModel):
    title: Optional[str] = None
    author: Optional[str] = None
    description: Optional[str] = None
    price: Optional[float] = None
    stock: Optional[int] = None

class Book(BookBase):
    id: int

    class Config:
        orm_mode = True

class UserBase(BaseModel):
    username: str
    email: str

class UserCreate(UserBase):
    password: str

class UserLogin(BaseModel):
    username: str
    password: str

class User(UserBase):
    id: int

    class Config:
        orm_mode = True

class CartItemCreate(BaseModel):
    cart_id: str
    book_id: int
    quantity: int

class CartItemUpdate(BaseModel):
    quantity: int

class CartItem(BaseModel):
    id: int
    book_id: int
    quantity: int
    book: Book

    class Config:
        orm_mode = True

class Cart(BaseModel):
    id: str
    items: List[CartItem] = []

    class Config:
        orm_mode = True

class OrderItemBase(BaseModel):
    book_id: int
    quantity: int

class OrderItemCreate(OrderItemBase):
    pass

class OrderItem(OrderItemBase):
    id: int
    price: float

    class Config:
        orm_mode = True

class OrderBase(BaseModel):
    user_id: int

class OrderCreate(OrderBase):
    items: List[OrderItemCreate]

class Order(OrderBase):
    id: int
    items: List[OrderItem] = []
    status: str = 'pending'

    class Config:
        orm_mode = True

class CommentCreate(BaseModel):
    book_id: int
    user_id: int
    content: str
    rating: int

class Comment(CommentCreate):
    id: int
    status: str = 'pending'

    class Config:
        orm_mode = True

class Token(BaseModel):
    access_token: str
    token_type: str
    username: str
    email: str
    id: int