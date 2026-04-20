<?php
/**
 * 统一底部访问统计栏（从根目录读取统计数据）
 * $xh_root 需在引入前定义（同 topnav.php）
 */
$_stat_root = $xh_root ?? '';
$_counter   = @file_get_contents($_stat_root . 'visits.txt');
$_total     = $_counter !== false ? (int)$_counter : '—';
$_logRaw    = @file_get_contents($_stat_root . 'visit_log.json');
$_log       = $_logRaw ? (json_decode($_logRaw, true) ?: []) : [];
$_recent    = array_slice($_log, -12);
$_items     = [];
foreach (array_reverse($_recent) as $_e) {
    $_items[] = substr($_e['time'], 11, 5) . '　' . ($_e['city'] ?? '未知');
}
$_scrollText = implode('<br>', $_items);
if ($_scrollText) { $_scrollText .= '<br><br>' . $_scrollText; }
?>
<div style="max-width:1280px;margin:48px auto 0;padding:0 24px 40px;">
    <div style="background:#fff;border-radius:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);
                display:flex;align-items:center;gap:30px;flex-wrap:wrap;padding:18px 24px;">
        <div style="font-size:1.25rem;font-weight:600;color:#1e40af;white-space:nowrap;">
            累计访问 <span style="color:#10b981;font-size:1.5rem;"><?= number_format((int)$_total) ?></span> 次
        </div>
        <?php if ($_scrollText): ?>
        <div style="flex:1;min-width:240px;height:54px;overflow:hidden;position:relative;
                    border-left:3px solid #e0f2fe;padding-left:20px;">
            <div style="position:absolute;width:100%;
                        animation:xhVertMarquee 18s linear infinite;
                        line-height:1.8;font-size:0.9rem;color:#64748b;">
                <?= $_scrollText ?>
            </div>
        </div>
        <style>@keyframes xhVertMarquee{0%{transform:translateY(0)}100%{transform:translateY(-50%)}}</style>
        <?php endif; ?>
    </div>
</div>
