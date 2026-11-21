<?php
    //step-1:-create a connection
    $con=mysqli_connect("localhost","root","");

    if($con){
        // echo "Connection sucessful";
    }else{
        echo "Error in Connection";
    }

    //step-2:Create database this is onetime Process sob we can comment in after creating database
    // $create_db="Create database grocery";
    // if(mysqli_query($con,$create_db)){
    //     echo "Database is Created";
    // }else{
    //     echo "Error in creating Database";
    // }

    //step-3 select database
    try{
        mysqli_select_db($con,"grocery");
    }catch(Exception){
        echo "Error in connctig with DB";
    }



    //step-4: Createing table is ontime process so we can comment in after creating table

    // Create users table if not exists (with proper structure)
// $create_table = "CREATE TABLE IF NOT EXISTS register (
//     id int auto_increment primary key,
//     first_name varchar(100),
//     last_name varchar(100),
//     email varchar(100) unique,
//     password varchar(255),
//     mobile bigint(10),
//     gender varchar(30),
//     profile_picture text,
//     role varchar(30) default 'user',
//     status varchar(30) default 'Inactive',
//     token text,
//     created_at timestamp default current_timestamp
// )";



// if (!mysqli_query($con, $create_table)) {
//     echo "Error creating table: " . mysqli_error($con);
// }

    //Product_categories
//     $create_table="CREATE TABLE product_categories (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     image VARCHAR(255) NOT NULL,
//     category_name VARCHAR(100) NOT NULL,
//     description TEXT,
//     status ENUM('Active','Inactive') DEFAULT 'Active',
//     created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )";
//  if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }

//product-table

// $create_table="CREATE TABLE IF NOT EXISTS products (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     product_name VARCHAR(255) NOT NULL,
//     category VARCHAR(100) NOT NULL,
//     price DECIMAL(10,2) NOT NULL,
//     weight VARCHAR(50) NOT NULL,
//     stock INT NOT NULL DEFAULT 0,
//     description TEXT,
//     image VARCHAR(255),
//     status ENUM('in-stock', 'out-of-stock') DEFAULT 'in-stock',
//     created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
// )";
// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }



// $create_table="CREATE TABLE IF NOT EXISTS contact_messages (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     email VARCHAR(100) NOT NULL,
//     phone VARCHAR(20),
//     subject VARCHAR(50) NOT NULL,
//     message TEXT NOT NULL,
//     status ENUM('pending', 'replied', 'closed') DEFAULT 'pending',
//     admin_reply TEXT,
//     replied_by INT,
//     replied_at DATETIME,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     INDEX (email),
//     INDEX (status)
// )";
// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }



// $create_table="CREATE TABLE IF NOT EXISTS orders (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     order_id VARCHAR(50) UNIQUE NOT NULL,
//     customer_name VARCHAR(100) NOT NULL,
//     customer_email VARCHAR(100) NOT NULL,
//     customer_mobile VARCHAR(20) NOT NULL,
//     customer_address TEXT NOT NULL,
//     payment_method VARCHAR(50) NOT NULL,
//     subtotal DECIMAL(10,2) NOT NULL,
//     gst DECIMAL(10,2) NOT NULL,
//     grand_total DECIMAL(10,2) NOT NULL,
//     order_status ENUM('pending', 'approved', 'delivered', 'cancelled') DEFAULT 'pending',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
// )";
// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }


// $create_table="CREATE TABLE IF NOT EXISTS order_items (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     order_id INT NOT NULL,
//     product_name VARCHAR(200) NOT NULL,
//     price DECIMAL(10,2) NOT NULL,
//     quantity INT NOT NULL,
//     total DECIMAL(10,2) NOT NULL,
//     FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
// )";
// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }


// $create_table="CREATE TABLE IF NOT EXISTS settings (
//     id int auto_increment primary key,
//     setting_key varchar(100) unique not null,
//     setting_value text,
//     setting_type varchar(50) default 'text',
//     created_at timestamp default current_timestamp,
//     updated_at timestamp default current_timestamp on update current_timestamp
// )";

// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }

// Create cart table
// $create_cart_table = "CREATE TABLE IF NOT EXISTS cart (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT NOT NULL,
//     product_id INT NOT NULL,
//     quantity INT NOT NULL DEFAULT 1,
//     added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
//     UNIQUE KEY unique_cart_item (user_id, product_id)
// )";

// if (!mysqli_query($con, $create_cart_table)) {
//     echo "Error creating cart table: " . mysqli_error($con);
// }


