from pydantic import BaseModel
from typing import List, Optional

class BookBase(BaseModel):
    title: str
    author: str
    description: Optional[str] = None
    price: float
    stock: int

class BookCreate(BookBase):
    pass

class Book(BookBase):
    id: int

    class Config:
        orm_mode = True

class UserBase(BaseModel):
    username: str
    email: str

class UserCreate(UserBase):
    password: str

class User(UserBase):
    id: int

    class Config:
        orm_mode = True

class OrderItemBase(BaseModel):
    book_id: int
    quantity: int

class OrderItemCreate(OrderItemBase):
    pass

class OrderItem(OrderItemBase):
    id: int

    class Config:
        orm_mode = True

class OrderBase(BaseModel):
    user_id: int
    items: List[OrderItemCreate]

class OrderCreate(OrderBase):
    pass

class Order(OrderBase):
    id: int

    class Config:
        orm_mode = True