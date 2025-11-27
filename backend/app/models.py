from sqlalchemy import Column, Integer, String, ForeignKey, Text, Float, DateTime
from sqlalchemy.orm import relationship
from datetime import datetime
from .database import Base

class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(255), unique=True, index=True)
    email = Column(String(255), unique=True, index=True)
    hashed_password = Column(String(255))
    created_at = Column(DateTime, default=datetime.utcnow)

    orders = relationship("Order", back_populates="user")
    comments = relationship("Comment", back_populates="user")

class Book(Base):
    __tablename__ = 'books'

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String(255), index=True)
    author = Column(String(255))
    description = Column(Text)
    price = Column(Float)
    stock = Column(Integer, default=0)
    file_path = Column(String(255), nullable=True)
    thumbnail_path = Column(String(255), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    order_items = relationship("OrderItem", back_populates="book")
    comments = relationship("Comment", back_populates="book")

class Order(Base):
    __tablename__ = 'orders'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'))
    status = Column(String(50), default='pending')
    created_at = Column(DateTime, default=datetime.utcnow)

    user = relationship("User", back_populates="orders")
    items = relationship("OrderItem", back_populates="order")

class OrderItem(Base):
    __tablename__ = 'order_items'

    id = Column(Integer, primary_key=True, index=True)
    order_id = Column(Integer, ForeignKey('orders.id'))
    book_id = Column(Integer, ForeignKey('books.id'))
    quantity = Column(Integer)
    price = Column(Float)

    order = relationship("Order", back_populates="items")
    book = relationship("Book", back_populates="order_items")

class Comment(Base):
    __tablename__ = 'comments'

    id = Column(Integer, primary_key=True, index=True)
    book_id = Column(Integer, ForeignKey('books.id'))
    user_id = Column(Integer, ForeignKey('users.id'))
    content = Column(Text)
    rating = Column(Integer)
    status = Column(String(50), default='pending')
    created_at = Column(DateTime, default=datetime.utcnow)

    book = relationship("Book", back_populates="comments")
    user = relationship("User", back_populates="comments")

class Cart(Base):
    __tablename__ = 'carts'

    id = Column(String(255), primary_key=True, index=True)
    created_at = Column(DateTime, default=datetime.utcnow)

    items = relationship("CartItem", back_populates="cart")

class CartItem(Base):
    __tablename__ = 'cart_items'

    id = Column(Integer, primary_key=True, index=True)
    cart_id = Column(String(255), ForeignKey('carts.id'))
    book_id = Column(Integer, ForeignKey('books.id'))
    quantity = Column(Integer, default=1)

    cart = relationship("Cart", back_populates="items")
    book = relationship("Book")