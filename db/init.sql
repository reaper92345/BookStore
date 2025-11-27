CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    hashed_password VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    owner_id INT DEFAULT NULL,
    file_path VARCHAR(255),
    thumbnail_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    book_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB;

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    user_id INT,
    content TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Add status to orders table
ALTER TABLE orders 
ADD COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') 
DEFAULT 'pending' AFTER user_id;

INSERT INTO books (title, author, description, price, stock) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 'A novel set in the 1920s about the American dream.', 10.99, 5),
('1984', 'George Orwell', 'A dystopian novel about totalitarianism and surveillance.', 8.99, 8),
('To Kill a Mockingbird', 'Harper Lee', 'A novel about racial injustice in the Deep South.', 12.99, 4),
('Pride and Prejudice', 'Jane Austen', 'A romantic novel that critiques the British landed gentry.', 9.99, 6),
('The Catcher in the Rye', 'J.D. Salinger', 'A story about teenage angst and alienation.', 10.99, 3),
('Moby Dick', 'Herman Melville', 'A novel about the obsession with hunting a giant whale.', 11.99, 2),
('War and Peace', 'Leo Tolstoy', 'A historical novel that intertwines the lives of several families.', 14.99, 1),
('The Odyssey', 'Homer', 'An epic poem about the adventures of Odysseus.', 13.99, 7),
('The Hobbit', 'J.R.R. Tolkien', 'A fantasy novel about the journey of Bilbo Baggins.', 15.99, 5),
('Fahrenheit 451', 'Ray Bradbury', 'A dystopian novel about a future where books are banned.', 9.99, 4);