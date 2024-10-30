CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(12) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    dob DATE NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    residence VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (LENGTH(password) >= 8)  
);

CREATE TABLE IF NOT EXISTS drivers (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_no VARCHAR(15) NOT NULL,
    residence TEXT NOT NULL,
    age INT NOT NULL CHECK (age BETWEEN 18 AND 70),
    driving_license_no VARCHAR(50) NOT NULL UNIQUE,
    license_image VARCHAR(255) NOT NULL
   );
    ALTER TABLE drivers
ADD email VARCHAR(255) NOT NULL;

ALTER TABLE drivers 
ADD COLUMN password VARCHAR(255) NOT NULL;


CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,  
    registration_no VARCHAR(8) NOT NULL,        
    model_name VARCHAR(100) NOT NULL,          
    description TEXT NOT NULL,                 
    availability_status ENUM('Available', 'Unavailable') NOT NULL,
    photo VARCHAR(255),                         
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_no VARCHAR(20) NOT NULL,
    email_address VARCHAR(100) NOT NULL UNIQUE,
    gender ENUM('male', 'female', 'other') NOT NULL,
    password VARCHAR(255) NOT NULL
    ALTER TABLE admins
ADD COLUMN profile_picture VARCHAR(255);

);

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT,
    customer_id INT,
    vehicle_id INT,
    delivery_address VARCHAR(255),
    delivery_date DATETIME,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    completion_date DATETIME,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id),
    FOREIGN KEY (id) REFERENCES customers(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);
;
