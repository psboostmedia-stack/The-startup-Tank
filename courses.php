<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student || $student['status'] !== 'approved') {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --sidebar-width: 260px; }
        body { background: #040911; display: flex; min-height: 100vh; }
        
        .sidebar {
            width: var(--sidebar-width);
            background: #060e1c;
            border-right: 1px solid rgba(255,255,255,0.05);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }
        .sidebar-brand { padding: 30px 25px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { padding: 20px 15px; flex-grow: 1; }
        .sidebar-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 15px;
            color: rgba(255,255,255,0.6); text-decoration: none; border-radius: 8px;
            margin-bottom: 5px; font-size: 14px; font-weight: 600; transition: all 0.2s;
        }
        .sidebar-item:hover, .sidebar-item.active { background: rgba(21,101,192,0.1); color: var(--blue-light); }
        
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 40px; }
        
        .course-card {
            background: var(--navy); border-radius: 16px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s;
            display: flex; flex-direction: column;
        }
        .course-card:hover { transform: translateY(-5px); border-color: var(--gold); }
        .course-img { height: 160px; background-size: cover; background-position: center; position: relative; }
        .course-badge { position: absolute; top: 15px; right: 15px; background: var(--blue); color: white; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 800; }
        .course-body { padding: 20px; flex-grow: 1; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

        @media (max-width: 900px) { .sidebar { display: none; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="nav-logo">
            <div class="nav-logo-circle">The<br>Tank</div>
            <div class="nav-logo-text">Student <span>Portal</span></div>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <a href="profile.php" class="sidebar-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="courses.php" class="sidebar-item active"><i data-lucide="book-open"></i> My Courses</a>
        <a href="mentorship.php" class="sidebar-item"><i data-lucide="video"></i> Live Mentorship</a>
        <a href="resources.php" class="sidebar-item"><i data-lucide="folder"></i> Resources</a>
        <a href="#" class="sidebar-item"><i data-lucide="users"></i> Community</a>
        <a href="logout.php" class="sidebar-item" style="color: #f44336; margin-top: auto;"><i data-lucide="log-out"></i> Sign Out</a>
    </div>
</div>

<div class="main-content">
    <div style="margin-bottom: 40px;">
        <h1 style="font-family:'Bebas Neue'; font-size:42px; margin-bottom:5px;">MY <span style="color:var(--gold);">COURSES</span></h1>
        <p style="color:rgba(255,255,255,0.5); font-size:14px;">Continue your entrepreneurial journey where you left off.</p>
    </div>

    <div class="grid">
        <?php
        $stmt_courses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
        $courses = $stmt_courses->fetchAll();
        
        if (empty($courses)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: var(--navy); border-radius: 16px; border: 1px dashed rgba(255,255,255,0.1);">
                <p style="color: rgba(255,255,255,0.5);">No courses are currently available. Check back soon!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($courses as $c): ?>
        <div class="course-card">
            <div class="course-img" style="background-image: url('<?php echo $c['image_url']; ?>');">
                <div class="course-badge"><?php echo $c['category']; ?></div>
            </div>
            <div class="course-body">
                <h3 style="font-family:'Barlow Condensed'; font-size:20px; margin-bottom:10px;"><?php echo $c['title']; ?></h3>
                <p style="font-size:13px; color:rgba(255,255,255,0.5); margin-bottom:20px;"><?php echo $c['description']; ?></p>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11px; color:var(--blue-light); font-weight:700;">By <?php echo $c['instructor'] ?: 'The Tank Mentor'; ?></span>
                    <?php if ($c['zoom_link']): ?>
                        <a href="<?php echo $c['zoom_link']; ?>" target="_blank" class="btn-primary" style="padding:8px 16px; font-size:12px; background: #4caf50;">Join Class</a>
                    <?php else: ?>
                        <a href="#" class="btn-primary" style="padding:8px 16px; font-size:12px;">Start Path</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