//  $create_table = "CREATE TABLE `orders` (
//   `id` int NOT NULL,
//   `user_id` int NOT NULL,
//   `order_number` varchar(20) NOT NULL,
//   `customer_name` varchar(100) NOT NULL,
//   `customer_mobile` varchar(15) NOT NULL,
//   `customer_address` text NOT NULL,
//   `payment_method` enum('COD','Online') NOT NULL,
//   `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
//   `subtotal` decimal(10,2) NOT NULL,
//   `gst_amount` decimal(10,2) NOT NULL,
//   `total_amount` decimal(10,2) NOT NULL,
//   `order_status` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
//   `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//   `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   `razorpay_payment_id` varchar(100) DEFAULT NULL,
//   `razorpay_order_id` varchar(100) DEFAULT NULL
// )";

// $create_table="CREATE TABLE IF NOT EXISTS order_items (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     order_id INT NOT NULL,
//     product_id INT NOT NULL,
//     product_name VARCHAR(255) NOT NULL,
//     quantity INT NOT NULL,
//     price DECIMAL(10,2) NOT NULL,
//     weight VARCHAR(50),
//     subtotal DECIMAL(10,2) NOT NULL,
//     FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
//     FOREIGN KEY (product_id) REFERENCES products(id)
// )";
// if(mysqli_query($con,$create_table)){
//         echo "Table created";
//     }else{
//         echo "error in creating table";
//     }

//  Create offers table
// $create_offers_table = "CREATE TABLE IF NOT EXISTS offers (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     coupon_code VARCHAR(50) UNIQUE NOT NULL,
//     offer_title VARCHAR(200) NOT NULL,
//     description TEXT,
//     discount_type ENUM('percentage', 'flat') NOT NULL,
//     discount_value DECIMAL(10,2) NOT NULL,
//     min_purchase DECIMAL(10,2) DEFAULT 0,
//     valid_from DATETIME NOT NULL,
//     valid_to DATETIME NOT NULL,
//     usage_limit INT DEFAULT 0,
//     used_count INT DEFAULT 0,
//     apply_to ENUM('all', 'category', 'products') DEFAULT 'all',
//     category_id INT DEFAULT NULL,
//     status ENUM('active', 'inactive') DEFAULT 'active',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     INDEX idx_coupon_code (coupon_code),
//     INDEX idx_status_dates (status, valid_from, valid_to)
// )";

// if (!mysqli_query($con, $create_offers_table)) {
//     echo "Error creating offers table: " . mysqli_error($con);
// }

// // Create offer_products table for specific product offers
// $create_offer_products = "CREATE TABLE IF NOT EXISTS offer_products (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     offer_id INT NOT NULL,
//     product_id INT NOT NULL,
//     FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
//     UNIQUE KEY unique_offer_product (offer_id, product_id)
// )";

// if (!mysqli_query($con, $create_offer_products)) {
//     echo "Error creating offer_products table: " . mysqli_error($con);
// }

// // Generate proper password hash
// $password = 'admin123';
// $hashed_password = password_hash($password, PASSWORD_DEFAULT);

// echo "Generated hash for 'admin123': " . $hashed_password . "<br><br>";

// // First, check if admin already exists
// $email = 'priyankmalaviya111@gmail.com';
// $check_query = "SELECT id FROM register WHERE email = '$email'";
// $check_result = mysqli_query($con, $check_query);

// if (mysqli_num_rows($check_result) > 0) {
//     echo "Admin with email $email already exists!<br>";
//     echo "Updating existing admin to have admin role...<br>";
    
//     // Update existing user to admin
//     $update_query = "UPDATE register SET role = 'admin', status = 'Active' WHERE email = '$email'";
//     if (mysqli_query($con, $update_query)) {
//         echo "User updated to admin successfully!<br>";
//     } else {
//         echo "Error updating user: " . mysqli_error($con) . "<br>";
//     }
// } else {
//     // Insert new admin
//     $insert_query = "INSERT INTO register (first_name, last_name, email, password, mobile, role, status, created_at) 
//                      VALUES ('Priyank', 'Malaviya', '$email', '$hashed_password', '1234567890', 'admin', 'Active', NOW())";
    
//     if (mysqli_query($con, $insert_query)) {
//         echo "Admin created successfully!<br>";
//     } else {
//         echo "Error creating admin: " . mysqli_error($con) . "<br>";
//     }
// }

// echo "<br><br>";
// echo "You can now login with:<br>";
// echo "Email: $email<br>";
// echo "Password: admin123<br>";


?>