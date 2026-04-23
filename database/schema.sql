CREATE DATABASE oscardb;

\c oscardb;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    visits INT NOT NULL DEFAULT 0
);