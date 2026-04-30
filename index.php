<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Startup Tank - Ideas Today. Impact Tomorrow.</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide/dist/umd/lucide.js"></script>
    <style>
        /* Specific page adjustments if needed */
        .why { padding: 100px 5%; text-align: center; }
        .why-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 50px; }
        .why-card { background: rgba(255,255,255,0.03); padding: 40px; border-radius: 12px; border: 1px solid rgba(255,193,7,0.1); }
        .why-card h3 { color: var(--gold); margin-bottom: 15px; font-family: 'Barlow Condensed'; }
        .cta-bottom { padding: 120px 5%; text-align: center; background: linear-gradient(135deg, var(--navy), var(--blue)); }
        .status-banner { display: none; } /* Hidden by default, shown by PHP or JS */

        .menu-toggle i { width: 28px; height: 28px; }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">
        <div class="nav-logo-circle">
            <span class="the-text">The</span>
            <span class="startup-text">Startup</span>
            <span class="tank-text">Tank</span>
        </div>
        <div class="nav-logo-text">The <span>Startup</span> Tank</div>
    </a>
    <ul class="nav-links">
        <li><a href="#about">About</a></li>
        <li><a href="#why">Why Join</a></li>
        <li><a href="#how">How It Works</a></li>
        <li><a href="#programs">Programs</a></li>
        <li><a href="login.php" style="color: var(--gold);">Student Login</a></li>
        <li><a href="#" class="btn-primary" style="padding: 10px 20px; font-size: 13px;" onclick="openModal('registerModal')">Register Now</a></li>
    </ul>
    <button class="menu-toggle" onclick="toggleMobileMenu()">
        <i data-lucide="menu"></i>
    </button>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:24px 5%; border-bottom:1px solid rgba(255,255,255,0.05);">
        <div class="nav-logo">
            <div class="nav-logo-circle" style="width:40px; height:40px; padding: 2px;">
                <span class="the-text" style="font-size: 6px; margin-left: 4px;">The</span>
                <span class="startup-text" style="font-size: 8px;">Startup</span>
                <span class="tank-text" style="font-size: 11px;">Tank</span>
            </div>
            <div class="nav-logo-text" style="font-size:16px;">The <span>Startup</span> Tank</div>
        </div>
        <button onclick="toggleMobileMenu()" style="background:none; border:none; color:white; cursor:pointer;">
            <i data-lucide="x"></i>
        </button>
    </div>
    <ul class="mobile-nav-links">
        <li><a href="#about" onclick="toggleMobileMenu()">About</a></li>
        <li><a href="#why" onclick="toggleMobileMenu()">Why Join</a></li>
        <li><a href="#how" onclick="toggleMobileMenu()">How It Works</a></li>
        <li><a href="#programs" onclick="toggleMobileMenu()">Programs</a></li>
        <li><a href="login.php" style="color: var(--gold);">Student Login</a></li>
        <li style="margin-top:20px;"><a href="#" class="btn-primary" style="width:100%; justify-content:center;" onclick="toggleMobileMenu(); openModal('registerModal')">Register Now</a></li>
    </ul>
</div>

<?php if (isset($_SESSION['enrollment_success'])): ?>
<div class="status-banner" id="enrollment-banner" style="background:#4caf50; color:white; padding:15px; text-align:center; font-family:'Barlow'; font-weight:600; position:sticky; top:80px; z-index:1000; display:flex; align-items:center; justify-content:center; gap:10px;">
    <span>🚀 Enrollment Successful! We have received your Startup Tank 2.0 entry.</span>
    <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:white; cursor:pointer; font-weight:900;">&times;</button>
</div>
<?php unset($_SESSION['enrollment_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['registered_success'])): ?>
<div class="status-banner" id="registered-banner" style="background:var(--blue); color:white; padding:15px; text-align:center; font-family:'Barlow'; font-weight:600; position:sticky; top:80px; z-index:1000; display:flex; align-items:center; justify-content:center; gap:10px;">
    <span>✅ Account Created! Please <a href="login.php" style="color:var(--gold); text-decoration:underline;">login</a> to access your dashboard.</span>
    <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:white; cursor:pointer; font-weight:900;">&times;</button>
</div>
<?php unset($_SESSION['registered_success']); ?>
<?php endif; ?>

<script>
    // Preview logic for AI Studio environment (which strips PHP tags)
    // This allows the banners to show when redirected by server.ts
    window.addEventListener('DOMContentLoaded', () => {
        if (window.location.search.includes('enrolled=success') || window.location.search.includes('registered=1')) {
            // Find banners even if PHP tags were stripped but HTML remained
            const banners = document.querySelectorAll('.status-banner');
            banners.forEach(banner => {
                if (window.location.search.includes('enrolled') && banner.innerText.includes('Enrollment')) {
                    banner.style.display = 'flex';
                }
                if (window.location.search.includes('registered') && banner.innerText.includes('Account')) {
                    banner.style.display = 'flex';
                }
            });

            // Clear URL parameters to prevent re-showing on refresh
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.delete('enrolled');
                url.searchParams.delete('registered');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }, 1000);
        }
    });
