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

// Get recent zoom links
$stmt_links = $pdo->query("SELECT * FROM zoom_links ORDER BY posted_at DESC LIMIT 5");
$links = $stmt_links->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $student['first_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide/dist/umd/lucide.js"></script>
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 70px;
        }
        body { background: #040911; display: flex; min-height: 100vh; }
        
        /* Sidebar */
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
        .sidebar-brand {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-menu { padding: 20px 15px; flex-grow: 1; }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background: rgba(21,101,192,0.1);
            color: var(--blue-light);
        }
        .sidebar-item i { width: 18px; }
        
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
        }
        
        .lms-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        /* Stats */
        .lms-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-box {
            background: var(--navy);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
            overflow: hidden;
        }
        .stat-box::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--blue);
        }
        .stat-box.gold::after { background: var(--gold); }
        .stat-num { font-family: 'Bebas Neue'; font-size: 32px; color: white; margin-bottom: 5px; }
        .stat-txt { font-size: 12px; color: rgba(255,255,255,0.4); text-transform: uppercase; font-weight: 700; }

        /* Learning Path */
        .learning-path { margin-bottom: 40px; }
        .module-card {
            background: var(--navy);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .module-icon {
            width: 50px; height: 50px; background: rgba(66,165,245,0.1); border-radius: 10px;
            display: flex; align-items: center; justify-content: center; color: var(--blue-light);
        }
        .progress-bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; margin-top: 10px; flex-grow: 1; position: relative; }
        .progress-fill { position: absolute; height: 100%; background: var(--blue-light); border-radius: 3px; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        
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
            .lms-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .lms-header div:last-child {
                text-align: left !important;
            }
            .mobile-header-bar {
                display: flex !important;
            }
            .lms-stats {
                grid-template-columns: 1fr 1fr;
            }
            .main-grid {
                grid-template-columns: 1fr !important;
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
    <div class="nav-logo">
        <div class="nav-logo-circle" style="width:32px; height:32px; font-size:7px;">The<br>Tank</div>
        <div style="display:flex; flex-direction:column;">
            <div class="nav-logo-text" style="font-size:12px; opacity:0.6; margin-bottom:-4px;">STUDENT</div>
            <div class="nav-logo-text" style="font-size:16px;"><span>DASHBOARD</span></div>
        </div>
    </div>
    <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer; padding:8px;">
        <i data-lucide="menu" style="width:28px; height:28px;"></i>
    </button>
</div>

<div class="sidebar" id="profileSidebar">
    <div class="sidebar-brand">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="index.php" class="nav-logo">
                <div class="nav-logo-circle">The<br>Tank</div>
                <div class="nav-logo-text">Student <span>Portal</span></div>
            </a>
            <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer; display:none;" class="mobile-only">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a href="profile.php" class="sidebar-item active"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="courses.php" class="sidebar-item"><i data-lucide="book-open"></i> My Courses</a>
        <a href="mentorship.php" class="sidebar-item"><i data-lucide="video"></i> Live Mentorship</a>
        <a href="resources.php" class="sidebar-item"><i data-lucide="folder"></i> Resources</a>
        <a href="#" class="sidebar-item"><i data-lucide="users"></i> Community</a>
        <a href="#" class="sidebar-item"><i data-lucide="award"></i> Certifications</a>
        <div style="margin: 20px 0 10px 15px; font-size: 10px; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px;">Account</div>
        <a href="#" class="sidebar-item"><i data-lucide="settings"></i> Settings</a>
    </div>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-item" style="color: #f44336;"><i data-lucide="log-out"></i> Sign Out</a>
    </div>
</div>

<div class="main-content">
    <div class="lms-header">
        <div style="display:flex; align-items:center; gap:25px;">
            <div style="width:70px; height:70px; background:linear-gradient(135deg, var(--blue), #1a237e); border-radius:16px; display:flex; align-items:center; justify-content:center; font-weight:900; border:2px solid var(--gold); font-size:28px; color:white; font-family:'Bebas Neue'; box-shadow: 0 4px 15px rgba(255,193,7,0.2);">
                <?php echo strtoupper($student['first_name'][0]); ?>
            </div>
            <div>
                <h1 style="font-family:'Bebas Neue'; font-size:38px; margin-bottom:0; line-height:1;">WELCOME BACK, <span style="color:var(--gold);"><?php echo strtoupper($student['first_name']); ?></span></h1>
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:20px; margin-top:10px;">
                    <div style="font-size:13px; color:rgba(255,255,255,0.6); display:flex; align-items:center; gap:6px;">
                        <i data-lucide="mail" style="width:14px; opacity:0.7;"></i> <?php echo $student['email']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
             <div style="font-size:11px; color:var(--gold); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">Innovator Status</div>
             <div style="background:rgba(21,101,192,0.2); padding:5px 15px; border-radius:20px; border:1px solid var(--blue); color:var(--blue-light); font-size:11px; font-weight:700;">
                MEMBER
             </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="lms-stats">
        <div class="stat-box">
            <div class="stat-num">45%</div>
            <div class="stat-txt">Course Progress</div>
        </div>
        <div class="stat-box gold">
            <div class="stat-num">12</div>
            <div class="stat-txt">Sessions Attended</div>
        </div>
        <div class="stat-box">
            <div class="stat-num">08</div>
            <div class="stat-txt">Assignments Done</div>
        </div>
        <div class="stat-box gold">
            <div class="stat-num">Rank #3</div>
            <div class="stat-txt">Leaderboard</div>
        </div>
    </div>

    <div class="main-grid" style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
        <!-- Learning Track -->
        <div>
            <div class="learning-path">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="font-family:'Bebas Neue'; font-size:28px; color:white;">LATEST <span style="color:var(--gold);">COURSES</span></h2>
                    <a href="courses.php" style="color:var(--blue-light); font-size:13px; font-weight:700; text-decoration:none;">View All Courses</a>
                </div>

                <?php
                $stmt_path = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 3");
                $path_courses = $stmt_path->fetchAll();
                
                if (empty($path_courses)): ?>
                    <p style="color:rgba(255,255,255,0.3); font-size:13px;">No courses available yet.</p>
                <?php endif; ?>

                <?php foreach ($path_courses as $pc): ?>
                <div class="module-card">
                    <div class="module-icon"><i data-lucide="<?php 
                        echo $pc['category'] == 'FINANCE' ? 'dollar-sign' : ($pc['category'] == 'STRATEGY' ? 'cpu' : 'rocket'); 
                    ?>"></i></div>
                    <div style="flex-grow:1;">
                        <h4 style="font-size:16px; margin-bottom:5px;"><?php echo $pc['title']; ?></h4>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <div class="progress-bar"><div class="progress-fill" style="width:0%;"></div></div>
                            <span style="font-size:12px; font-weight:700; color:rgba(255,255,255,0.3);">0%</span>
                        </div>
                    </div>
                    <?php if ($pc['zoom_link']): ?>
                        <a href="<?php echo $pc['zoom_link']; ?>" target="_blank" style="padding:4px 8px; font-size:10px; background:#4CAF50; color:white; border-radius:4px; text-decoration:none; font-weight:700;">LIVE</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div>
            <!-- Live Feed -->
            <div class="card" style="padding:25px; border-color:var(--blue);">
                <h3 style="font-family:'Barlow Condensed'; font-size:20px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                    <span style="display:block; width:8px; height:8px; background:#f44336; border-radius:50%; animation: pulse 2s infinite;"></span>
                    LIVE SESSIONS
                </h3>

                <?php if (empty($links)): ?>
                    <p style="color:rgba(255,255,255,0.3); font-size:13px; text-align:center;">No active sessions.</p>
                <?php else: ?>
                    <?php foreach ($links as $link): ?>
                        <div style="margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.05); last-child:border-none;">
                            <div style="font-weight:700; color:white; font-size:14px; margin-bottom:8px;"><?php echo $link['topic']; ?></div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:11px; color:rgba(255,255,255,0.4);"><?php echo date('g:i A', strtotime($link['posted_at'])); ?></span>
                                <a href="<?php echo $link['link']; ?>" target="_blank" style="color:var(--gold); font-size:12px; font-weight:700; text-decoration:none;">JOIN NOW &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Announcements -->
            <div class="card" style="padding:25px; margin-top:20px; background:rgba(255,193,7,0.02);">
                <h3 style="font-family:'Barlow Condensed'; font-size:20px; margin-bottom:15px; color:var(--gold);">FLASH NEWS</h3>
                <div style="font-size:13px; color:rgba(255,255,255,0.6); line-height:1.6;">
                    <p>Pitch Night is coming up! Ensure your slides are submitted by Friday midnight.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
    
    function toggleSidebar() {
        document.getElementById('profileSidebar').classList.toggle('active');
    }
</script>

<style>
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

</body>
</html>
