<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_query = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$sql = "SELECT 
            vehicle_id, 
            registration_no, 
            model_name, 
            description, 
            availability_status, 
            photo, 
            price_per_day,
            ac_price_per_day,
            non_ac_price_per_day,
            km_price,
            original_price_per_day,
            original_ac_price_per_day,
            original_non_ac_price_per_day,
            original_km_price,
            discount_percentage 
        FROM vehicles 
        WHERE availability_status = 'Available'";

$conditions = [];
$param_types = '';
$param_values = [];

if (!empty($search_query)) {
    $conditions[] = "(model_name LIKE ? OR description LIKE ?)";
    $param_types .= 'ss';
    $param_values[] = "%$search_query%";
    $param_values[] = "%$search_query%";
}


if (!empty($min_price)) {
    $conditions[] = "(
        (original_price_per_day * (1 - discount_percentage/100) >= ?) OR 
        (price_per_day >= ?)
    )";
    $param_types .= 'dd';
    $param_values[] = $min_price;
    $param_values[] = $min_price;
}

if (!empty($max_price)) {
    $conditions[] = "(
        (original_price_per_day * (1 - discount_percentage/100) <= ?) OR 
        (price_per_day <= ?)
    )";
    $param_types .= 'dd';
    $param_values[] = $max_price;
    $param_values[] = $max_price;
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

$stmt = $conn->prepare($sql);

