<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page SPA AI</title>
  <link rel="icon" type="image/png" sizes="712x712" href="images/SPA AI.png">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="home.css">
</head>
<?php
include 'database.php';
$result = mysqli_query($conn, "SELECT home_bg FROM settings WHERE id = 1");
$settings = mysqli_fetch_assoc($result);
$home_bg = 'uploads/' . ($settings['home_bg'] ?? 'default_home.png'); // Corrected path

$sql = "SELECT platform, url FROM social_links";
$result = $conn->query($sql);

$socialLinks = [];
while ($row = $result->fetch_assoc()) {
  $socialLinks[$row["platform"]] = $row["url"];
}
?>
<style>
  .content {
    height: 100vh;
    background-color: #f0f0f0;
    background-image: url('<?php echo $home_bg; ?>');
    background-repeat: no-repeat;
    background-size: 60%;
    background-position: right center;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0e0606;
    padding: 0;
    overflow: hidden;
  }
</style>

<body>

  <nav class="navbar navbar-expand-lg navbar-light navbar-green fixed-top">
    <a class="navbar-brand" href="index.php">
      <img src="images/SPA AI.png" alt="SPA AI - Smart Personal Assistant" class="logo-animate">
      <span class="text-wave">
        <span style="--i:1">S</span>
        <span style="--i:2">P</span>
        <span style="--i:3">A</span>
        <span style="--i:4">.</span>
        <span style="--i:5">A</span>
        <span style="--i:6">I</span>
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="login.php">Admin</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="users.php">Join</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#about">About</a>
        </li>
      </ul>
    </div>
  </nav>

  <section id="welcome" class="welcome-section">
  <div class="content">
    <div class="text-center">
      <h1 class="carousel-heading">Welcome to<br>Smart Personal Assistant <br> <a href="users.php" class="get-started-btn">Get Started →</a>
    </h1>
    </div>
  </div>
</section>


  <!-- About Section -->
  <section id="about" class="py-5 text-center">
    <div class="container">
      <h2 class="mb-4 section-title">About <span>Smart Personal Assistant A.I</span></h2>
      <p class="lead about-text">
        SPA.AI is an intelligent, automated announcement system designed to streamline communication across various platforms.
        Whether for schools, businesses, or organizations, it ensures timely updates, event notifications,
        and important announcements reach the right audience. With AI-powered automation and seamless integration,
        SPA.AI enhances engagement, keeps users informed, and simplifies the way essential information is shared.
      </p>
    </div>

    <!-- Features Section -->
    <div class="container mt-5">
      <h2 class="section-title text-center">Key Features</h2>
      <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12 feature-box">
          <div class="feature-icon">🤖</div>
          <h4>AI-Powered Announcements</h4>
          <p>Generate announcements <b>instantly</b> using <b>AI text generation</b> and <b>voice recognition</b> for seamless automation.</p>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 feature-box">
          <div class="feature-icon">📩</div>
          <h4>Instant SMS & Email Delivery</h4>
          <p>Receive announcements via <b>SMS and email</b> without delays, ensuring <b>real-time updates</b> for recipients.</p>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 feature-box">
          <div class="feature-icon">📋</div>
          <h4>Recipient Management</h4>
          <p>Easily <b>manage, organize, and categorize recipients</b> for targeted and efficient announcement distribution.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <footer class="footer text-center py-4">
    <div class="container">
      <div class="social-links">
        <a href="#">
          <img src="images/SPA AI.png" alt="SPA AI - Smart Personal Assistant" class="logo-animate" id="back-to-top">
        </a>
        <a href="<?= htmlspecialchars($socialLinks['facebook'] ?? '#') ?>" target="_blank" class="social-icon">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="<?= htmlspecialchars($socialLinks['messenger'] ?? '#') ?>" target="_blank" class="social-icon">
          <i class="fab fa-facebook-messenger"></i>
        </a>
        <a href="<?= htmlspecialchars($socialLinks['website'] ?? '#') ?>" target="_blank" class="social-icon">
          <i class="fas fa-globe"></i>
        </a>
      </div>
      <p><b>© 2025 Smart Personal Assistant A.I, All rights reserved.</b></p>
    </div>
  </footer>




  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    document.getElementById("back-to-top").addEventListener("click", function(event) {
      event.preventDefault(); // Prevent default anchor behavior
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      }); // Smooth scroll to top
    });
    document.querySelector('.nav-link[href="#about"]').addEventListener("click", function(event) {
      event.preventDefault(); // Prevent default anchor behavior

      let aboutSection = document.querySelector("#about");
      let navbarHeight = document.querySelector(".navbar").offsetHeight; // Get navbar height

      // Smooth scroll with an offset
      window.scrollTo({
        top: aboutSection.offsetTop - navbarHeight - -10, // Adjusting with extra spacing
        behavior: "smooth"
      });
    });
  </script>


</body>

</html>