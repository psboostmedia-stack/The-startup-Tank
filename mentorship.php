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

// Fetch general live sessions (from zoom_links table)
$stmt_links = $pdo->query("SELECT * FROM zoom_links ORDER BY posted_at DESC LIMIT 10");
$live_sessions = $stmt_links->fetchAll();

// Fetch course-specific live classes (courses with zoom links)
$stmt_course_live = $pdo->query("SELECT * FROM courses WHERE zoom_link IS NOT NULL AND zoom_link != '' ORDER BY created_at DESC");
$course_live = $stmt_course_live->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Mentorship - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide/dist/umd/lucide.js"></script>
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
        .sidebar-item i { width: 18px; }
        
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 40px; }
        
        .live-card {
            background: var(--navy);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .live-card:hover { border-color: #4caf50; transform: scale(1.01); }
        .live-badge {
            background: #f44336;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
        }
        .pulse {
            width: 8px; height: 8px; background: white; border-radius: 50%;
            animation: pulse-animation 1.5s infinite;
        }
        @keyframes pulse-animation { 0% { opacity:1; } 50% { opacity:0.3; } 100% { opacity:1; } }

        .session-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }

        @media (max-width: 900px) {
            .sidebar { 
                width: var(--sidebar-width); 
                left: -100%; 
                transition: left 0.3s ease;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content { 
                margin-left: 0; 
                padding: 20px;
                padding-top: 80px;
            }
            .mobile-header-bar {
                display: flex !important;
            }
            .session-grid {
                grid-template-columns: 1fr;
            }
            .live-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .live-card .btn-primary {
                width: 100%;
                justify-content: center;
            }
        }

        .mobile-header-bar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #060e1c;
            padding: 0 20px;
            align-items: center;
            justify-content: space-between;
            z-index: 99;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
    </style>
</head>
<body>

<div class="mobile-header-bar">
    <div class="nav-logo" style="margin-bottom: 40px; padding: 0 10px;">
        <div class="nav-logo-circle" style="width:42px; height:42px; padding: 2px;">
            <span class="the-text" style="font-size: 6px; margin-left: 6px;">The</span>
            <span class="startup-text" style="font-size: 8px;">Startup</span>
            <span class="tank-text" style="font-size: 11px;">Tank</span>
        </div>
        <div style="display:flex; flex-direction:column;">
            <div class="nav-logo-text" style="font-size:12px; opacity:0.6; margin-bottom:-4px;">STUDENT</div>
            <div class="nav-logo-text" style="font-size:16px;"><span>MENTORSHIP</span></div>
        </div>
    </div>
    <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer; padding:8px;">
        <i data-lucide="menu" style="width:28px; height:28px;"></i>
    </button>
</div>

<div class="sidebar" id="mentorshipSidebar">
    <div class="sidebar-brand">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="index.php" class="nav-logo">
                <div class="nav-logo-circle" style="width:38px; height:38px; padding: 2px;">
                    <span class="the-text" style="font-size: 5px; margin-left: 5px;">The</span>
                    <span class="startup-text" style="font-size: 7px;">Startup</span>
                    <span class="tank-text" style="font-size: 10px;">Tank</span>
                </div>
                <div class="nav-logo-text">Student <span>Portal</span></div>
            </a>
            <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer; display:none;" class="mobile-only">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a href="profile.php" class="sidebar-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="courses.php" class="sidebar-item"><i data-lucide="book-open"></i> My Courses</a>
        <a href="mentorship.php" class="sidebar-item active"><i data-lucide="video"></i> Live Mentorship</a>
        <a href="resources.php" class="sidebar-item"><i data-lucide="folder"></i> Resources</a>
        <a href="#" class="sidebar-item"><i data-lucide="users"></i> Community</a>
        <a href="logout.php" class="sidebar-item" style="color: #f44336; margin-top: auto;"><i data-lucide="log-out"></i> Sign Out</a>
    </div>
</div>

<div class="main-content">
    <div style="margin-bottom: 40px;">
        <h1 style="font-family:'Bebas Neue'; font-size:42px; margin-bottom:5px;">LIVE <span style="color:var(--gold);">MENTORSHIP</span></h1>
        <p style="color:rgba(255,255,255,0.5); font-size:14px;">Join live classes, workshops, and coaching sessions.</p>
    </div>

    <!-- General Live Sessions -->
    <h2 style="font-family:'Barlow Condensed'; font-size:24px; color:var(--blue-light); margin-bottom:20px;">UPCOMING SESSIONS</h2>
    <div class="session-grid">
        <?php if (empty($live_sessions)): ?>
            <div style="background:var(--navy); padding:40px; border-radius:16px; border:1px dashed rgba(255,255,255,0.1); text-align:center; grid-column: 1/-1;">
                <p style="color:rgba(255,255,255,0.3);">No general sessions scheduled at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($live_sessions as $s): ?>
                <div class="live-card">
                    <div>
                        <div class="live-badge" style="margin-bottom:10px;"><span class="pulse"></span> LIVE NOW</div>
                        <h3 style="font-family:'Barlow Condensed'; font-size:22px; color:white; margin-bottom:5px;"><?php echo $s['topic']; ?></h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.5);"><?php echo date('d M Y • g:i A', strtotime($s['posted_at'])); ?></p>
                    </div>
                    <a href="<?php echo $s['link']; ?>" target="_blank" class="btn-primary" style="background:#4caf50; border:none; padding:12px 24px;">Join Session</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Course Specific Live Classes -->
    <h2 style="font-family:'Barlow Condensed'; font-size:24px; color:var(--gold); margin:40px 0 20px;">COURSE LIVE CLASSES</h2>
    <div class="session-grid">
        <?php if (empty($course_live)): ?>
            <div style="background:var(--navy); padding:40px; border-radius:16px; border:1px dashed rgba(255,255,255,0.1); text-align:center; grid-column: 1/-1;">
                <p style="color:rgba(255,255,255,0.3);">No course-specific live classes currently active.</p>
            </div>
        <?php else: ?>
            <?php foreach ($course_live as $c): ?>
                <div class="live-card" style="border-left: 4px solid var(--gold);">
                    <div>
                        <div style="font-size:10px; font-weight:800; color:var(--gold); text-transform:uppercase; margin-bottom:5px;"><?php echo $c['category']; ?> COURSE</div>
                        <h3 style="font-family:'Barlow Condensed'; font-size:22px; color:white; margin-bottom:5px;"><?php echo $c['title']; ?></h3>
                        <p style="font-size:13px; color:rgba(255,255,255,0.5);">Instructor: <?php echo $c['instructor']; ?></p>
                    </div>
                    <a href="<?php echo $c['zoom_link']; ?>" target="_blank" class="btn-primary" style="background:var(--blue); border:none; padding:12px 24px;">Join Class</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
    function toggleSidebar() {
        document.getElementById('mentorshipSidebar').classList.toggle('active');
    }
</script>
</body>
</html>