if (!empty($param_values)) {
    $stmt->bind_param($param_types, ...$param_values);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/book.css">
    <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Online Car Rental</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <link href="assets/img/p.png" rel="icon">
  <link href="assets/img/p.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #d9534f;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            z-index: 10;
        }
        .original-price {
            text-decoration: line-through;
            color: #888;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .discounted-price {
            color: #d9534f;
            font-weight: bold;
        }
        .vehicle-card {
            position: relative;
        }
        .price-section {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .tab-container {
            display: flex;
            margin: 10px 0;
        }
        .price-tab {
            padding: 5px 10px;
            background-color: #f1f1f1;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 4px 4px 0 0;
            margin-right: 2px;
        }
        .price-tab.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .price-content {
            display: none;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 4px;
            margin-bottom: 10px;
        }
        .price-content.active {
            display: block;
        }
      
        :root {
            --primary-color: #007bff;
            --background-color: #f8f9fa;
            --text-color: #343a40;
            --card-background: #ffffff;
            --header-height: 60px;
            --border-radius: 8px;
            --box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            --price-color: #28a745; 
            --details-color: #17a2b8; 
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding-top: var(--header-height);
        }

        .header {
            width: 100%;
            height: var(--header-height);
            background-color: black;
            color: white;
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: var(--box-shadow);
            z-index: 10;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            transition: background-color 0.3s;
        }

        .header a:hover {
            background-color: black;
        }

        .search-bar {
            display: flex;
            margin-top: 70px; 
            justify-content: center;
            align-items: center;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            margin-right: 10px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .vehicle-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 10px;
            width: calc(25% - 20px);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .vehicle-card:hover {
            transform: translateY(-5px);
        }

        .vehicle-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .vehicle-info {
            padding: 15px;
            text-align: center;
        }

        .vehicle-info h3 {
            margin: 10px 0;
            font-size: 1.2rem;
        }

        .vehicle-info p {
            margin: 5px 0;
            color: var(--details-color);
        }

        .vehicle-price {
            color: var(--price-color);
            font-weight: bold;
            font-size: 1.5rem;
            margin: 10px 0;
        }

        .rent-button {
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.3s;
        }

        .rent-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .vehicle-card {
                width: calc(50% - 20px); 
            }
        }

        @media (max-width: 480px) {
            .vehicle-card {
                width: 100%; 
            }
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            font-size: 2rem;
            color: #000033;
        }
    

    </style>
</head>
<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center me-auto me-lg-0">
       
        <h1 class="sitename"><img src="assets/img/p.png" rel="icon">Online Car Rental</h1>
        <span>.</span>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php#hero" class="active">Home<br></a></li>
          <li><a href="index.php#about">About</a></li>
          <li><a href="index.php#services">Services</a></li>
          <li><a href="#">Cars</a></li>
          <li><a href="index.php#team">Team</a></li>
          <li class="dropdown"><a href="#"><span>Portals</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="customer/">Customer</a></li>
              <li class="dropdown"><a href="#"><span>Staffs</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                  <li><a href="driver/">Driver</a></li>
                  <li><a href="employee/">Employees</a></li>
                  <li><a href="admin/">Admin</a></li>
                  
                </ul>
              </li>
            </ul>
          </li>
          <li><a href="index.php#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="customer/">Get Started</a>

    </div>
  </header>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                
                $discount_percentage = $row['discount_percentage'] ?? 0;
                $has_discount = $discount_percentage > 0;
                
                
                $original_price = $row['original_price_per_day'] ?? 0;
                $discounted_price = $original_price * (1 - $discount_percentage / 100);
                
                $original_ac_price = $row['original_ac_price_per_day'] ?? 0;
                $discounted_ac_price = $original_ac_price * (1 - $discount_percentage / 100);
                
                $original_non_ac_price = $row['original_non_ac_price_per_day'] ?? 0;
                $discounted_non_ac_price = $original_non_ac_price * (1 - $discount_percentage / 100);
                
                $original_km_price = $row['original_km_price'] ?? 0;
                $discounted_km_price = $original_km_price * (1 - $discount_percentage / 100);
                
                
                $final_price = $has_discount ? $discounted_price : $original_price;
                if ((!empty($min_price) && $final_price < $min_price) || 
                    (!empty($max_price) && $final_price > $max_price)) {
                    continue; 
                }
            ?>
                <div class="vehicle-card">
                    <?php if ($has_discount): ?>
                        <div class="discount-badge">
                            -<?php echo number_format($discount_percentage, 0); ?>% OFF
                        </div>
                    <?php endif; ?>
                    
                    <img src="/admin/<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['model_name']); ?>">
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($row['model_name']); ?></h3>
                        <p><strong>Registration No:</strong> <?php echo htmlspecialchars($row['registration_no']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        
                        <div class="tab-container">
                            <div class="price-tab active" onclick="showPriceTab('standard-price-<?php echo $row['vehicle_id']; ?>', this)">Standard</div>
                            <?php if ($original_ac_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('ac-price-<?php echo $row['vehicle_id']; ?>', this)">AC</div>
                            <?php endif; ?>
                            <?php if ($original_non_ac_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('non-ac-price-<?php echo $row['vehicle_id']; ?>', this)">Non-AC</div>
                            <?php endif; ?>
                            <?php if ($original_km_price > 0): ?>
                                <div class="price-tab" onclick="showPriceTab('km-price-<?php echo $row['vehicle_id']; ?>', this)">Per KM</div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="standard-price-<?php echo $row['vehicle_id']; ?>" class="price-content active">
                            <div class="price-section">
                                <?php if ($has_discount): ?>
                                    <span class="original-price">KSH <?php echo number_format($original_price, 2); ?></span>
                                    <span class="discounted-price">KSH <?php echo number_format($discounted_price, 2); ?></span>
                                <?php else: ?>
                                    <span class="vehicle-price">Price per day: KSH <?php echo number_format($original_price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($original_ac_price > 0): ?>
                            <div id="ac-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_ac_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">AC Price per day: KSH <?php echo number_format($original_ac_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($original_non_ac_price > 0): ?>
                            <div id="non-ac-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_non_ac_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">Non-AC Price per day: KSH <?php echo number_format($original_non_ac_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($original_km_price > 0): ?>
                            <div id="km-price-<?php echo $row['vehicle_id']; ?>" class="price-content">
                                <div class="price-section">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price">KSH <?php echo number_format($original_km_price, 2); ?></span>
                                        <span class="discounted-price">KSH <?php echo number_format($discounted_km_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="vehicle-price">Price per KM: KSH <?php echo number_format($original_km_price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <button class="rent-button" onclick="location.href='customer/?id=<?php echo $row['vehicle_id']; ?>'">
                            <i class="fas fa-car"></i> Rent Now
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No vehicles available matching your search criteria.</p>
        <?php endif; ?>
    </div>

    <footer id="footer" class="footer dark-background">

  <div class="footer-top">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">Car Rental</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Kindaruma Road</p>
            <p>Nairbi, Kenya</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+254 75756537</span></p>
            <p><strong>Email:</strong> <span>carrental@gmail.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#"> Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#"> About us</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#"> Services</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="terms.html"> Terms of service</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#"> Privacy policy</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-12 footer-newsletter">
          <h4>Our Newsletter</h4>
          <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
          <form action="subscribe.php" method="post">
            <div class="newsletter-form" style="display: flex; flex-direction: column; align-items: center; margin: 20px 0;">
                <input type="email" name="email" 
                       style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px;" 
                       placeholder="Enter Your Email" required>
                <input type="submit" value="Subscribe" 
                       style="padding: 10px; width: 300px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
            </div>
        </form>
        
        </div>

      </div>
    </div>
  </div>

  <div class="copyright">
    <div class="container text-center">
      <p>© <span> <?php echo date("Y"); ?></span> <strong class="px-1 sitename">Online Car Rental</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        
        Designed by <a href="https://estonkiama.netlify.app/">Eston Kiama</a>
      </div>
    </div>
  </div>

</footer>
    <script>
    function showPriceTab(tabId, clickedTab) {
        const allTabs = clickedTab.parentElement.getElementsByClassName('price-tab');
        for (let i = 0; i < allTabs.length; i++) {
            allTabs[i].classList.remove('active');
        }
        clickedTab.classList.add('active');
        const vehicleInfo = clickedTab.parentElement.parentElement;
        const allContent = vehicleInfo.getElementsByClassName('price-content');
        for (let i = 0; i < allContent.length; i++) {
            allContent[i].classList.remove('active');
        }
        document.getElementById(tabId).classList.add('active');
    }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>