</script>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">🚀 Enrollments Open Now</div>
        <h1 class="hero-h1">IDEAS TODAY.<br><span>IMPACT TOMORROW.</span></h1>
        <p class="hero-sub">Empowering school and college students to turn ideas into startups, solutions, and <strong>real-world impact.</strong> Join India’s most exciting student innovation platform.</p>
        <div class="hero-features">
            <span>PITCH</span> <span class="dot">·</span> <span>LEARN</span> <span class="dot">·</span> <span class="gold">GROW</span>
        </div>
        <div class="hero-btns">
            <button class="btn-primary" onclick="openModal('enrollModal')">🚀 Enroll Now</button>
            <button class="btn-secondary" onclick="window.location.href='#programs'">Explore Programs</button>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat-card">
            <div class="stat-num">5000+</div>
            <div class="stat-label">Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">200+</div>
            <div class="stat-label">Mentors</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">50+</div>
            <div class="stat-label">Events</div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="about" id="about">
    <div class="about-grid">
        <div class="about-img">
            <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=800&q=80" alt="Innovation Hub">
        </div>
        <div class="about-text">
            <p class="section-label">Who We Are</p>
            <h2 class="section-title">WHAT IS THE <span class="gold">STARTUP TANK?</span></h2>
            <p style="margin-bottom:20px; line-height:1.7; font-size:16px;">The Startup Tank is a student-focused entrepreneurship platform designed to help young minds <strong>ideate, innovate, and inspire.</strong> We provide a stage where students can pitch their ideas, gain expert feedback, learn startup skills, and build meaningful connections.</p>
            <p style="margin-bottom:30px; line-height:1.7; font-size:16px;">Whether you have a startup idea, a college project, or a dream to solve real problems - <strong>this is where your journey begins.</strong></p>
            <button class="btn-primary" style="background:var(--blue); border-color:var(--blue); color:white; box-shadow:none;" onclick="openModal('enrollModal')">Enroll Now</button>
        </div>
    </div>
</section>

<!-- WHY JOIN SECTION -->
<section style="padding: 120px 5%; background: var(--navy); text-align: center;" id="why">
    <p class="section-label" style="color:var(--gold);">Why Choose Us</p>
    <h2 class="section-title white"><span class="gold">WHY</span> STARTUP TANK?</h2>
    <div class="card-grid">
        <div class="feature-card">
            <div class="feature-icon">🚀</div>
            <h3>Pitch Your Ideas</h3>
            <p>Present your startup or business idea in front of mentors, industry experts, and judges who give real, actionable feedback.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📚</div>
            <h3>Learn & Grow</h3>
            <p>Attend workshops, masterclasses, bootcamps, and one-on-one mentorship sessions with seasoned entrepreneurs.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🏆</div>
            <h3>Win Opportunities</h3>
            <p>Get featured, win prizes, incubation support, internships, and real growth opportunities for your startup.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🤝</div>
            <h3>Build Your Network</h3>
            <p>Connect with founders, investors, creators, professionals, and like-minded students from across India.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS (Timeline) -->
<section class="how" id="how">
    <p class="section-label" style="color:var(--gold);">The Process</p>
    <h2 class="section-title white">HOW IT <span class="gold">WORKS</span></h2>
    <div class="steps-timeline">
        <div class="step-item">
            <div class="step-circle">01</div>
            <h4>Enroll Now</h4>
            <p>Sign up online and submit your details to get started.</p>
        </div>
        <div class="step-item">
            <div class="step-circle">02</div>
            <h4>Submit Idea</h4>
            <p>Share your startup idea, innovation, or problem-solving concept.</p>
        </div>
        <div class="step-item">
            <div class="step-circle">03</div>
            <h4>Get Shortlisted</h4>
            <p>Top ideas get selected for the pitch round by expert panel.</p>
        </div>
        <div class="step-item">
            <div class="step-circle">04</div>
            <h4>Pitch Live</h4>
            <p>Present your idea on stage before investors and mentors.</p>
        </div>
        <div class="step-item">
            <div class="step-circle">05</div>
            <h4>Grow Further</h4>
            <p>Receive mentorship, rewards, and future opportunities.</p>
        </div>
    </div>
