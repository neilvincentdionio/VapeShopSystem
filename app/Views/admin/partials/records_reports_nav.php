<?php
$activeTab = $activeTab ?? 'records';
?>
<style>
    .records-module-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
    .records-module-nav a {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.55rem 1rem;
        border-radius: 999px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #444;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .records-module-nav a.active {
        background: #27c56f;
        border-color: #27c56f;
        color: #fff;
    }
</style>
<nav class="records-module-nav" aria-label="Records and reports">
    <a href="<?= site_url('records') ?>" class="<?= $activeTab === 'records' ? 'active' : '' ?>">
        <i class="fas fa-list"></i> All Records
    </a>
    <a href="<?= site_url('records/reports') ?>" class="<?= $activeTab === 'reports' ? 'active' : '' ?>">
        <i class="fas fa-chart-pie"></i> Sales Reports
    </a>
</nav>
