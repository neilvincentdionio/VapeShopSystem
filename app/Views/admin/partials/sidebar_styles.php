<style>
    .admin-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 270px !important;
        height: 100vh !important;
        padding: 1rem !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        z-index: 10000 !important;
        border-right: 1px solid #e0e0e0 !important;
        border-bottom: none !important;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.06) !important;
        background: #fff !important;
        text-align: left !important;
        box-sizing: border-box !important;
    }
    .admin-sidebar,
    .admin-sidebar * {
        box-sizing: border-box !important;
    }
    .admin-sidebar .navbar-content {
        max-width: none !important;
        min-height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1rem !important;
        text-align: left !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .admin-sidebar .navbar-brand {
        display: block !important;
        font-size: 1.1rem !important;
        padding: 0.25rem 0.25rem 1rem !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        border-bottom: 1px solid #ececec !important;
        font-weight: 700 !important;
        text-align: center !important;
        white-space: normal !important;
        overflow-wrap: normal !important;
        line-height: 1.2 !important;
        text-decoration: none !important;
        color: #1f2937 !important;
    }
    .admin-sidebar .brand-logo-image {
        display: block !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: 112px !important;
        margin: 0 auto !important;
    }
    .admin-sidebar .navbar-center {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
    }
    .admin-sidebar .navbar-menu {
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.35rem !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .admin-sidebar .navbar-menu a,
    .admin-sidebar .nav-dropdown-btn {
        color: #333333 !important;
        text-decoration: none !important;
        border: none !important;
        background: transparent !important;
        cursor: pointer !important;
        font-family: inherit !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        line-height: 1.35 !important;
        width: 100% !important;
        text-align: left !important;
        border-radius: 8px !important;
        padding: 0.7rem 1rem !important;
        white-space: normal !important;
        transition: all 0.2s ease !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .admin-sidebar .navbar-menu a:hover,
    .admin-sidebar .navbar-menu a.active,
    .admin-sidebar .nav-dropdown-btn:hover {
        background: #f1f3f5 !important;
        color: #27c56f !important;
    }
    .admin-sidebar .nav-dropdown {
        width: 100% !important;
    }
    .admin-sidebar .nav-dropdown-content {
        position: static !important;
        display: none !important;
        margin-top: 0.4rem !important;
        min-width: 0 !important;
        border: 1px solid #efefef !important;
        box-shadow: none !important;
    }
    .admin-sidebar .nav-dropdown:hover .nav-dropdown-content {
        display: block !important;
    }
    .admin-sidebar .nav-dropdown-content a {
        display: block !important;
        padding-left: 1.2rem !important;
        text-align: left !important;
    }

     .admin-sidebar .nav-link.active {
        background: #f1f3f5 !important;
        color: #27c56f !important;
    }
    .admin-sidebar .nav-right {
        border-top: 1px solid #ececec !important;
        padding-top: 0.9rem !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.7rem !important;
        text-align: left !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
     .admin-sidebar .user-info {
        justify-content: flex-start !important;
        flex-wrap: wrap !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.55rem !important;
        text-align: left !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

     .admin-sidebar .admin-user-actions {
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
    }

     .admin-sidebar .admin-user-main {
        display: flex !important;
        align-items: center !important;
        gap: 0.55rem !important;
        min-width: 0 !important;
    }

     .admin-sidebar .user-name {
        max-width: 150px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        font-weight: 500 !important;
    }

     .admin-sidebar .user-profile-link {
        color: #333333 !important;
        text-decoration: none !important;
    }

     .admin-sidebar .user-profile-link:hover {
        color: #27c56f !important;
        text-decoration: underline !important;
    }

     .admin-sidebar .user-avatar {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #27c56f, #7ef0b2) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
    }

     .admin-sidebar .btn-danger {
        background: #dc3545 !important;
        color: #ffffff !important;
        text-decoration: none !important;
        padding: 0.75rem 1rem !important;
        border-radius: 8px !important;
        border: none !important;
        font-size: 0.95rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        width: 100% !important;
        text-align: center !important;
    }

     .admin-sidebar .btn-danger:hover {
        background: #c82333 !important;
    }

    .container,
    .container-fluid {
        max-width: none !important;
        margin-left: 270px !important;
        width: calc(100% - 270px) !important;
        padding: 1.5rem !important;
        overflow-x: hidden !important;
    }

    /* Unified Admin Button System */
    .container .btn,
    .container button.btn,
    .container a.btn,
    .container .action-btn,
    .container .btn-view,
    .container .btn-edit,
    .container .btn-delete,
    .container .btn-view-proof,
    .container .btn-danger-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.35rem !important;
        padding: 0.58rem 0.95rem !important;
        border-radius: 10px !important;
        border: 1px solid transparent !important;
        font-size: 0.95rem !important;
        font-weight: 600 !important;
        line-height: 1.1 !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
    }

    .container .btn-sm {
        padding: 0.42rem 0.72rem !important;
        font-size: 0.85rem !important;
        border-radius: 8px !important;
    }

    .container .btn-lg {
        padding: 0.72rem 1.15rem !important;
        font-size: 1rem !important;
        border-radius: 11px !important;
    }

    .container .btn-primary,
    .container .btn-success,
    .container .btn-checkout,
    .container .btn-delivered {
        background: #27c56f !important;
        border-color: #27c56f !important;
        color: #ffffff !important;
    }

    .container .btn-primary:hover,
    .container .btn-success:hover,
    .container .btn-checkout:hover,
    .container .btn-delivered:hover {
        background: #20ae61 !important;
        border-color: #20ae61 !important;
        transform: translateY(-1px) !important;
    }

    .container .btn-secondary {
        background: #ffffff !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }

    .container .btn-secondary:hover {
        background: #f3f4f6 !important;
        border-color: #c7cdd5 !important;
    }

    .container .btn-warning,
    .container .btn-edit {
        background: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
    }

    .container .btn-warning:hover,
    .container .btn-edit:hover {
        background: #d88707 !important;
        border-color: #d88707 !important;
        transform: translateY(-1px) !important;
    }

    .container .btn-danger,
    .container .btn-delete,
    .container .btn-danger-icon {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
        color: #ffffff !important;
    }

    .container .btn-danger:hover,
    .container .btn-delete:hover,
    .container .btn-danger-icon:hover {
        background: #bf2d3b !important;
        border-color: #bf2d3b !important;
        transform: translateY(-1px) !important;
    }

    .container .btn-info,
    .container .btn-view,
    .container .btn-details,
    .container .btn-view-proof {
        background: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #ffffff !important;
    }

    .container .btn-info:hover,
    .container .btn-view:hover,
    .container .btn-details:hover,
    .container .btn-view-proof:hover {
        background: #0b90cc !important;
        border-color: #0b90cc !important;
        transform: translateY(-1px) !important;
    }

    @media (max-width: 992px) {
    .admin-sidebar {
            position: sticky !important;
            width: 100% !important;
            height: auto !important;
            border-right: none !important;
            border-bottom: 1px solid #e0e0e0 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }
    .admin-sidebar .navbar-content {
            min-height: auto !important;
        }

        .container,
        .container-fluid {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 1rem !important;
        }
    }
</style>