</section>

<!-- WHO CAN JOIN -->
<section class="who">
    <p class="section-label" style="color:white; opacity:0.8;">Open To All</p>
    <h2 class="section-title white"><span class="gold">WHO</span> CAN JOIN?</h2>
    <div class="tag-container">
        <span class="tag">🎓 School Students</span>
        <span class="tag">🏫 College Students</span>
        <span class="tag">💡 Aspiring Entrepreneurs</span>
        <span class="tag">🔬 Innovators</span>
        <span class="tag">⚡ Startup Enthusiasts</span>
        <span class="tag">💻 Tech Creators</span>
        <span class="tag">🌟 Future Founders</span>
    </div>
    <div style="margin-top: 40px; position: relative; z-index:1;">
        <button class="btn-primary" onclick="openModal('enrollModal')">Enroll Now - It's Free!</button>
    </div>
</section>

<!-- PROGRAMS -->
<section id="programs">
    <p class="section-label">What We Offer</p>
    <h2 class="section-title">PROGRAMS & <span class="gold">EVENTS</span></h2>
    <div class="card-grid">
        <!-- Card 1 -->
        <div class="event-card">
            <div class="event-img-wrapper">
                <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80" alt="Pitch Competition">
            </div>
            <div class="event-content">
                <span class="event-tag">Competition</span>
                <h3>Startup Pitch competition</h3>
                <p>The ultimate stage for student founders. Pitch your vision to actual investors and win seed funding along with professional mentorship.</p>
                <a href="#" onclick="openModal('enrollModal')">Enroll Now <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="event-card">
            <div class="event-img-wrapper">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&q=80" alt="Masterclass">
            </div>
            <div class="event-content">
                <span class="event-tag">Learning</span>
                <h3>Workshops & Masterclasses</h3>
                <p>Deep-dive sessions on product building, growth hacking, and fundraising led by founders who have scaled startups successfully.</p>
                <a href="#" onclick="openModal('enrollModal')">View Schedule <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="event-card">
            <div class="event-img-wrapper">
                <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&q=80" alt="Innovation Challenge">
            </div>
            <div class="event-content">
                <span class="event-tag">Hackathon</span>
                <h3>Innovation Challenges</h3>
                <p>Collaborate with peers to solve critical industry problems. A high-intensity environment to test your technical and creative skills.</p>
                <a href="#" onclick="openModal('enrollModal')">Join Challenge <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="event-card">
            <div class="event-img-wrapper">
                <img src="https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=600&q=80" alt="Networking">
            </div>
            <div class="event-content">
                <span class="event-tag">Networking</span>
                <h3>Founder Mixers</h3>
                <p>Exclusive networking meets to find your co-founders, meet mentors, and build a powerful circle within the startup ecosystem.</p>
                <a href="#" onclick="openModal('enrollModal')">Reserve Seat <i data-lucide="arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section style="padding: 120px 5%; background: var(--navy); text-align: center;">
    <p class="section-label" style="color:var(--gold);">Student Stories</p>
    <h2 class="section-title white">SAY</h2>
    <div class="card-grid">
        <!-- Testi 1 -->
        <div style="background:rgba(255,255,255,0.03); padding:32px; border-radius:12px; text-align:left; border:1px solid rgba(255,255,255,0.05);">
            <div style="color:var(--gold); margin-bottom:15px;">★★★★★</div>
            <p style="font-size:15px; color:rgba(255,255,255,0.7); line-height:1.7; margin-bottom:20px; font-style:italic;">"The Startup Tank gave me the confidence to present my idea publicly. I never thought I could stand on a stage and pitch - but here I am with a working prototype!"</p>
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:50%; background:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:700;">A</div>
                <div>
                    <div style="font-size:14px; font-weight:700;">Arjun Sharma</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.4);">Student Founder, Delhi</div>
                </div>
            </div>
        </div>
        <!-- Testi 2 -->
        <div style="background:rgba(255,255,255,0.03); padding:32px; border-radius:12px; text-align:left; border:1px solid rgba(255,255,255,0.05);">
            <div style="color:var(--gold); margin-bottom:15px;">★★★★★</div>
            <p style="font-size:15px; color:rgba(255,255,255,0.7); line-height:1.7; margin-bottom:20px; font-style:italic;">"Amazing platform for young entrepreneurs. The mentorship and workshops completely changed how I think about business and problem-solving."</p>
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:50%; background:var(--blue-light); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--navy);">P</div>
                <div>
                    <div style="font-size:14px; font-weight:700;">Priya Menon</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.4);">College Participant, Mumbai</div>
                </div>
            </div>
        </div>
        <!-- Testi 3 -->
        <div style="background:rgba(255,255,255,0.03); padding:32px; border-radius:12px; text-align:left; border:1px solid rgba(255,255,255,0.05);">
            <div style="color:var(--gold); margin-bottom:15px;">★★★★★</div>
            <p style="font-size:15px; color:rgba(255,255,255,0.7); line-height:1.7; margin-bottom:20px; font-style:italic;">"Best opportunity to learn the startup mindset early. I met my co-founder here and we've already launched our first product. Don't miss this!"</p>
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:50%; background:var(--gold); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--navy);">R</div>
                <div>
                    <div style="font-size:14px; font-weight:700;">Rahul Verma</div>
                    <div style="font-size:11px; color:rgba(255,255,255,0.4);">School Student, Bangalore</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<section style="padding: 120px 5%; text-align: center; background: linear-gradient(0deg, #060e1c 0%, #0a1628 100%);">
    <p class="section-label">Take The Leap</p>
    <h2 class="section-title white">READY TO TURN YOUR <span class="gold">IDEA INTO REALITY?</span></h2>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 40px; font-size:18px;">Join thousands of students building the future of India. Enrollments are open now.</p>
    <div style="display:flex; gap:16px; justify-content:center;">
        <button class="btn-primary" onclick="openModal('enrollModal')">🚀 Enroll Now - Free!</button>
        <button class="btn-secondary" onclick="window.location.href='mailto:info@thestartuptank.com'">Contact Us</button>
    </div>
