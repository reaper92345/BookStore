import React, { useEffect, useState } from 'react';

interface Book {
    id: number | string;
    title: string;
    author: string;
    description?: string;
}

const BookList: React.FC = () => {
    const [books, setBooks] = useState<Book[]>([]);

    useEffect(() => {
        const fetchBooks = async () => {
            try {
                const response = await fetch('/api/books');
                if (!response.ok) return;
                const data: Book[] = await response.json();
                setBooks(data);
            } catch (e) {
                // swallow fetch errors in the UI for now
                console.error('Failed to fetch books', e);
            }
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