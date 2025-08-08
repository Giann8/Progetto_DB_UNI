CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(100) NOT NULL,
  isAdmin BOOLEAN DEFAULT FALSE
);

INSERT INTO users (name, email, password, isAdmin) VALUES
('Graziano', 'graziano@example.com', 'mypassword', FALSE),
('Admin', 'admin@example.com', 'adminpassword', TRUE);