</section>

<footer>
    <div class="footer-grid">
        <div>
            <div class="footer-logo">
                <div class="nav-logo">
                    <div class="nav-logo-circle">
                        <span class="the-text">The</span>
                        <span class="startup-text">Startup</span>
                        <span class="tank-text">Tank</span>
                    </div>
                    <div class="nav-logo-text">The <span>Startup</span> Tank</div>
                </div>
            </div>
            <p style="color:rgba(255,255,255,0.5); font-size:14px; line-height:1.7; max-width:320px; margin-bottom:24px;">A platform empowering school and college students to ideate, innovate, and inspire. Your journey from idea to impact starts here.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#about">About Us</a></li>
                <li><a href="#why">Why Join</a></li>
                <li><a href="#how">How It Works</a></li>
                <li><a href="#programs">Programs</a></li>
                <li><a href="#" onclick="openModal('enrollModal')">Enroll Now</a></li>
            </ul>
        </div>
        <div class="footer-links">
            <h4>Contact</h4>
            <ul>
                <li><a href="#">www.thestartuptank.com</a></li>
                <li><a href="mailto:info@thestartuptank.com">info@thestartuptank.com</a></li>
                <li><a href="#">@thestartuptank</a></li>
                <li><a href="admin_login.php">Admin Panel</a></li>
            </ul>
        </div>
    </div>
    <div style="text-align:center; padding-top:40px; margin-top:60px; border-top:1px solid rgba(255,255,255,0.05); color:rgba(255,255,255,0.3); font-size:12px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-family:'Bebas Neue'; font-size:18px; letter-spacing:2px;">IDEATE. <span style="color:var(--gold);">INNOVATE.</span> INSPIRE.</div>
        <div>&copy; 2025 The Startup Tank. All rights reserved.</div>
    </div>
</footer>

