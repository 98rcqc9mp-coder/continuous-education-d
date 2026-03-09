<?php
require_once "db.php";
require_once "header.php";

function e($str){ return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$code = trim((string)($_GET['code'] ?? ($_POST['code'] ?? '')));
$result = null;
$error = '';

if ($code !== '') {
    try {
        // If certificate_issues table doesn't exist yet, this will throw.
        $st = $pdo->prepare("\n            SELECT ci.verify_code, ci.issued_at,\n                   p.full_name,\n                   c.course_name, c.start_date, c.end_date,\n                   d.site_name\n            FROM certificate_issues ci\n            JOIN participants p ON p.id = ci.participant_id\n            JOIN courses c ON c.id = ci.course_id\n            LEFT JOIN design d ON d.id = 1\n            WHERE ci.verify_code = ?\n            LIMIT 1\n        ");
        $st->execute([$code]);
        $result = $st->fetch();
        if (!$result) {
            $error = 'لم يتم العثور على شهادة بهذا الرقم.';
        }
    } catch (Throwable $ex) {
        $error = 'تعذر التحقق حالياً. تأكد من إنشاء جدول الشهادات (certificate_issues).';
    }
}
?>

<div class="glass soft">
  <div class="section-title">
    <h2>✅ التحقق من الشهادة</h2>
    <div class="meta">أدخل رقم الشهادة أو افتح الرابط من QR</div>
  </div>

  <form method="post" class="row g-2 align-items-end">
    <div class="col-md-9">
      <label class="form-label">رقم الشهادة</label>
      <input class="form-control" name="code" value="<?= e($code) ?>" placeholder="مثال: CE-2026-000123" required>
    </div>
    <div class="col-md-3">
      <button class="btn-soft primary w-100 justify-content-center" type="submit">تحقق</button>
    </div>
  </form>

  <?php if ($code !== ''): ?>
    <hr class="sep">

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" style="font-weight:900;">
        <?= e($error) ?>
      </div>
    <?php else: ?>
      <div class="alert alert-success" style="font-weight:900;">
        ✅ شهادة صحيحة
      </div>

      <div class="table-responsive">
        <table class="table-soft">
          <tbody>
            <tr>
              <th style="width:220px;">اسم المشترك</th>
              <td style="text-align:right;"><?= e($result['full_name'] ?? '') ?></td>
            </tr>
            <tr>
              <th>اسم الدورة</th>
              <td style="text-align:right;"><?= e($result['course_name'] ?? '') ?></td>
            </tr>
            <tr>
              <th>تاريخ الدورة</th>
              <td style="text-align:right;">
                <?= e($result['start_date'] ?? '') ?> إلى <?= e($result['end_date'] ?? '') ?>
              </td>
            </tr>
            <tr>
              <th>الجهة المانحة</th>
              <td style="text-align:right;"><?= e(($result['site_name'] ?? '') ?: 'نظام التعليم المستمر') ?></td>
            </tr>
            <tr>
              <th>رقم الشهادة</th>
              <td style="text-align:right;"><span class="pill info"><?= e($result['verify_code'] ?? '') ?></span></td>
            </tr>
            <tr>
              <th>تاريخ الإصدار</th>
              <td style="text-align:right;"><?= e($result['issued_at'] ?? '') ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

</div><!-- content -->
</div><!-- overlay -->
</body>
</html>
