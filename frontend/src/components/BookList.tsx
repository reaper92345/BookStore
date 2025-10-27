import React, { useEffect, useState } from 'react';

const BookList: React.FC = () => {
    const [books, setBooks] = useState([]);

    useEffect(() => {
        const fetchBooks = async () => {
            const response = await fetch('/api/books');
            const data = await response.json();
            setBooks(data);
        };

        fetchBooks();
    }, []);

    return (
        <div>
            <h1>Book List</h1>
            <ul>
                {books.map((book) => (
                    <li key={book.id}>
                        <h2>{book.title}</h2>
                        <p>{book.author}</p>
                        <p>{book.description}</p>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default BookList;