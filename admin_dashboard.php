<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Zoom Link Posting
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_zoom'])) {
    $topic = $_POST['topic'];
    $link = $_POST['link'];

    $stmt = $pdo->prepare("INSERT INTO zoom_links (topic, link) VALUES (?, ?)");
    if ($stmt->execute([$topic, $link])) {
        $msg = "Zoom link posted successfully!";
    }
}

// Fetch students
try {
    // Migration: Auto-approve all students since approval system is disabled
    $pdo->exec("UPDATE students SET status = 'approved' WHERE status IS NULL OR status = '' OR status = 'pending'");

    $pending_students = []; // No more pending students
    
    $stmt_approved = $pdo->query("SELECT * FROM students WHERE status = 'approved' ORDER BY created_at DESC");
    $approved_students = $stmt_approved->fetchAll();

    $stmt_rejected_count = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'rejected'");
    $rejected_count = $stmt_rejected_count->fetchColumn();
} catch (Exception $e) {
    $pending_students = [];
    $approved_students = [];
    $rejected_count = 0;
}

// Fetch links for reference
try {
    $stmt_links = $pdo->query("SELECT * FROM zoom_links ORDER BY posted_at DESC LIMIT 10");
    $recent_links = $stmt_links->fetchAll();
} catch (Exception $e) { $recent_links = []; }

// Table creation for courses (if not exists)
$pdo->exec("CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructor VARCHAR(255),
    category VARCHAR(100),
    image_url VARCHAR(255),
    zoom_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle Course Posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_course'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $instructor = $_POST['instructor'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'] ?: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&q=80';
    $zoom_link = $_POST['zoom_link'];

    $stmt = $pdo->prepare("INSERT INTO courses (title, description, instructor, category, image_url, zoom_link) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $instructor, $category, $image_url, $zoom_link])) {
        $msg = "Course created successfully!";
    }
}

// Fetch courses
$stmt_courses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
$courses = $stmt_courses->fetchAll();

// Table creation for resources (if not exists)
$pdo->exec("CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    file_url VARCHAR(255),
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle Resource Posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_resource'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $file_url = $_POST['file_url'];
    $file_type = $_POST['file_type'];

    $stmt = $pdo->prepare("INSERT INTO resources (title, category, file_url, file_type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $category, $file_url, $file_type])) {
        $msg = "Resource added successfully!";
    }
}

// Fetch resources
$stmt_res = $pdo->query("SELECT * FROM resources ORDER BY category ASC");
$admin_resources = $stmt_res->fetchAll();

