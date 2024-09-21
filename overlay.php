<?php
// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once 'connection.php'; // Assuming this file sets up $pdo

// Check if 'id' is passed in the query string
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize the ID

    // Prepare and execute the query to fetch project details by ID
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the project data
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo "Project not found.";
        exit;
    }

    // Fetch the associated media for the project
    $mediaStmt = $pdo->prepare("SELECT media_file, media_type, thumbnail, position FROM media WHERE project_id = :project_id ORDER BY position ASC");
    $mediaStmt->bindParam(':project_id', $id, PDO::PARAM_INT);
    $mediaStmt->execute();
    $mediaItems = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch similar projects based on category or other criteria
    $similarStmt = $pdo->prepare("SELECT * FROM projects WHERE category = :category AND id != :id LIMIT 3");
    $similarStmt->bindParam(':category', $project['category'], PDO::PARAM_STR);
    $similarStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $similarStmt->execute();
    $similarProjects = $similarStmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo "No project ID specified.";
    exit;
}

// Function to safely escape values or return an empty string if null
function safe_escape($value) {
    return $value !== null ? htmlspecialchars($value) : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_escape($project['title']); ?> - Portfolio</title>

    <!-- External CSS -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="shortcut icon" href="./assets/images/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Your CSS for the overlaypage -->
    <style>
        
.modalover-container {
    position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 20;
  pointer-events: none;
  visibility: hidden;
  overflow-y: auto;
  
}

.modalover-container.active {
  pointer-events: all;
  visibility: visible;
}

.overlaypage {
    position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  backdrop-filter: blur(10px); /* Blur the background */
  background: rgba(0, 0, 0, 0.3); /* Darken the background */
  opacity: 1;
  visibility: hidden;
  pointer-events: none;
  z-index: 1;
  transition: var(--transition-1);
}

.overlaypage.active {
  visibility: visible;
  pointer-events: all;
}

.testimonials-modalover {
    background: var(--eerie-black-2);
  position: relative;
  padding: 15px;
  border: 1px solid var(--jet);
  border-radius: 14px;
  box-shadow: var(--shadow-5);
  transform: scale(1.2);
  opacity: 0;
  transition: var(--transition-1);
  z-index: 2;
  width: 60%; /* Fixed width */
  max-width: 800px; /* Limit max width */
  max-height: 90vh; /* Keep the modal within viewport */
  margin: 10vh auto;
  overflow-y: auto; /* Enable internal scrolling */
  
  
}

.modalover-container.active .testimonials-modalover {
  transform: scale(1);
  opacity: 1;
}

.modalover-close-btn {
  position: absolute;
  top: 1%;
  right: 15px;
  background: var(--onyx);
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: var(--white-2);
  font-size: 18px;
  opacity: 0.7;
}

.modalover-close-btn:hover,
.modalover-close-btn:focus {
  opacity: 1;
}

.modalover-close-btn ion-icon {
  --ionicon-stroke-width: 50px;
}

.modalover-title {
  margin-bottom: 4px;
}

.project-gallery img,
.project-gallery video {
  max-width: 100%;
  justify-content: center;
  ;
}

.modalover-content {
  padding: 15px;
  justify-content: center;
}
    </style>
</head>
<body>

<!-- modalover Container -->
<div class="modalover-container" id="modaloverContainer">
    <div class="testimonials-modalover">
        <button class="modalover-close-btn" id="closemodalover">&times;</button>

        <div class="project-details">
            <h1 class="h2"><?php echo safe_escape($project['title']); ?></h1>
            <p class="subtitle"><?php echo safe_escape($project['subtitle']); ?></p>
        </div>

        <!-- Project gallery (images/videos) -->
        <div class="project-gallery">
            <?php foreach ($mediaItems as $media): ?>
                <?php 
                $fileExtension = pathinfo($media['media_file'], PATHINFO_EXTENSION);
                $isVideo = in_array($fileExtension, ['mp4', 'avi', 'mkv', 'mov']); 
                ?>
                <?php if ($isVideo): ?>
                    <video controls>
                        <source src="<?php echo safe_escape($media['media_file']); ?>" type="video/<?php echo $fileExtension; ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <img src="<?php echo safe_escape($media['media_file']); ?>" alt="Project Media">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Similar Projects -->
        <div class="similar-projects">
            <h2>Similar Projects</h2>
            <?php foreach ($similarProjects as $similar): ?>
                <div class="project-card">
                    <a href="overlaypage.php?id=<?php echo safe_escape($similar['id']); ?>">
                        <img src="<?php echo safe_escape($similar['thumbnail']); ?>" alt="<?php echo safe_escape($similar['title']); ?>">
                        <h3><?php echo safe_escape($similar['title']); ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="overlaypage" id="overlaypage"></div>
</div>

<script>
    // JavaScript to handle the modalover opening and closing

    const modaloverContainer = document.getElementById('modaloverContainer');
    const overlaypage = document.getElementById('overlaypage');
    const closemodaloverButton = document.getElementById('closemodalover');

    function openmodalover() {
        modaloverContainer.classList.add('active');
        overlaypage.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closemodalover() {
        modaloverContainer.classList.remove('active');
        overlaypage.classList.remove('active');
        document.body.style.overflow = ''; // Restore background scrolling
    }

    closemodaloverButton.addEventListener('click', closemodalover);
    overlaypage.addEventListener('click', closemodalover);

    // Open the modalover when the page loads
    window.onload = function() {
        openmodalover();
    };
</script>

</body>
</html>
