<style>
    body.cms-admin-theme #content .content.container-fluid {
        padding-bottom: 2rem;
    }

    body.cms-admin-theme #content .inline-page-menu {
        margin-bottom: 1.5rem;
    }

    body.cms-admin-theme #content .inline-page-menu ul {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
    }

    body.cms-admin-theme #content .inline-page-menu li {
        list-style: none;
        margin: 0;
    }

    body.cms-admin-theme #content .inline-page-menu .nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.875rem;
        padding: 0.8rem 1.15rem;
        border: 1px solid #d9e6f3;
        border-radius: 999px;
        background: #fff;
        color: #4a617a;
        font-weight: 600;
        line-height: 1.2;
        transition: color .2s ease, border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    body.cms-admin-theme #content .inline-page-menu .nav-link:hover {
        color: #1455ac;
        border-color: #9fc0ea;
        text-decoration: none;
        transform: translateY(-1px);
    }

    body.cms-admin-theme #content .inline-page-menu .nav-link.active {
        color: #1455ac;
        background: linear-gradient(180deg, #f7fbff 0%, #edf5ff 100%);
        border-color: #1455ac;
        box-shadow: 0 14px 28px rgba(20, 85, 172, 0.12);
    }

    body.cms-admin-theme #content .card {
        border: 0;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(5, 36, 78, 0.08);
    }

    body.cms-admin-theme #content .card-header,
    body.cms-admin-theme #content .modal-header {
        border-bottom: 1px solid #e8eef5;
        background:
            radial-gradient(circle at top right, rgba(20, 85, 172, 0.08), transparent 42%),
            linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    body.cms-admin-theme #content .card-body {
        background: #f7faff;
    }

    body.cms-admin-theme #content .table-responsive {
        border: 1px solid #e2ebf4;
        border-radius: 1rem;
        box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        background: #fff;
    }

    body.cms-admin-theme #content .table {
        margin-bottom: 0;
        background: #fff;
    }

    body.cms-admin-theme #content .table thead th {
        background: #f7faff;
        color: #4a617a;
        border-bottom: 1px solid #e2ebf4;
        vertical-align: middle;
    }

    body.cms-admin-theme #content .table td,
    body.cms-admin-theme #content .table th {
        vertical-align: middle;
    }

    body.cms-admin-theme #content .form-control,
    body.cms-admin-theme #content .custom-select,
    body.cms-admin-theme #content textarea {
        border-radius: 0.9rem;
        border-color: #d7e3ef;
        min-height: calc(1.5em + 1rem + 2px);
        box-shadow: none;
    }

    body.cms-admin-theme #content .form-control:focus,
    body.cms-admin-theme #content .custom-select:focus,
    body.cms-admin-theme #content textarea:focus {
        border-color: #1455ac;
        box-shadow: 0 0 0 0.2rem rgba(20, 85, 172, 0.1);
    }

    body.cms-admin-theme #content .form-label,
    body.cms-admin-theme #content label,
    body.cms-admin-theme #content .title-color {
        color: #1e3250;
        font-weight: 600;
    }

    body.cms-admin-theme #content .btn {
        border-radius: 999px;
    }

    body.cms-admin-theme #content .btn--primary,
    body.cms-admin-theme #content .btn-primary {
        box-shadow: 0 14px 28px rgba(20, 85, 172, 0.16);
    }

    body.cms-admin-theme #content .btn-secondary {
        border-color: #d4e1ec;
        background: #eef4f9;
        color: #48627f;
    }

    body.cms-admin-theme #content .nav-tabs {
        display: flex;
        gap: 0.75rem;
        border-bottom: 0;
        margin-bottom: 1.5rem !important;
        flex-wrap: wrap;
    }

    body.cms-admin-theme #content .nav-tabs .nav-link {
        margin: 0;
        border: 1px solid #dbe6f3;
        border-radius: 999px;
        background: #eef5fb;
        color: #60748a;
        font-weight: 600;
        padding: 0.7rem 1.05rem;
        transition: all .2s ease;
    }

    body.cms-admin-theme #content .nav-tabs .nav-link:hover {
        border-color: #1455ac;
        color: #1455ac;
    }

    body.cms-admin-theme #content .nav-tabs .nav-link.active {
        color: #1455ac;
        border-color: #1455ac;
        background: #fff;
        box-shadow: 0 10px 20px rgba(20, 85, 172, 0.08);
    }

    body.cms-admin-theme #content .badge {
        border-radius: 999px;
    }

    body.cms-admin-theme #content .cms-admin-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    body.cms-admin-theme #content .cms-admin-heading__title {
        margin: 0;
        color: #1e3250;
    }

    body.cms-admin-theme #content .cms-admin-empty {
        padding: 2rem;
        border: 1px dashed #c7d8ea;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        text-align: center;
        color: #60748a;
    }

    body.cms-admin-theme #content .cms-admin-note {
        padding: 1rem 1.25rem;
        border: 1px solid #dce8f4;
        border-radius: 1rem;
        background: #f9fbff;
        color: #60748a;
    }

    body.cms-admin-theme #content .modal-content {
        border: 1px solid #e2ebf4;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 44, 84, 0.12);
    }

    body.cms-admin-theme #content .modal-footer {
        border-top: 1px solid #e8eef5;
    }

    html[dir="rtl"] body.cms-admin-theme #content .card-header,
    html[dir="rtl"] body.cms-admin-theme #content .table td,
    html[dir="rtl"] body.cms-admin-theme #content .table th,
    html[dir="rtl"] body.cms-admin-theme #content .cms-admin-heading {
        text-align: right;
    }

    @media (max-width: 767.98px) {
        body.cms-admin-theme #content .card-header,
        body.cms-admin-theme #content .card-body {
            padding: 1rem;
        }

        body.cms-admin-theme #content .cms-admin-heading {
            align-items: stretch;
        }
    }
</style>