// Fetch enrollments (Startup Tank 2.0)
try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        class_year VARCHAR(100) NOT NULL,
        stream VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        idea TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $stmt_enrollments = $pdo->query("SELECT * FROM enrollments ORDER BY created_at DESC");
    $enrollments = $stmt_enrollments->fetchAll();
} catch (Exception $e) {
    $enrollments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Startup Tank</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
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
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
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
            width: calc(100% - var(--sidebar-width));
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .tab-content { display: none; animation: fadeIn 0.4s ease; }
        .tab-content.active { display: block; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--navy); padding: 25px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden; }
        .stat-card::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--blue); }
        .stat-val { font-family: 'Bebas Neue'; font-size: 32px; color: white; margin-bottom: 5px; }
        .stat-label { font-size: 12px; color: rgba(255,255,255,0.4); text-transform: uppercase; font-weight: 700; }

        /* Enhanced Table Design */
        .table-container {
            background: #081220;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: rgba(255,255,255,0.8);
        }
        
        thead th {
            background: rgba(21,101,192,0.1);
            color: var(--blue-light);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 18px 20px;
            text-align: left;
            border-bottom: 2px solid rgba(255,255,255,0.05);
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        
        tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        
        tbody tr:last-child {
            border-bottom: none;
        }
        
        tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }
        
        tbody tr:nth-child(even) {
            background: rgba(255,255,255,0.01);
        }
        
        tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 14px;
        }

        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #2e7d32;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
            border: 1px solid #388e3c;
        }
        
        .export-btn:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76,175,80,0.2);
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-brand span, .sidebar-item span, .sidebar-footer span { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
        }

        @media (max-width: 768px) {
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
                width: 100%; 
                padding: 20px;
                padding-top: 80px;
            }
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .mobile-admin-bar {
                display: flex !important;
            }
            .table-container {
                overflow-x: auto;
            }
            table {
                min-width: 800px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .mobile-admin-bar {
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

<div class="mobile-admin-bar">
    <div class="nav-logo">
        <div class="nav-logo-circle" style="width:32px; height:32px; font-size:7px;">The<br>Tank</div>
        <div class="nav-logo-text" style="font-size:16px;">Admin <span>Portal</span></div>
    </div>
    <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer;">
        <i data-lucide="menu"></i>
    </button>
</div>

<div class="sidebar" id="adminSidebar">
    <div class="sidebar-brand">
         <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="index.php" class="nav-logo" style="text-decoration:none;">
                <div class="nav-logo-circle">The<br>Tank</div>
                <div class="nav-logo-text">Admin <span>Portal</span></div>
            </a>
            <button class="mobile-only" onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer; display:none;">
                <i data-lucide="x"></i>
            </button>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <button onclick="switchTab(event, 'tab-enrollments')" class="sidebar-item active">
            <i data-lucide="rocket"></i> <span>Tank 2.0 Entries</span>
        </button>
        <button onclick="switchTab(event, 'tab-accounts')" class="sidebar-item">
            <i data-lucide="users"></i> <span>Student Registrations</span>
        </button>
        <button onclick="switchTab(event, 'tab-courses')" class="sidebar-item">
            <i data-lucide="book-open"></i> <span>Course Manager</span>
        </button>
        <button onclick="switchTab(event, 'tab-resources')" class="sidebar-item">
            <i data-lucide="folder"></i> <span>Resource Hub</span>
        </button>
        <button onclick="switchTab(event, 'tab-zoom')" class="sidebar-item">
            <i data-lucide="video"></i> <span>Live Sessions</span>
        </button>
        
        <div style="margin: 20px 0 10px 15px; font-size: 10px; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px;">System</div>
        <a href="export_enrollments.php" class="sidebar-item"><i data-lucide="download"></i> <span>Export Data</span></a>
    </div>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-item" style="color: #f44336;"><i data-lucide="log-out"></i> <span>Sign Out</span></a>
    </div>
</div>

<div class="main-content">
    <div class="admin-header">
        <div>
            <h1 style="font-family:'Bebas Neue'; font-size:42px; margin-bottom:5px;">ADMIN <span style="color:var(--gold);">DASHBOARD</span></h1>
            <p style="color:rgba(255,255,255,0.5); font-size:14px;">Platform Overview & Management Control</p>
        </div>
        <div style="display:flex; align-items:center; gap:20px;">
            <div style="text-align:right;">
                <div style="font-weight:700; color:white;">Administrator</div>
                <div style="font-size:11px; color:var(--blue-light); font-weight:700; text-transform:uppercase;">Super Admin Access</div>
            </div>
            <div style="width:50px; height:50px; background:var(--navy); border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:900; border:1px solid rgba(255,255,255,0.1);">
                <i data-lucide="shield-check" style="color:var(--gold);"></i>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card" style="border-bottom: 3px solid #4CAF50;">
            <div class="stat-val" style="color: #4CAF50;"><?php echo count($enrollments); ?></div>
            <div class="stat-label">Tank 2.0 Enrollments</div>
        </div>
        <div class="stat-card" style="border-bottom: 3px solid var(--gold);">
            <div class="stat-val" style="color: var(--gold);"><?php echo count($approved_students); ?></div>
            <div class="stat-label">Active Students</div>
        </div>
        <div class="stat-card" style="border-bottom: 3px solid #ff9800;">
            <div class="stat-val" style="color: #ff9800;"><?php echo count($pending_students); ?></div>
            <div class="stat-label">Pending Approval</div>
        </div>
        <div class="stat-card" style="border-bottom: 3px solid var(--blue-light);">
            <div class="stat-val" style="color: var(--blue-light);"><?php echo count($courses); ?></div>
            <div class="stat-label">Active Courses</div>
        </div>
    </div>


     <!-- Tab 1: Enrollments -->
    <div id="tab-enrollments" class="tab-content active">
        <div class="card" style="border-top: 4px solid #4CAF50;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h2 style="font-family:'Bebas Neue'; font-size:32px; color:#4CAF50; margin-bottom:0;">STARTUP TANK 2.0 ENTRIES</h2>
                    <p style="font-size:12px; color:rgba(255,255,255,0.5);">Direct enrollments from the landing page form</p>
                </div>
                <div style="display:flex; gap:12px; align-items:center;">
                    <a href="export_enrollments.php" class="export-btn">
                        <i data-lucide="file-spreadsheet"></i>
                        <span>Export Excel (CSV)</span>
                    </a>
                    <span style="background:#4CAF50; padding:6px 16px; border-radius:100px; font-size:12px; font-weight:700; color:white;"><?php echo count($enrollments); ?> ENROLLED</span>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Class & Stream</th>
                            <th>Contact</th>
                            <th>Idea</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enrollments)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:50px; opacity:0.5;">
                                <i data-lucide="inbox" style="width:40px; height:40px; margin-bottom:10px; opacity:0.3;"></i>
                                <div style="font-size:16px; font-weight:600;">No Tank 2.0 Enrollments Found</div>
                                <div style="font-size:12px;">Data from the index page enrollment modal will appear here.</div>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($enrollments as $e): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:white;"><?php echo $e['full_name']; ?></div>
                                        <div style="font-size:11px; color:#4CAF50;"><?php echo $e['email']; ?></div>
                                    </td>
                                    <td style="font-size:13px;">
                                        <div><?php echo $e['class_year']; ?></div>
                                        <div style="color:rgba(255,255,255,0.4); font-size:11px;"><?php echo $e['stream']; ?></div>
                                    </td>
                                    <td style="font-size:13px;"><?php echo $e['phone']; ?></td>
                                    <td style="max-width:300px;">
                                        <div style="font-size:12px; line-height:1.4; color:rgba(255,255,255,0.7);">
                                            <?php echo strlen($e['idea'] ?? '') > 100 ? substr($e['idea'], 0, 100) . '...' : ($e['idea'] ?? 'No idea provided'); ?>
                                        </div>
                                        <?php if (strlen($e['idea'] ?? '') > 100): ?>
                                            <button onclick="alert('Full Idea: <?php echo addslashes($e['idea']); ?>')" style="background:none; border:none; color:#4CAF50; padding:0; cursor:pointer; font-size:11px; font-weight:600;">Show More</button>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:11px; color:rgba(255,255,255,0.4);">
                                        <?php echo date('d M Y, H:i', strtotime($e['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Accounts -->
    <div id="tab-accounts" class="tab-content">
        <div style="margin-bottom: 25px;">
            <div style="position:relative;">
                <input type="text" id="studentSearch" placeholder="Search accounts (name, email, institution)..." 
                       style="width:100%; padding: 15px 45px 15px 20px; background: var(--navy); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; color: white; outline: none; border-bottom: 2px solid var(--blue); font-size:14px;">
                <span style="position:absolute; right:20px; top:50%; transform:translateY(-50%); opacity:0.3;">🔍</span>
            </div>
        </div>

        <?php if (count($pending_students) > 0): ?>
        <div class="card" style="border-color: #ff9800; margin-bottom: 30px;">
            <h2 style="font-family:'Bebas Neue'; font-size:28px; color:#ff9800; margin-bottom:20px;">PENDING VERIFICATIONS</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Institution</th>
                            <th>Stream</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_students as $s): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:36px; height:36px; background:rgba(255,152,0,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-family:'Bebas Neue'; font-size:18px; color:#ff9800; border:1px solid #ff9800;">
                                            <?php echo strtoupper($s['first_name'][0]); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:700; color:white;"><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></div>
                                            <div style="font-size:10px; color:rgba(255,255,255,0.4);"><?php echo date('d M Y', strtotime($s['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:13px;"><?php echo $s['email']; ?></td>
                                <td style="font-size:12px;">
                                    <div style="font-weight:600;"><?php echo $s['institution']; ?></div>
                                    <div style="color:var(--gold); font-size:11px;"><?php echo strtoupper($s['student_type']); ?> (<?php echo $s['class_year']; ?>)</div>
                                </td>
                                <td style="font-size:12px; color:rgba(255,255,255,0.7);"><?php echo $s['stream'] ?: 'N/A'; ?></td>
                                <td>
                                    <a href="approve_student.php?id=<?php echo $s['id']; ?>" style="padding:4px 8px; font-size:10px; background:#4CAF50; border:none; color:white; text-decoration:none; border-radius:3px; font-weight:700;">Approve</a>
                                    <a href="reject_student.php?id=<?php echo $s['id']; ?>" style="padding:4px 8px; font-size:10px; background:#f44336; border:none; color:white; text-decoration:none; border-radius:3px; font-weight:700; margin-left:5px;" onclick="return confirm('Reject this student?')">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-family:'Bebas Neue'; font-size:28px; color:var(--blue-light);">REGISTERED STUDENT ACCOUNTS</h2>
                <div style="display:flex; gap:12px; align-items:center;">
                    <a href="export_students.php" class="export-btn" style="background:#1565C0; border-color:#1E88E5;">
                        <i data-lucide="download"></i>
                        <span>Export Students</span>
                    </a>
                    <span style="background:var(--gold); color:var(--navy); padding:6px 16px; border-radius:100px; font-size:12px; font-weight:700;"><?php echo count($approved_students); ?> ACTIVE</span>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approved_students)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:50px; opacity:0.5;">
                                <i data-lucide="users" style="width:40px; height:40px; margin-bottom:10px; opacity:0.3;"></i>
                                <div style="font-size:16px; font-weight:600;">No Registered Accounts Yet</div>
                                <div style="font-size:12px;">Students who register for a portal account will appear here.</div>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($approved_students as $s): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width:36px; height:36px; background:rgba(255,215,0,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-family:'Bebas Neue'; font-size:18px; color:var(--gold); border:1px solid rgba(255,215,0,0.3);">
                                                <?php echo strtoupper($s['first_name'][0] ?? 'S'); ?>
                                            </div>
                                            <div style="font-weight:700; color:white;"><?php echo ($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''); ?></div>
                                        </div>
                                    </td>
                                    <td style="font-size:13px; font-weight:600; color:#4CAF50;"><?php echo $s['phone'] ?? 'N/A'; ?></td>
                                    <td style="font-size:13px;"><?php echo $s['email'] ?? 'N/A'; ?></td>
                                    <td style="font-size:12px; color:rgba(255,255,255,0.4);"><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
                                    <td>
                                        <button onclick='openEditModal(<?php echo json_encode($s); ?>)' style="padding:6px 12px; font-size:11px; background:var(--gold); border:none; color:var(--navy); cursor:pointer; font-weight:700; border-radius:4px;">Edit Profile</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Courses -->
    <div id="tab-courses" class="tab-content">
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">
            <div>
                <div class="card">
                    <h2 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:20px; color:var(--blue-light);">CREATE NEW COURSE</h2>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Course Title</label>
                            <input type="text" name="title" placeholder="e.g. Finance Strategy" required minlength="5">
                        </div>
                        <div class="form-group">
                            <label>Instructor</label>
                            <input type="text" name="instructor" placeholder="e.g. John Doe" minlength="3">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="CORE">CORE</option>
                                <option value="FINANCE">FINANCE</option>
                                <option value="STRATEGY">STRATEGY</option>
                                <option value="MARKETING">MARKETING</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Zoom Meeting Link (Optional)</label>
                            <input type="url" name="zoom_link" placeholder="https://zoom.us/...">
                        </div>
                        <div class="form-group">
                            <label>Course Image URL</label>
                            <input type="url" name="image_url" placeholder="https://images.unsplash.com/...">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Briefly describe the course contents (min. 10 chars)..." minlength="10"></textarea>
                        </div>
                        <button type="submit" name="post_course" class="btn-primary" style="width:100%;">Create Course</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:15px; color:var(--gold);">ACTIVE COURSES</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Category</th>
                                <th>Instructor</th>
                                <th>Live Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($courses) == 0): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; opacity:0.5;">No courses created yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach($courses as $c): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:white;"><?php echo $c['title']; ?></div>
                                        <div style="font-size:11px; opacity:0.6;"><?php echo date('d M Y', strtotime($c['created_at'])); ?></div>
                                    </td>
                                    <td><span style="background:rgba(255,193,7,0.1); color:var(--gold); padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700;"><?php echo $c['category']; ?></span></td>
                                    <td style="font-size:13px;"><?php echo $c['instructor']; ?></td>
                                    <td>
                                        <?php if ($c['zoom_link']): ?>
                                            <a href="<?php echo $c['zoom_link']; ?>" target="_blank" style="color:var(--blue-light); font-size:11px;">Join Class</a>
                                        <?php else: ?>
                                            <span style="opacity:0.3; font-size:11px;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="delete_course.php?id=<?php echo $c['id']; ?>" style="color:#f44336; font-size:12px; text-decoration:none;" onclick="return confirm('Delete course?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Resources -->
    <div id="tab-resources" class="tab-content">
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">
            <div>
                <div class="card">
                    <h2 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:20px; color:var(--blue-light);">ADD RESOURCE</h2>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Resource Title</label>
                            <input type="text" name="title" placeholder="e.g. Pitch Deck Template" required minlength="5">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="TEMPLATES">TEMPLATES</option>
                                <option value="GUIDES">GUIDES</option>
                                <option value="FINANCE">FINANCE</option>
                                <option value="LEGAL">LEGAL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>File URL / Link</label>
                            <input type="text" name="file_url" placeholder="https://..." required>
                        </div>
                        <div class="form-group">
                            <label>File Type (Label)</label>
                            <input type="text" name="file_type" placeholder="e.g. PDF, XLSX, DOCX">
                        </div>
                        <button type="submit" name="post_resource" class="btn-primary" style="width:100%;">Add Resource</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:15px; color:var(--gold);">PLATFORM RESOURCES</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Category</th>
                                <th>File Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($admin_resources) == 0): ?>
                                <tr><td colspan="4" style="text-align:center; padding:30px; opacity:0.5;">No resources uploaded.</td></tr>
                            <?php endif; ?>
                            <?php foreach($admin_resources as $r): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:white;"><?php echo $r['title']; ?></div>
                                        <div style="font-size:11px; opacity:0.4;"><?php echo $r['file_url']; ?></div>
                                    </td>
                                    <td><span style="font-size:10px; font-weight:700; color:var(--blue-light);"><?php echo $r['category']; ?></span></td>
                                    <td style="font-size:12px;"><?php echo $r['file_type']; ?></td>
                                    <td>
                                        <a href="delete_resource.php?id=<?php echo $r['id']; ?>" style="color:#f44336; font-size:12px; text-decoration:none;" onclick="return confirm('Delete resource?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Zoom -->
    <div id="tab-zoom" class="tab-content">
        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">
            <div>
                <div class="card">
                    <h2 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:20px; color:var(--gold);">POST NEW SESSION</h2>
                    <?php if ($msg): ?>
                        <p style="color: #4CAF50; margin-bottom: 15px; font-weight: 600;"><?php echo $msg; ?></p>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Session Topic</label>
                            <input type="text" name="topic" placeholder="e.g. Masterclass: AI in Marketing" required minlength="5">
                        </div>
                        <div class="form-group">
                            <label>Zoom / Meeting Link</label>
                            <input type="url" name="link" placeholder="https://zoom.us/j/..." required>
                        </div>
                        <button type="submit" name="post_zoom" class="btn-primary" style="width:100%;">Create Live Link</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3 style="font-family:'Bebas Neue'; font-size:28px; margin-bottom:15px; color:var(--blue-light);">RECENTLY POSTED LINKS</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Link</th>
                                <th>Date Posted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_links as $rl): ?>
                                <tr>
                                    <td style="font-weight:700; color:white;"><?php echo $rl['topic']; ?></td>
                                    <td><a href="<?php echo $rl['link']; ?>" target="_blank" style="color:var(--gold); font-size:12px;">Open Meeting</a></td>
                                    <td style="font-size:12px; color:rgba(255,255,255,0.4);"><?php echo date('d M Y, H:i', strtotime($rl['posted_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div id="edit_avatar" style="width:50px; height:50px; background:var(--blue); border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:900; border:2px solid var(--gold); color:white; font-family:'Bebas Neue'; font-size:24px;"></div>
                <div>
                    <h2 style="font-family:'Bebas Neue'; font-size:28px; color:var(--gold); margin-bottom:0;">EDIT STUDENT</h2>
                    <p id="edit_subtitle" style="font-size:11px; color:rgba(255,255,255,0.5); margin:0;">Platform User Record</p>
                </div>
            </div>
            <button onclick="closeEditModal()" style="background:transparent; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <form action="update_student.php" method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="edit_first_name" required minlength="2">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="edit_last_name" required minlength="2">
                </div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label>Contact Number Number</label>
                <input type="text" name="phone" id="edit_phone">
            </div>
            <button type="submit" class="btn-primary" style="width:100%; border:none; background:var(--gold); color:var(--navy); font-weight:800;">Update Student Profile</button>
        </form>
    </div>
</div>



<script>
function switchTab(evt, tabId) {
    // Mobile: close sidebar on tab switch
    if (window.innerWidth <= 768) {
        document.getElementById('adminSidebar').classList.remove('active');
    }
    
    // Hide all tab content
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(c => c.classList.remove('active'));

    // Remove active class from all triggers
    const triggers = document.querySelectorAll('.sidebar-item');
    triggers.forEach(t => t.classList.remove('active'));

    // Show the current tab
    const targetTab = document.getElementById(tabId);
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Add active class to the sidebar item
    evt.currentTarget.classList.add('active');
    
    // Save tab preference
    localStorage.setItem('adminActiveTab', tabId);
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    // Initialize icons
    lucide.createIcons();

    // Load last tab on refresh
    const lastTab = localStorage.getItem('adminActiveTab') || 'tab-enrollments';
    const trigger = document.querySelector(`[onclick*="${lastTab}"]`);
    if (trigger) {
        trigger.click();
    }
});

function openEditModal(student) {
    if (window.innerWidth <= 768) {
        document.getElementById('adminSidebar').classList.remove('active');
    }
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_first_name').value = student.first_name || '';
    document.getElementById('edit_last_name').value = student.last_name || '';
    document.getElementById('edit_email').value = student.email || '';
    document.getElementById('edit_phone').value = student.phone || '';
    
    // Update modal avatar
    const avatar = document.getElementById('edit_avatar');
    avatar.textContent = student.first_name ? student.first_name.charAt(0).toUpperCase() : 'S';
    document.getElementById('edit_subtitle').textContent = `Account: ${student.email}`;
    
    document.getElementById('editModal').classList.add('active');
}



function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('active');
}

// Student Search Logic
document.getElementById('studentSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        let visibleRows = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(term)) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Optionally show/hide the entire section if no results, but simple row hiding is usually preferred
    });
});
</script>

</body>
</html>
