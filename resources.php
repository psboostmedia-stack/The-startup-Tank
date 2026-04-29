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
    <title>Resources - The Startup Tank</title>
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
        
        .resource-item {
            background: var(--navy); padding: 20px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 15px; transition: border-color 0.2s;
        }
        .resource-item:hover { border-color: var(--blue-light); }
        .resource-info { display: flex; align-items: center; gap: 20px; }
        .res-icon { width: 45px; height: 45px; background: rgba(255,255,255,0.03); border-radius: 8px; display: flex; align-items: center; justify-content: center; }

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
        <a href="courses.php" class="sidebar-item"><i data-lucide="book-open"></i> My Courses</a>
        <a href="mentorship.php" class="sidebar-item"><i data-lucide="video"></i> Live Mentorship</a>
        <a href="resources.php" class="sidebar-item active"><i data-lucide="folder"></i> Resources</a>
        <a href="#" class="sidebar-item"><i data-lucide="users"></i> Community</a>
        <a href="logout.php" class="sidebar-item" style="color: #f44336; margin-top: auto;"><i data-lucide="log-out"></i> Sign Out</a>
    </div>
</div>

<div class="main-content">
    <div style="margin-bottom: 40px;">
        <h1 style="font-family:'Bebas Neue'; font-size:42px; margin-bottom:5px;">LEARNING <span style="color:var(--gold);">RESOURCES</span></h1>
        <p style="color:rgba(255,255,255,0.5); font-size:14px;">Curated templates, guides, and toolkits for your startup.</p>
    </div>

    <div style="max-width: 800px;">
        <?php
        $stmt_cats = $pdo->query("SELECT DISTINCT category FROM resources ORDER BY category ASC");
        $categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($categories)): ?>
            <div style="text-align: center; padding: 60px; background: var(--navy); border-radius: 16px; border: 1px dashed rgba(255,255,255,0.1);">
                <p style="color: rgba(255,255,255,0.5);">No resources available yet. Check back soon!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($categories as $cat): ?>
            <h2 style="font-family:'Barlow Condensed'; font-size:24px; margin:40px 0 20px; color:var(--blue-light);"><?php echo $cat; ?></h2>
            
            <?php
            $stmt_r = $pdo->prepare("SELECT * FROM resources WHERE category = ? ORDER BY title ASC");
            $stmt_r->execute([$cat]);
            $res_items = $stmt_r->fetchAll();
            
            foreach ($res_items as $item): ?>
                <div class="resource-item">
                    <div class="resource-info">
                        <div class="res-icon">
                            <i data-lucide="<?php 
                                echo $cat == 'FINANCE' ? 'bar-chart-2' : ($cat == 'LEGAL' ? 'shield' : 'file-text'); 
                            ?>" style="color:<?php echo $cat == 'TEMPLATES' ? 'var(--gold)' : 'var(--blue-light)'; ?>;"></i>
                        </div>
                        <div>
                            <h4 style="font-size:16px; margin-bottom:4px;"><?php echo $item['title']; ?></h4>
                            <span style="font-size:11px; color:rgba(255,255,255,0.4);"><?php echo $item['file_type']; ?> • Added <?php echo date('M Y', strtotime($item['created_at'])); ?></span>
                        </div>
                    </div>
                    <a href="<?php echo $item['file_url']; ?>" target="_blank" class="btn-secondary" style="padding:8px 16px; font-size:12px;">Access</a>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
