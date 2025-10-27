import React from 'react';
import BookList from '../components/BookList';

const Home: React.FC = () => {
    return (
        <div>
            <h1>Welcome to the Online Bookstore</h1>
            <p>Explore our collection of books below:</p>
            <BookList />
        </div>
    );
};

export default Home;