<!-- Enrollment Modal -->
<div class="modal-overlay" id="enrollModal">
    <div class="modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-family:'Bebas Neue'; font-size:36px;">TANK 2.0 <span style="color:var(--gold);">ENROLLMENT</span></h2>
            <button onclick="closeModal('enrollModal')" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <p style="margin-bottom: 25px; color: rgba(255,255,255,0.7); font-size: 14px;">Submit your entry for The Startup Tank 2.0. This is an enrollment form, not a login account registration.</p>
        
        <form action="enrollment_submit.php" method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required minlength="3" placeholder="Enter your full name">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required placeholder="Your contact email">
                </div>
                <div class="form-group">
                    <label>Contact No. *</label>
                    <input type="tel" name="phone" required pattern="[0-9]{10,12}" title="Please enter a valid phone number (10-12 digits)" placeholder="Your phone number">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Class *</label>
                    <select name="class_year" required>
                        <option value="" disabled selected>Select</option>
                        <option value="9th">9th</option>
                        <option value="10th">10th</option>
                        <option value="11th">11th</option>
                        <option value="12th">12th</option>
                        <option value="1st Year (College)">1st Year (College)</option>
                        <option value="2nd Year (College)">2nd Year (College)</option>
                        <option value="3rd Year (College)">3rd Year (College)</option>
                        <option value="4th Year (College)">4th Year (College)</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stream *</label>
                    <select name="stream" required>
                        <option value="" disabled selected>Select</option>
                        <option value="Science (PCM)">Science (PCM)</option>
                        <option value="Science (PCB)">Science (PCB)</option>
                        <option value="Commerce">Commerce</option>
                        <option value="Humanities">Humanities</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Management">Management</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Your Startup Idea *</label>
                <textarea name="idea" rows="4" required minlength="20" placeholder="Describe your vision for Startup Tank 2.0 (min. 20 chars)..."></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; border:none; background: var(--blue); color: white; font-weight: 800; padding: 16px;">Enroll for Tank 2.0</button>
        </form>
    </div>
</div>

<!-- Student Registration Modal (Login Account) -->
<div class="modal-overlay" id="registerModal">
    <div class="modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-family:'Bebas Neue'; font-size:36px;">CREATE <span style="color:var(--gold);">STUDENT ACCOUNT</span></h2>
            <button onclick="closeModal('registerModal')" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
        </div>
        <p style="margin-bottom: 25px; color: rgba(255,255,255,0.7); font-size: 14px;">Register to access courses, resources, and your innovation dashboard.</p>
        
        <form action="enroll_action.php" method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required minlength="3" placeholder="Enter your full name">
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required placeholder="admin">
            </div>
            <div class="form-group">
                <label>Contact Number *</label>
                <input type="tel" name="phone" required placeholder="Your phone number">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Create Password *</label>
                    <input type="password" name="password" id="reg_pass" required minlength="6" placeholder=".........">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" id="reg_confirm" required oninput="checkPasswordMatch()">
                </div>
            </div>
            <div id="pass_error" style="color: #f44336; font-size: 12px; margin-top: -10px; margin-bottom: 10px; display: none;">Passwords do not match.</div>
            
            <button type="submit" class="btn-primary" style="width:100%; border:none; background: var(--gold); color: black; font-weight: 800; margin-top: 10px;">Create Account</button>
        </form>

        <div style="margin: 25px 0; display: flex; align-items: center; gap: 10px;">
            <div style="flex: 1; height: 1px; background: rgba(255,255,255,0.1);"></div>
            <div style="font-size: 12px; color: rgba(255,255,255,0.3); font-weight: 700; text-transform: uppercase;">Or continue with</div>
            <div style="flex: 1; height: 1px; background: rgba(255,255,255,0.1);"></div>
        </div>

        <button onclick="loginWithGoogle()" class="btn-secondary" style="width:100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px; border-color: rgba(255,255,255,0.2); color: white;">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" alt="Google">
            Sign in with Google
        </button>

        <p style="text-align:center; margin-top:20px; font-size:14px; color:rgba(255,255,255,0.6);">Already have an account? <a href="login.php" style="color:var(--gold);">Login here</a></p>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    function loginWithGoogle() {
        alert("Google Login Integration requires Client ID configuration. This feature is coming soon!");
    }
    function checkPasswordMatch() {
        const pass = document.getElementById('reg_pass').value;
        const confirm = document.getElementById('reg_confirm').value;
        const error = document.getElementById('pass_error');
        if (pass && confirm && pass !== confirm) {
            error.style.display = 'block';
        } else {
            error.style.display = 'none';
        }
    }
    
    function toggleMobileMenu() {
        document.getElementById('mobileMenu').classList.toggle('active');
        document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('active') ? 'hidden' : 'auto';
    }
    
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

</body>
</html>
