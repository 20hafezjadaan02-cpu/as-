<?php
include('../config.php'); 
session_start();

// تحقق من تسجيل الدخول
if(!isset($_SESSION['user_data'])){
    header('location: ../index.php');
    exit;
}

// إصلاح مشكلة المتغير غير المعرف
if (!isset($con) || !$con) {
    die('خطأ: لم يتم تعريف اتصال قاعدة البيانات ($con). تحقق من ملف config.php');
}

// حذف الطالب (إذا تم الطلب عبر GET)
$deletedMsg = '';
if(isset($_GET['delete_id'])){
    $id = (int)$_GET['delete_id'];
    $delete_student = "DELETE FROM students WHERE id = $id";
    $delete_query   = mysqli_query($con, $delete_student);
    if($delete_query){
        $deletedMsg = "✅ تم الحذف بنجاح";
    }else{
        $deletedMsg = "❌ فشل الحذف، يرجى المحاولة لاحقًا.";
    }
}

// الصفحة الحالية للـ pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$limit  = 5;
$offset = ($page - 1) * $limit;

// جلب الطلاب
$sql   = "SELECT id, name, department, semister, address, phone, birth_date, email, created_at, photo 
          FROM students ORDER BY id DESC LIMIT $offset,$limit";
$query = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>جميع الطلاب</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@500;700&display=swap');
*{box-sizing:border-box;font-family:"Cairo",sans-serif;}
body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#ff6a00,#ee0979,#2575fc,#6a11cb);
    background-size:400% 400%;
    animation:gradientBG 15s ease infinite;
    color:#fff;
    display:flex;
    flex-direction:column;
}
@keyframes gradientBG{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}
.container{
    max-width:1100px;
    margin:50px auto;
    background:rgba(0,0,0,0.45);
    padding:30px;
    border-radius:25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.6);
    backdrop-filter:blur(18px);
}
h1{text-align:center;margin-bottom:20px;color:#00f7ff;text-shadow:0 0 10px #00f7ff,0 0 20px #00c6ff;}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:rgba(255,255,255,0.1);
}
th,td{
    padding:12px;
    border:1px solid rgba(255,255,255,0.2);
    text-align:center;
}
th{background:rgba(0,0,0,0.3);}
tr:hover{background:rgba(255,255,255,0.05);}
button{
    font-family:"Cairo",sans-serif;
    padding:8px 12px;
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}
.delete-btn{background:#ff4d4d;color:#fff;transition:0.3s;}
.delete-btn:hover{background:#ff1a1a;}
.alert-success{
    background:rgba(0,255,127,0.2);
    color:#00ffb3;
    font-weight:bold;
    text-align:center;
    padding:12px;
    margin-bottom:15px;
    border-radius:12px;
}
.nav-bar{
    display:flex;
    justify-content:center;
    margin-bottom:20px;
}
.nav-bar a{
    text-decoration:none;
    color:#fff;
    font-weight:bold;
    margin:0 10px;
    padding:8px 12px;
    border-radius:12px;
    transition:0.3s;
}
.nav-bar a:hover{
    background:rgba(255,255,255,0.2);
}
</style>
<script>
function confirmDelete(id, name){
    if(confirm(`⚠️ هل أنت متأكد من حذف الطالب "${name}"؟`)){
        window.location.href = "?delete_id=" + id;
    }
}
</script>
</head>
<body>
<div class="nav-bar">
    <a href="index.php">🏠 لوحة التحكم</a>
    <a href="all_students.php">📚 جميع الطلاب</a>
    <a href="all_users.php">👤 جميع المشرفين</a>
    <a href="report.php">📊 تقارير الطلاب</a>
    <a href="add_student.php">➕ إضافة طالب</a>
</div>

<div class="container">
<h1>📚 جميع الطلاب</h1>

<?php if($deletedMsg): ?>
    <div class="alert-success"><?= htmlspecialchars($deletedMsg) ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>م</th>
            <th>الاسم</th>
            <th>القسم</th>
            <th>الفصل</th>
            <th>العنوان</th>
            <th>الهاتف</th>
            <th>تاريخ الميلاد</th>
            <th>البريد</th>
            <th>تاريخ الإضافة</th>
            <th>الصورة</th>
            <th>إجراء</th>
        </tr>
    </thead>
    <tbody>
<?php
$sr = $offset;
while($row = mysqli_fetch_assoc($query)){
    $date_fmt = !empty($row['created_at']) ? date('d-m-Y', strtotime($row['created_at'])) : '—';
?>
<tr>
<td><?= ++$sr ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['department']) ?></td>
<td><?= htmlspecialchars($row['semister']) ?></td>
<td><?= htmlspecialchars($row['address']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['birth_date']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= $date_fmt ?></td>
<td>
<?php if(!empty($row['photo']) && file_exists("../uploads/".$row['photo'])): ?>
    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" width="80">
<?php else: ?>لا توجد صورة<?php endif; ?>
</td>
<td>
<button class="delete-btn" onclick="confirmDelete(<?= $row['id'] ?>,'<?= htmlspecialchars($row['name']) ?>')">حذف</button>
</td>
</tr>
<?php } ?>
    </tbody>
</table>
</div>
</body>
</html>
