import express from "express";
import fs from "fs";
import path from "path";

const app = express();
const PORT = 3000;

// Middleware to serve static assets
app.use("/assets", express.static(path.join(process.cwd(), "assets")));

// Simple function to "render" php files as static html for preview
// Fixed to actually handle some data in the preview
const mockDB = {
    enrollments: [
        { id: 1, full_name: "Yash Sharma", email: "yash@example.com", phone: "9876543210", class_year: "12th", stream: "Science", idea: "An AI platform for rural education.", created_at: new Date().toISOString() }
    ],
    students: [
        { id: 1, first_name: "John", last_name: "Doe", email: "student@example.com", status: "approved", institution: "IIT Bombay", class_year: "4th Year", student_type: "college", stream: "Engineering", idea: "Solar powered IoT devices", created_at: new Date().toISOString() },
        { id: 2, first_name: "Pending", last_name: "Student", email: "pending@example.com", status: "pending", institution: "Delhi University", class_year: "2nd Year", student_type: "college", stream: "Commerce", idea: "Fintech app for students", created_at: new Date().toISOString() }
    ]
};

function renderPHP(filePath: string, context: 'admin' | 'student' | 'none' = 'none') {
    if (!fs.existsSync(filePath)) return "File not found";
    let content = fs.readFileSync(filePath, "utf8");
    
    // Inject data for preview if on specific pages
    if (context === 'admin') {
        // Mocking the loop for Enrollments
        const enrollmentRows = mockDB.enrollments.map(e => `
            <tr>
                <td>
                    <div style="font-weight:700; color:white;">${e.full_name}</div>
                    <div style="font-size:11px; color:#4CAF50;">${e.email}</div>
                </td>
                <td style="font-size:13px;">
                    <div>${e.class_year}</div>
                    <div style="color:rgba(255,255,255,0.4); font-size:11px;">${e.stream}</div>
                </td>
                <td style="font-size:13px;">${e.phone}</td>
                <td><div style="font-size:12px; line-height:1.4; color:rgba(255,255,255,0.7);">${e.idea}</div></td>
                <td style="font-size:11px; color:rgba(255,255,255,0.4);">${e.created_at}</td>
            </tr>
        `).join('');
        
        // Mocking the loop for Pending Students
        const pendingRows = mockDB.students.filter(s => s.status === 'pending').map(s => `
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; background:rgba(255,152,0,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-family:'Bebas Neue'; font-size:18px; color:#ff9800; border:1px solid #ff9800;">
                            ${(s.first_name || 'S')[0]}
                        </div>
                        <div>
                            <div style="font-weight:700; color:white;">${s.first_name} ${s.last_name}</div>
                            <div style="font-size:10px; color:rgba(255,255,255,0.4);">${s.created_at}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;">${s.email}</td>
                <td style="font-size:12px;">
                    <div style="font-weight:600;">${s.institution}</div>
                    <div style="color:var(--gold); font-size:11px;">${(s.student_type || '').toUpperCase()} (${s.class_year})</div>
                </td>
                <td style="font-size:12px; color:rgba(255,255,255,0.7);">${s.stream || 'N/A'}</td>
                <td>
                    <a href="approve_student.php?id=${s.id}" style="padding:4px 8px; font-size:10px; background:#4CAF50; border:none; color:white; text-decoration:none; border-radius:3px; font-weight:700;">Approve</a>
                    <a href="reject_student.php?id=${s.id}" style="padding:4px 8px; font-size:10px; background:#f44336; border:none; color:white; text-decoration:none; border-radius:3px; font-weight:700; margin-left:5px;">Reject</a>
                </td>
            </tr>
        `).join('');

        // Mocking the loop for Approved Students
        const approvedRows = mockDB.students.filter(s => s.status === 'approved').map(s => `
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:36px; height:36px; background:rgba(0,102,255,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-family:'Bebas Neue'; font-size:18px; color:var(--blue-light); border:1px solid var(--gold);">
                            ${(s.first_name || 'S')[0]}
                        </div>
                        <div>
                            <div style="font-weight:700; color:white;">${s.first_name} ${s.last_name}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:13px;">${s.email}</td>
                <td>${s.institution}</td>
                <td>${s.stream}</td>
                <td>APPROVED</td>
            </tr>
        `).join('');

        // Handle the conditional rendering for empty states in preview
        const enrollmentsCount = mockDB.enrollments.length;
        const pendingCount = mockDB.students.filter(s => s.status === 'pending').length;
        const approvedCount = mockDB.students.filter(s => s.status === 'approved').length;

        if (enrollmentsCount > 0) {
            // Remove the empty message block
            content = content.replace(/<\?php if \(empty\(\$enrollments\)\): \?>[\s\S]*?<\?php else: \?>/g, "");
            content = content.replace(/<\?php endif; \?>/g, "");
        } else {
            // Remove the table and else block, keep only the if block content
            content = content.replace(/<\?php else: \?>[\s\S]*?<\?php endif; \?>/g, "");
        }

        if (approvedCount > 0) {
            content = content.replace(/<\?php if \(empty\(\$approved_students\)\): \?>[\s\S]*?<\?php else: \?>/g, "");
            // The endif will be caught by a generic sweep or specific replace
        } else {
            content = content.replace(/<\?php else: \?>[\s\S]*?<\?php endif; \?>/g, "");
        }

        if (pendingCount === 0) {
            content = content.replace(/<\?php if \(count\(\$pending_students\) > 0\): \?>[\s\S]*?<\?php endif; \?>/g, "");
        }

        // Basic replacement of PHP loops with our mock data
        content = content.replace(/<\?php foreach \(\$enrollments as \$e\): \?>[\s\S]*?<\?php endforeach; \?>/g, enrollmentRows);
        content = content.replace(/<\?php foreach \(\$pending_students as \$s\): \?>[\s\S]*?<\?php endforeach; \?>/g, pendingRows);
        content = content.replace(/<\?php foreach \(\$approved_students as \$s\): \?>[\s\S]*?<\?php endforeach; \?>/g, approvedRows);
        
        // Replace summary counts
        content = content.replace(/<\?php echo count\(\$enrollments\); \?>/g, enrollmentsCount.toString());
        content = content.replace(/<\?php echo count\(\$pending_students\); \?>/g, pendingCount.toString());
        content = content.replace(/<\?php echo count\(\$approved_students\); \?>/g, approvedCount.toString());

        // Final sweep for remaining php tags like endif/else that might be left over if not caught above
        content = content.replace(/<\?php[\s\S]*?\?>/g, "");
    } else if (context === 'student') {
        const s = mockDB.students[0];
        content = content.replace(/<\?php echo strtoupper\(\$student\['first_name'\]\); \?>/g, (s.first_name || '').toUpperCase());
        content = content.replace(/<\?php echo \$student\['first_name'\]; \?>/g, s.first_name);
        content = content.replace(/<\?php echo \$student\['last_name'\]; \?>/g, s.last_name);
        content = content.replace(/<\?php echo \$student\['email'\]; \?>/g, s.email);
        content = content.replace(/<\?php echo \$student\['institution'\]; \?>/g, s.institution || '');
        content = content.replace(/<\?php echo \$student\['class_year'\]; \?>/g, s.class_year || '');
        content = content.replace(/<\?php echo strtoupper\(\$student\['stream'\]\); \?>/g, (s.stream || '').toUpperCase());
        content = content.replace(/<\?php echo strtoupper\(\$student\['student_type'\]\); \?>/g, (s.student_type || 'STUDENT').toUpperCase());
        content = content.replace(/<\?php echo \$student\['idea'\] \? nl2br\(\$student\['idea'\]\) : '.*?'; \?>/g, s.idea || 'No idea');
        content = content.replace(/<\?php echo strtoupper\(\$student\['first_name'\]\[0\]\); \?>/g, (s.first_name || 'S')[0].toUpperCase());
    }

    // Finally strip remaining PHP tags
    content = content.replace(/<\?php[\s\S]*?\?>/g, "");
    return content;
}

