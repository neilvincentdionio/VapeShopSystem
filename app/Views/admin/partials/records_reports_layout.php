<style>
    .records-reports-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 1.5rem 2rem 2.5rem;
        width: 100%;
    }
    .records-reports-page .module-shell {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .records-reports-page .module-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #eef0f2;
    }
    .records-reports-page .module-header h1,
    .records-reports-page .module-header h2 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 700;
        color: #1f2937;
    }
    .records-reports-page .module-header p {
        margin: 0.35rem 0 0;
        color: #666;
        font-size: 0.92rem;
    }
    .records-reports-page .module-header-meta {
        color: #6b7280;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    .records-reports-page .module-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eef0f2;
    }
    .records-reports-page .module-toolbar .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 0.65rem;
        flex: 1 1 520px;
        margin: 0;
    }
    .records-reports-page .module-toolbar label {
        display: block;
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 0.2rem;
    }
    .records-reports-page .module-toolbar input,
    .records-reports-page .module-toolbar select {
        min-height: 40px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.5rem 0.7rem;
        font-family: inherit;
        font-size: 0.92rem;
        background: #fff;
    }
    .records-reports-page .module-toolbar .search-input {
        flex: 1 1 220px;
        min-width: 180px;
    }
    .records-reports-page .module-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.85rem 1.5rem;
        background: #fafbfc;
        border-bottom: 1px solid #eef0f2;
    }
    .records-reports-page .module-actions-bar strong {
        color: #374151;
        font-size: 0.9rem;
    }
    .records-reports-page .module-actions-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-left: auto;
    }
    .records-reports-page .module-meta {
        padding: 0.85rem 1.5rem 1rem;
        margin: 0;
        font-size: 0.85rem;
        color: #6b7280;
        border-bottom: 1px solid #eef0f2;
    }
    .records-reports-page .module-table-wrap {
        padding: 0 1.5rem 1.25rem;
        overflow-x: auto;
    }
    .records-reports-page .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .records-reports-page .stat-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        min-height: 92px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .records-reports-page .stat-card h3 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #27c56f;
        line-height: 1.2;
    }
    .records-reports-page .stat-card p {
        margin: 0.25rem 0 0;
        font-size: 0.84rem;
        color: #666;
    }
    .records-reports-page .stat-card.is-refund h3 {
        color: #6d28d9;
    }
    .records-reports-page .section-tabs {
        padding: 1rem 1.5rem 0;
    }
    .records-reports-page .section-body {
        padding: 0.75rem 1.5rem 1.25rem;
    }
    .records-reports-page .table-wrap {
        overflow-x: auto;
    }
    .records-reports-page .tabs {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }
    .records-reports-page .tab-btn {
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        border: 1px solid #e0e0e0;
        background: #fff;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.85rem;
    }
    .records-reports-page .tab-btn.active {
        background: #27c56f;
        color: #fff;
        border-color: #27c56f;
    }
    .records-reports-page .tab-panel {
        display: none;
    }
    .records-reports-page .tab-panel.active {
        display: block;
    }
    @media (max-width: 1100px) {
        .records-reports-page .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .records-reports-page {
            padding: 1rem;
        }
        .records-reports-page .module-header,
        .records-reports-page .module-toolbar,
        .records-reports-page .module-actions-bar,
        .records-reports-page .module-meta,
        .records-reports-page .module-table-wrap,
        .records-reports-page .section-tabs,
        .records-reports-page .section-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .records-reports-page .stats-grid {
            grid-template-columns: 1fr;
        }
        .records-reports-page .module-actions-group {
            margin-left: 0;
            width: 100%;
        }
    }
</style>
