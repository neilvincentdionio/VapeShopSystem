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
        z-index: 100 !important;
        border-right: 1px solid #e0e0e0 !important;
        border-bottom: none !important;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.06) !important;
        background: #fff !important;
        text-align: left !important;
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
    .admin-sidebar .brand-mark {
        display: block !important;
        font-size: 1.35rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
        color: #4c1d95 !important;
        margin-bottom: 0.2rem !important;
        text-align: center !important;
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

     .admin-sidebar .user-name {
        max-width: 170px !important;
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