app.use(express.urlencoded({ extended: true }));

app.get("/", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "index.php")));
});

app.get("/index.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "index.php")));
});

app.get("/login.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "login.php")));
});

app.get("/profile.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "profile.php"), 'student'));
});

app.get("/admin_login.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "admin_login.php")));
});

app.get("/admin_dashboard.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "admin_dashboard.php"), 'admin'));
});

app.get("/courses.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "courses.php")));
});

app.get("/resources.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "resources.php")));
});

app.get("/forgot_password.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "forgot_password.php")));
});

app.get("/export_students.php", (req, res) => {
    res.send("Download triggered in a real PHP environment (Students CSV Export).");
});

app.get("/export_enrollments.php", (req, res) => {
    res.send("Download triggered in a real PHP environment (Enrollments CSV Export).");
});

app.get("/reset_password.php", (req, res) => {
    res.send(renderPHP(path.join(process.cwd(), "reset_password.php")));
});

app.get("/approve_student.php", (req, res) => {
    const id = req.query.id;
    const student = mockDB.students.find(s => s.id == Number(id));
    if (student) student.status = 'approved';
    res.redirect("/admin_dashboard.php?approved=1");
});

app.get("/reject_student.php", (req, res) => {
    const id = req.query.id;
    const student = mockDB.students.find(s => s.id == Number(id));
    if (student) student.status = 'rejected';
    res.redirect("/admin_dashboard.php?rejected=1");
});

