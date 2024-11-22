<?php


$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "car_rental_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}


$sql = "SELECT vehicle_id, registration_no, model_name, description, availability_status, photo, price_per_day 
        FROM vehicles 
        WHERE availability_status = 'Available' 
        AND (model_name LIKE ? OR description LIKE ? OR price_per_day LIKE ?)";
$stmt = $conn->prepare($sql);
$like_query = "%" . $search_query . "%";
$stmt->bind_param("sss", $like_query, $like_query, $like_query);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Online Car Rental</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/p.png" rel="icon">
  <link href="assets/img/p.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
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
       
        <h1 class="sitename">Online Car Rental</h1>
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
  <main>
  <h1>Available Vehicles</h1>

    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <img src="/admin/<?php echo htmlspecialchars($row['photo']); ?>" alt="<?php echo htmlspecialchars($row['model_name']); ?>">
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($row['model_name']); ?></h3>
                        <p><strong style="color: var(--details-color);">Registration No:</strong> <?php echo htmlspecialchars($row['registration_no']); ?></p>
                        <p><strong style="color: var(--details-color);">Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="vehicle-price">Price per day: KSH <?php echo number_format($row['price_per_day'], 2); ?></p>
                        <button class="rent-button" onclick="location.href='customer/?id=<?php echo $row['vehicle_id']; ?>'">Rent Now</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No vehicles available at the moment.</p>
        <?php endif; ?>
    </div>
  </main>

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

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Preloader -->
<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
<script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>

<!-- Main JS File -->
<script src="assets/js/main.js"></script>

</body>

</html>