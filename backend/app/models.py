from sqlalchemy import Column, Integer, String, ForeignKey
from sqlalchemy.orm import relationship
from database import Base

class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String, unique=True, index=True)
    email = Column(String, unique=True, index=True)
    hashed_password = Column(String)

    books = relationship("Book", back_populates="owner")
    orders = relationship("Order", back_populates="user")

class Book(Base):
    __tablename__ = 'books'

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String, index=True)
    author = Column(String)
    description = Column(String)
    price = Column(Integer)
    owner_id = Column(Integer, ForeignKey('users.id'))

    owner = relationship("User", back_populates="books")
    order_items = relationship("OrderItem", back_populates="book")

class Order(Base):
    __tablename__ = 'orders'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'))

    user = relationship("User", back_populates="orders")
    items = relationship("OrderItem", back_populates="order")

class OrderItem(Base):
    __tablename__ = 'order_items'

    id = Column(Integer, primary_key=True, index=True)
    order_id = Column(Integer, ForeignKey('orders.id'))
    book_id = Column(Integer, ForeignKey('books.id'))
    quantity = Column(Integer)

    order = relationship("Order", back_populates="items")
    book = relationship("Book", back_populates="order_items")