// Post handlers to handle navigation in the preview environment
app.post("/enrollment_submit.php", (req, res) => {
    console.log("Enrollment:", req.body);
    mockDB.enrollments.push({
        id: mockDB.enrollments.length + 1,
        full_name: req.body.full_name,
        email: req.body.email,
        phone: req.body.phone,
        class_year: req.body.class_year || 'Not Specified',
        stream: req.body.stream || 'Not Specified',
        idea: req.body.idea || '',
        created_at: new Date().toISOString()
    });
    res.redirect("/index.php?enrolled=success");
});

app.post("/enroll_action.php", (req, res) => {
    console.log("Registration:", req.body);
    const parts = (req.body.full_name || "").split(" ");
    const firstName = parts[0];
    const lastName = parts.slice(1).join(" ");
    
    mockDB.students.push({
        id: mockDB.students.length + 1,
        first_name: firstName,
        last_name: lastName,
        email: req.body.email,
        status: 'approved',
        institution: 'Not Specified',
        class_year: req.body.class_year || 'Not Specified',
        student_type: 'college',
        stream: req.body.stream || 'Not Specified',
        idea: '',
        created_at: new Date().toISOString()
    });
    res.redirect("/login.php?registered=1");
});

app.post("/login.php", (req, res) => {
    res.redirect("/profile.php");
});

app.post("/register_action.php", (req, res) => {
    res.redirect("/login.php?registered=1");
});

app.post("/admin_login.php", (req, res) => {
    res.redirect("/admin_dashboard.php");
});

app.post("/admin_dashboard.php", (req, res) => {
    res.redirect("/admin_dashboard.php?success=1");
});

app.post("/update_student.php", (req, res) => {
    res.redirect("/admin_dashboard.php?updated=1");
});

app.post("/update_idea.php", (req, res) => {
    res.redirect("/profile.php?updated=1");
});

app.post("/forgot_password.php", (req, res) => {
    res.redirect("/forgot_password.php?sent=1");
});

app.post("/reset_password.php", (req, res) => {
    res.redirect("/login.php?reset=1");
});

app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
