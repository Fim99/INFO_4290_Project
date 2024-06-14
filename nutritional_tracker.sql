-- create and select the database
DROP DATABASE IF EXISTS nutritional_tracker;
CREATE DATABASE nutritional_tracker;
USE nutritional_tracker;  -- MySQL command

-- Create the users table
CREATE TABLE users 
(
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    alerts JSON NOT NULL
);

-- Create the meals table
CREATE TABLE meals 
(
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    food_fdcId JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create the users
CREATE USER IF NOT EXISTS admin_user
IDENTIFIED BY '12345';

-- Grant privileges to the users
GRANT SELECT, INSERT, DELETE, UPDATE
ON nutritional_tracker.* 
TO admin_user;
