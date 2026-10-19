<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Digos Veterinary Clinic & Supply')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
    <style>
        :root {
            --shell-bg: #f3efe7;
            --shell-ink: #17313d;
            --shell-muted: #60707d;
            --shell-line: rgba(23, 49, 61, 0.12);
            --shell-panel: rgba(255, 255, 255, 0.84);
            --shell-panel-strong: #ffffff;
            --shell-accent: #0f8b8d;
            --shell-accent-deep: #0d6475;
            --shell-warm: #d67c4a;
            --shell-success: #198754;
            --shell-warning: #b7791f;
            --shell-danger: #c44f43;
            --shell-shadow: 0 20px 55px rgba(23, 49, 61, 0.12);
            --shell-radius-xl: 28px;
            --shell-radius-lg: 22px;
            --shell-radius-md: 16px;
            --shell-radius-sm: 12px;
            --font-body: "Aptos", "Segoe UI", "Trebuchet MS", sans-serif;
            --font-heading: "Aptos Display", "Trebuchet MS", "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(15, 139, 141, 0.16), transparent 26rem),
                radial-gradient(circle at right center, rgba(214, 124, 74, 0.14), transparent 28rem),
                linear-gradient(180deg, #f8f4ed 0%, #eef3f5 100%);
            color: var(--shell-ink);
            font-family: var(--font-body);
            line-height: 1.55;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .app-shell {
            display: grid;
            grid-template-columns: 290px minmax(0, 1fr);
            gap: 1.5rem;
            padding: 1.5rem;
            min-height: 100vh;
        }

        .app-sidebar {
            position: sticky;
            top: 1.5rem;
            align-self: start;
            min-height: calc(100vh - 3rem);
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.42);
            border-radius: calc(var(--shell-radius-xl) + 6px);
            background:
                linear-gradient(180deg, rgba(19, 49, 61, 0.96) 0%, rgba(15, 68, 84, 0.96) 100%);
            color: rgba(255, 255, 255, 0.88);
            box-shadow: var(--shell-shadow);
            overflow: hidden;
        }

        .app-sidebar::before {
            content: "";
            position: absolute;
            inset: auto -3rem -6rem auto;
            width: 14rem;
            height: 14rem;
            border-radius: 50%;
            background: rgba(214, 124, 74, 0.16);
            filter: blur(20px);
            pointer-events: none;
        }

        .sidebar-brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 0.75rem 1.4rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 0.85rem;
        }

        .brand-lockup {
            display: grid;
            gap: 0.22rem;
        }

        .brand-mark {
            display: block;
            width: clamp(4.5rem, 10vw, 12.5rem);
            aspect-ratio: 1;
            max-width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.35rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 12px 22px rgba(3, 20, 25, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .sidebar-brand strong {
            font: 800 1.15rem/1.1 var(--font-heading);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .sidebar-brand span {
            display: none;
        }

        .sidebar-nav {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 0.5rem;
            padding-top: 1.25rem;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.95rem 1rem;
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.8);
            transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease;
        }

        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            transform: translateX(2px);
        }

        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(214, 124, 74, 0.2));
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        }

        .sidebar-foot {
            position: relative;
            z-index: 1;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.66);
            font-size: 0.9rem;
        }

        .app-main {
            min-width: 0;
            overflow: visible;
        }

        .topbar {
            position: relative;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem 1rem 1.4rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.52);
            border-radius: calc(var(--shell-radius-xl) + 2px);
            background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(18px);
            box-shadow: 0 16px 38px rgba(23, 49, 61, 0.09);
            overflow: visible;
        }

        .topbar-copy {
            display: grid;
            gap: 0.18rem;
        }

        .topbar-eyebrow {
            color: var(--shell-muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .topbar-title {
            font: 700 1.32rem/1.1 var(--font-heading);
            color: var(--shell-ink);
        }

        .profile-menu {
            position: relative;
            z-index: 50;
        }

        .profile-menu summary {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.55rem 0.75rem 0.55rem 0.55rem;
            border-radius: 999px;
            background: rgba(15, 139, 141, 0.08);
            cursor: pointer;
            user-select: none;
        }

        .profile-menu summary::-webkit-details-marker {
            display: none;
        }

        .profile-avatar {
            width: 2.55rem;
            height: 2.55rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep));
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .profile-meta {
            display: grid;
            text-align: left;
        }

        .profile-meta strong {
            font-size: 0.96rem;
            color: var(--shell-ink);
        }

        .profile-meta span {
            color: var(--shell-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 0.7rem);
            right: 0;
            min-width: 230px;
            padding: 0.6rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-md);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 22px 48px rgba(23, 49, 61, 0.16);
            z-index: 50;
        }

        .dropdown-link,
        .dropdown-button {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 0.95rem;
            border: none;
            border-radius: 14px;
            background: transparent;
            color: var(--shell-ink);
            cursor: pointer;
            text-align: left;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .dropdown-link:hover,
        .dropdown-button:hover {
            background: rgba(15, 139, 141, 0.08);
        }

        .dropdown-divider {
            height: 1px;
            margin: 0.45rem 0;
            background: var(--shell-line);
        }

        .page-content {
            display: grid;
            gap: 1.4rem;
        }

        .hero-card,
        .card,
        .widget,
        .surface {
            position: relative;
            min-width: 0;
            background: var(--shell-panel);
            border: 1px solid rgba(255, 255, 255, 0.54);
            border-radius: var(--shell-radius-xl);
            box-shadow: var(--shell-shadow);
            backdrop-filter: blur(14px);
        }

        .hero-card,
        .card {
            padding: 1.55rem;
        }

        .hero-card {
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(214, 124, 74, 0.16), transparent 18rem),
                radial-gradient(circle at left bottom, rgba(15, 139, 141, 0.14), transparent 16rem),
                rgba(255, 255, 255, 0.82);
        }

        .hero-card::after {
            content: "";
            position: absolute;
            inset: auto -4rem -4rem auto;
            width: 11rem;
            height: 11rem;
            border-radius: 50%;
            background: rgba(23, 49, 61, 0.05);
        }

        .hero-row,
        .section-head,
        .split-head,
        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-copy,
        .section-copy,
        .muted-copy {
            color: var(--shell-muted);
        }

        .hero-title,
        h1,
        h2,
        h3,
        h4 {
            font-family: var(--font-heading);
            color: var(--shell-ink);
        }

        .hero-title {
            font-size: clamp(2rem, 3vw, 2.7rem);
            line-height: 1.02;
            letter-spacing: -0.03em;
        }

        .hero-copy {
            max-width: 48rem;
            margin-top: 0.7rem;
            font-size: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 46px;
            padding: 0.82rem 1.2rem;
            border: none;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep));
            color: #fff;
            box-shadow: 0 14px 28px rgba(15, 100, 117, 0.24);
        }

        .btn-secondary {
            background: rgba(23, 49, 61, 0.08);
            color: var(--shell-ink);
        }

        .btn-danger {
            background: linear-gradient(135deg, #d56557, var(--shell-danger));
            color: #fff;
            box-shadow: 0 14px 28px rgba(196, 79, 67, 0.22);
        }

        .grid,
        .stat-grid,
        .metric-grid,
        .info-grid,
        .form-grid {
            display: grid;
            gap: 1rem;
        }

        .compact-shell {
            padding: 0.75rem;
        }

        .compact-shell .card,
        .compact-shell .surface-header,
        .compact-shell .alert {
            padding: 1rem;
        }

        .compact-shell h1 {
            font-size: 1.4rem;
        }

        .compact-shell .section-copy {
            margin-top: 0.55rem;
            font-size: 0.95rem;
        }

        .compact-shell .action-row {
            display: grid;
            gap: 0.75rem;
        }

        .compact-shell .btn {
            min-height: 42px;
            padding: 0.72rem 0.95rem;
            width: 100%;
        }

        @media (max-width: 640px) {
            .compact-shell {
                margin: 0;
                min-height: 100vh;
                padding: 0.75rem 0.75rem 1rem;
            }

            .compact-shell .card {
                border-radius: 1rem;
                box-shadow: 0 18px 32px rgba(23, 49, 61, 0.15);
            }

            .compact-shell .surface-header {
                gap: 0.65rem;
            }

            .compact-shell .action-row {
                gap: 0.65rem;
            }

            .compact-shell .btn {
                min-height: 40px;
                padding: 0.65rem 0.9rem;
            }
        }

        .grid,
        .stat-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .metric-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .info-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .form-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .widget,
        .metric-card,
        .stat-card,
        .detail-card,
        .detail-panel,
        .summary-card {
            padding: 1.15rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.58);
        }

        .widget-title,
        .metric-label,
        .detail-label,
        .summary-label,
        .eyebrow {
            display: block;
            color: var(--shell-muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 0.45rem;
        }

        .widget-value,
        .metric-value {
            font: 700 clamp(1.7rem, 2.4vw, 2.4rem)/1 var(--font-heading);
            color: var(--shell-ink);
            letter-spacing: -0.03em;
        }

        .stack {
            display: grid;
            gap: 1rem;
        }

        .list-grid {
            display: grid;
            gap: 1rem;
        }

        .list-card,
        .record-card,
        .session-card,
        .appointment-card {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: start;
            padding: 1.2rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: var(--shell-panel-strong);
            box-shadow: 0 10px 28px rgba(23, 49, 61, 0.06);
        }

        .item-title {
            font-size: 1.02rem;
            font-weight: 700;
        }

        .item-copy,
        .item-meta,
        .detail-copy {
            color: var(--shell-muted);
        }

        .item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.65rem;
        }

        .pill,
        .badge,
        .count-chip,
        .mini-chip,
        .status-badge,
        .status-pill,
        .issued-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .pill,
        .badge,
        .count-chip,
        .mini-chip,
        .issued-chip {
            background: rgba(15, 139, 141, 0.1);
            color: var(--shell-accent-deep);
        }

        .pill-success,
        .badge-monitor,
        .status-complete {
            background: rgba(25, 135, 84, 0.14);
            color: #116d44;
        }

        .pill-warning,
        .badge-visit,
        .status-pending {
            background: rgba(183, 121, 31, 0.14);
            color: #8d5e12;
        }

        .pill-danger,
        .badge-emergency,
        .status-danger {
            background: rgba(196, 79, 67, 0.14);
            color: #9f372d;
        }

        .pill-neutral,
        .status-open,
        .status-approved {
            background: rgba(13, 100, 117, 0.14);
            color: var(--shell-accent-deep);
        }

        .surface-header {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .empty-state {
            padding: 1.35rem;
            border: 1px dashed rgba(23, 49, 61, 0.18);
            border-radius: var(--shell-radius-lg);
            color: var(--shell-muted);
            text-align: center;
            background: rgba(255, 255, 255, 0.54);
        }

        .field {
            display: grid;
            gap: 0.48rem;
        }

        .field-label,
        .form-label {
            font-weight: 700;
            color: var(--shell-ink);
        }

        .field-control,
        .form-field {
            width: 100%;
            min-height: 50px;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(23, 49, 61, 0.16);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.94);
            color: var(--shell-ink);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        textarea.field-control,
        textarea.form-field {
            min-height: 140px;
            resize: vertical;
        }

        .field-control:focus,
        .form-field:focus {
            outline: none;
            border-color: rgba(15, 139, 141, 0.55);
            box-shadow: 0 0 0 4px rgba(15, 139, 141, 0.12);
        }

        .field-error {
            color: var(--shell-danger);
            font-size: 0.84rem;
        }

        .banner,
        .alert {
            padding: 1rem 1.05rem;
            border-radius: 18px;
            border: 1px solid var(--shell-line);
            background: rgba(255, 255, 255, 0.86);
        }

        .alert-info,
        .banner-info {
            border-color: rgba(15, 139, 141, 0.18);
            background: rgba(15, 139, 141, 0.1);
            color: var(--shell-accent-deep);
        }

        .banner-danger {
            border-color: rgba(196, 79, 67, 0.18);
            background: rgba(196, 79, 67, 0.1);
            color: #8d3026;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.88);
        }

        .workspace-grid {
            display: grid;
            grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .thread-list {
            display: grid;
            gap: 0.85rem;
        }

        .thread-link {
            display: grid;
            gap: 0.65rem;
            padding: 1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.9);
            color: var(--shell-ink);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .thread-link:hover {
            transform: translateY(-1px);
            border-color: rgba(15, 139, 141, 0.25);
            box-shadow: 0 14px 30px rgba(23, 49, 61, 0.08);
        }

        .thread-link.active {
            border-color: rgba(15, 139, 141, 0.34);
            background: linear-gradient(180deg, rgba(15, 139, 141, 0.1), rgba(255, 255, 255, 0.95));
            box-shadow: 0 18px 34px rgba(15, 100, 117, 0.1);
        }

        .thread-link-head,
        .thread-link-foot {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 0.85rem;
        }

        .thread-link-copy {
            display: grid;
            gap: 0.2rem;
            min-width: 0;
        }

        .thread-link-title {
            font-weight: 700;
        }

        .thread-link-subtitle,
        .thread-link-preview {
            color: var(--shell-muted);
            font-size: 0.92rem;
        }

        .thread-link-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .detail-grid-compact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
        }

        .chat-shell {
            display: grid;
            gap: 1rem;
        }

        .chat-window {
            display: grid;
            gap: 1rem;
        }

        .message-stream {
            display: grid;
            gap: 0.85rem;
            max-height: 620px;
            padding-right: 0.25rem;
            overflow-y: auto;
        }

        .message-row {
            display: flex;
            justify-content: flex-start;
        }

        .message-row.outgoing {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: min(40rem, 100%);
            padding: 0.95rem 1rem;
            border: 1px solid rgba(23, 49, 61, 0.1);
            border-radius: 22px 22px 22px 10px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 12px 24px rgba(23, 49, 61, 0.06);
        }

        .message-row.outgoing .message-bubble {
            border-radius: 22px 22px 10px 22px;
            background: linear-gradient(135deg, rgba(15, 139, 141, 0.15), rgba(13, 100, 117, 0.2));
            border-color: rgba(13, 100, 117, 0.12);
        }

        .message-bubble.staff {
            background: linear-gradient(135deg, rgba(214, 124, 74, 0.12), rgba(255, 255, 255, 0.95));
        }

        .message-bubble.veterinarian {
            background: linear-gradient(135deg, rgba(15, 139, 141, 0.12), rgba(255, 255, 255, 0.95));
        }

        .message-bubble.pet-owner {
            background: rgba(255, 255, 255, 0.96);
        }

        .message-row.outgoing .message-bubble,
        .message-row.outgoing .message-bubble.staff,
        .message-row.outgoing .message-bubble.veterinarian,
        .message-row.outgoing .message-bubble.pet-owner {
            background: linear-gradient(135deg, rgba(15, 139, 141, 0.15), rgba(13, 100, 117, 0.2));
            border-color: rgba(13, 100, 117, 0.12);
        }

        .message-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.45rem;
            color: var(--shell-muted);
            font-size: 0.78rem;
            letter-spacing: 0.02em;
        }

        .message-body {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .composer-card {
            padding: 1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.92);
        }

        .summary-panel {
            padding: 1rem 1.1rem;
            border: 1px solid rgba(15, 139, 141, 0.12);
            border-radius: var(--shell-radius-lg);
            background: rgba(15, 139, 141, 0.08);
        }

        .summary-panel.warning {
            border-color: rgba(183, 121, 31, 0.14);
            background: rgba(183, 121, 31, 0.08);
        }

        .app-table {
            width: 100%;
            border-collapse: collapse;
        }

        .app-table th,
        .app-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
        }

        .app-table th {
            color: var(--shell-muted);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(23, 49, 61, 0.03);
        }

        .app-table tr:last-child td {
            border-bottom: none;
        }

        .super-admin-page {
            display: grid;
            gap: 1.25rem;
        }

        .super-admin-section {
            display: grid;
            gap: 0.9rem;
        }

        .super-admin-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.9rem;
            flex-wrap: wrap;
        }

        .super-admin-list-shell {
            display: grid;
            gap: 0.9rem;
        }

        .super-admin-list-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .super-admin-list-toolbar .form-control,
        .super-admin-list-toolbar .form-select {
            min-width: 220px;
        }

        .super-admin-list-toolbar .toolbar-group {
            display: flex;
            gap: 0.65rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .super-admin-sort-button {
            border: 1px solid #d1d5db;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            padding: 0.35rem 0.7rem;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .super-admin-sort-button.active {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .super-admin-list-item {
            padding: 0.95rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.9rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .super-admin-list-item .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .scrollable-compact-list {
            display: grid;
            gap: 0.75rem;
            max-height: 25.5rem;
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .scrollable-compact-list::-webkit-scrollbar {
            width: 0.45rem;
        }

        .scrollable-compact-list::-webkit-scrollbar-thumb {
            background: rgba(23, 49, 61, 0.2);
            border-radius: 999px;
        }

        .super-admin-pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .super-admin-pagination button {
            padding: 0.45rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.65rem;
            background: #fff;
            color: #111827;
            cursor: pointer;
        }

        .super-admin-pagination button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .super-admin-empty-state {
            padding: 1rem;
            color: #6b7280;
            border: 1px dashed #d1d5db;
            border-radius: 0.85rem;
            background: #f8fafc;
        }

        @media (max-width: 1100px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .app-sidebar {
                position: static;
                min-height: auto;
            }

            .workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .app-shell {
                grid-template-columns: 1fr;
                padding: 1rem;
                gap: 1rem;
                min-height: 100vh;
            }

            .app-sidebar {
                position: sticky;
                top: 0;
                z-index: 100;
                min-height: auto;
                padding: 1rem;
                border-radius: var(--shell-radius-lg);
                margin-bottom: 1rem;
            }

            .sidebar-brand {
                padding: 0.6rem 0.5rem 0.9rem;
                margin-bottom: 0.8rem;
            }

            .brand-mark {
                width: clamp(3.2rem, 18vw, 5.3rem);
                padding: 0.2rem;
            }

            .sidebar-brand strong {
                font-size: 1rem;
            }

            .sidebar-brand span {
                display: none;
            }

            .sidebar-nav {
                display: flex;
                gap: 0.5rem;
                padding-top: 0;
                overflow-x: auto;
            }

            .sidebar-item {
                padding: 0.75rem 1rem;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .sidebar-foot {
                display: none;
            }

            .topbar {
                position: relative;
                z-index: 30;
                padding: 0.85rem 1rem;
                margin-bottom: 1rem;
                gap: 0.75rem;
                overflow: visible;
            }

            .topbar-copy {
                gap: 0.1rem;
            }

            .topbar-eyebrow {
                font-size: 0.7rem;
            }

            .topbar-title {
                font-size: 1.1rem;
            }

            .profile-menu summary {
                padding: 0.5rem;
                gap: 0.5rem;
            }

            .profile-avatar {
                width: 2.2rem;
                height: 2.2rem;
                font-size: 0.9rem;
            }

            .profile-meta {
                display: none;
            }

            .profile-dropdown {
                min-width: 200px;
                right: -10px;
                z-index: 60;
            }

            .page-content {
                gap: 1rem;
            }

            .hero-card,
            .card {
                padding: 1rem;
            }

            .hero-title {
                font-size: 1.5rem;
            }

            .hero-copy {
                font-size: 0.95rem;
            }

            .btn {
                min-height: 44px;
                padding: 0.7rem 1rem;
                font-size: 0.95rem;
            }

            .grid,
            .stat-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .metric-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .widget,
            .metric-card,
            .stat-card,
            .detail-card {
                padding: 0.9rem;
            }

            .widget-title,
            .metric-label,
            .detail-label {
                font-size: 0.7rem;
            }

            .widget-value,
            .metric-value {
                font-size: clamp(1.4rem, 2vw, 2rem);
            }

            .list-card,
            .record-card,
            .session-card,
            .appointment-card {
                flex-direction: column;
                padding: 1rem;
                gap: 0.75rem;
            }

            .item-title {
                font-size: 0.95rem;
            }

            .item-meta {
                gap: 0.4rem;
                margin-top: 0.5rem;
            }

            .pill,
            .badge,
            .status-badge,
            .status-pill {
                min-height: 28px;
                padding: 0.3rem 0.7rem;
                font-size: 0.7rem;
            }

            .field-control,
            .form-field {
                min-height: 44px;
                padding: 0.8rem;
                font-size: 1rem;
            }

            textarea.field-control,
            textarea.form-field {
                min-height: 120px;
            }

            .field-label,
            .form-label {
                font-size: 0.9rem;
            }

            .banner,
            .alert {
                padding: 0.85rem;
                font-size: 0.9rem;
            }

            .hero-row,
            .section-head,
            .split-head,
            .action-row,
            .surface-header {
                flex-direction: column;
                gap: 0.75rem;
            }

            .workspace-grid {
                grid-template-columns: 1fr;
            }

            .thread-link {
                gap: 0.5rem;
                padding: 0.85rem;
            }

            .thread-link-copy {
                gap: 0.15rem;
            }

            .detail-grid-compact {
                grid-template-columns: 1fr;
                gap: 0.7rem;
            }

            .message-stream {
                max-height: 400px;
            }

            .message-bubble {
                max-width: 100%;
                padding: 0.85rem;
                font-size: 0.95rem;
            }

            .composer-card {
                padding: 0.85rem;
            }

            .summary-panel {
                padding: 0.85rem;
            }

            .app-table th,
            .app-table td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .app-table th {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .app-shell {
                padding: 0.75rem;
                gap: 0.75rem;
            }

            .topbar {
                padding: 0.7rem;
                margin-bottom: 0.75rem;
            }

            .topbar-title {
                font-size: 1rem;
            }

            .sidebar-nav {
                gap: 0.35rem;
            }

            .sidebar-item {
                padding: 0.65rem 0.8rem;
                font-size: 0.85rem;
            }

            .hero-title {
                font-size: 1.3rem;
            }

            .btn {
                width: 100%;
                min-height: 44px;
            }

            .widget-value,
            .metric-value {
                font-size: clamp(1.2rem, 1.8vw, 1.8rem);
            }

            .grid,
            .stat-grid {
                grid-template-columns: 1fr;
            }

            .metric-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-card,
            .card {
                padding: 0.9rem;
            }

            .hero-card::after {
                inset: auto -2rem -2rem auto;
                width: 8rem;
                height: 8rem;
            }

            .field-control,
            .form-field {
                font-size: 16px;
            }

            .dropdown-link,
            .dropdown-button {
                padding: 0.75rem 0.8rem;
                font-size: 0.9rem;
            }

            .message-stream {
                max-height: 300px;
            }

            .list-card,
            .record-card,
            .session-card,
            .appointment-card {
                padding: 0.85rem;
            }

            .thread-link {
                padding: 0.75rem;
            }
        }
    </style>
    @stack('styles')
    @yield('styles')
    <style>
        .page-content > .card,
        .page-content .card {
            border: 1px solid rgba(23, 49, 61, 0.08);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.9));
            box-shadow: 0 18px 36px rgba(23, 49, 61, 0.08);
        }

        .page-content .surface-header {
            padding-bottom: 0.9rem;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
        }

        .page-content :is(.table-shell, .table-wrap, .table-responsive, .mini-table-wrap) {
            overflow-x: auto;
            border: 1px solid rgba(23, 49, 61, 0.08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.92);
        }

        .page-content table {
            border-collapse: collapse;
        }

        .page-content table thead th {
            background: rgba(148, 163, 184, 0.08) !important;
            color: #475569 !important;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .page-content table th,
        .page-content table td {
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
            vertical-align: middle;
        }

        .page-content table tbody tr {
            transition: background-color 0.16s ease;
        }

        .page-content table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.035);
        }

        .page-content :is(.action-btn, .queue-filter-btn, .filter-btn) {
            border-radius: 0.8rem;
            font-weight: 700;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .page-content :is(.action-btn, .queue-filter-btn, .filter-btn):hover {
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .page-content .surface-header {
                gap: 0.75rem;
            }

            .page-content table th,
            .page-content table td {
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    @php
        $currentUser = auth()->user();
        $roleLabel = $currentUser?->role ?? 'User';
        $initials = collect(explode(' ', trim((string) ($currentUser?->name ?? 'User'))))
            ->filter()
            ->take(2)
            ->map(fn ($segment) => strtoupper(substr($segment, 0, 1)))
            ->implode('');
    @endphp

    <div class="app-shell">
        @auth
            <aside class="app-sidebar">
                <div class="sidebar-brand">
                    <img src="{{ asset('images/Test.jpg') }}" alt="DVCS logo" class="brand-mark">
                </div>

                <nav class="sidebar-nav">
                    @yield('sidebar')
                </nav>

                <div class="sidebar-foot">
                    Signed in as {{ $roleLabel }}.
                </div>
            </aside>
        @endauth

        <div class="app-main">
            <header class="topbar">
                <div class="topbar-copy">
                    <span class="topbar-eyebrow">Care Operations</span>
                    <strong class="topbar-title">@yield('title', 'Digos Veterinary Clinic & Supply')</strong>
                </div>

                @auth
                    <details class="profile-menu">
                        <summary>
                            <span class="profile-avatar">{{ $initials ?: 'VC' }}</span>
                            <span class="profile-meta">
                                <strong>{{ $currentUser->name }}</strong>
                                <span>{{ $roleLabel }}</span>
                            </span>
                        </summary>

                        <div class="profile-dropdown">
                            <a href="{{ route('profile.settings') }}" class="dropdown-link">Profile Settings</a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-button">Logout</button>
                            </form>
                        </div>
                    </details>
                @endauth
            </header>

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
     @livewireScripts

    <script>
        function openPetCareAI(event) {
            event.preventDefault();
            window.location.assign(@json(route('pet-owner.symptom-checker')));
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-super-admin-list]').forEach((root) => {
                const searchInput = root.querySelector('[data-super-admin-search]');
                const filterSelects = Array.from(root.querySelectorAll('[data-super-admin-filter]'));
                const sortButtons = Array.from(root.querySelectorAll('[data-super-admin-sort]'));
                const pageSizeSelect = root.querySelector('[data-super-admin-page-size]');
                const items = Array.from(root.querySelectorAll('[data-super-admin-item]'));
                const countLabel = root.querySelector('[data-super-admin-count]');
                const pageInfo = root.querySelector('[data-super-admin-page-info]');
                const prevButton = root.querySelector('[data-super-admin-prev]');
                const nextButton = root.querySelector('[data-super-admin-next]');
                const resetButton = root.querySelector('[data-super-admin-reset]');

                if (items.length === 0) {
                    return;
                }

                let currentPage = 1;
                let currentSortKey = root.dataset.defaultSort || '';
                let currentSortDirection = root.dataset.defaultDirection || 'asc';

                const getValue = (item, key) => (item.dataset[key] || '').trim().toLowerCase();

                const applyFilters = (rows) => {
                    let filtered = [...rows];

                    if (searchInput) {
                        const query = searchInput.value.trim().toLowerCase();
                        if (query) {
                            filtered = filtered.filter((item) => (item.dataset.searchText || '').includes(query));
                        }
                    }

                    filterSelects.forEach((select) => {
                        const key = select.dataset.superAdminFilter;
                        const value = select.value;
                        if (key && value && value !== 'all') {
                            filtered = filtered.filter((item) => (item.dataset[key] || '').toLowerCase() === value.toLowerCase());
                        }
                    });

                    return filtered;
                };

                const applySort = (rows) => {
                    if (!currentSortKey) {
                        return rows;
                    }

                    return [...rows].sort((a, b) => {
                        const left = getValue(a, currentSortKey);
                        const right = getValue(b, currentSortKey);

                        const leftNumber = Number(left);
                        const rightNumber = Number(right);
                        const isNumeric = !Number.isNaN(leftNumber) && !Number.isNaN(rightNumber);
                        const comparison = isNumeric
                            ? leftNumber - rightNumber
                            : left.localeCompare(right);

                        return currentSortDirection === 'desc' ? -comparison : comparison;
                    });
                };

                const render = () => {
                    const filtered = applySort(applyFilters(items));
                    const pageSize = parseInt(pageSizeSelect?.value || root.dataset.pageSize || '8', 10);
                    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
                    currentPage = Math.min(currentPage, totalPages);
                    const start = (currentPage - 1) * pageSize;
                    const end = start + pageSize;

                    items.forEach((item) => {
                        item.style.display = 'none';
                    });

                    filtered.slice(start, end).forEach((item) => {
                        item.style.display = 'block';
                    });

                    if (countLabel) {
                        countLabel.textContent = filtered.length > 0
                            ? `Showing ${Math.min(end, filtered.length)} of ${filtered.length} records`
                            : 'No records found';
                    }

                    if (pageInfo) {
                        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
                    }

                    if (prevButton) {
                        prevButton.disabled = currentPage === 1;
                    }

                    if (nextButton) {
                        nextButton.disabled = currentPage >= totalPages;
                    }
                };

                searchInput?.addEventListener('input', () => {
                    currentPage = 1;
                    render();
                });

                filterSelects.forEach((select) => {
                    select.addEventListener('change', () => {
                        currentPage = 1;
                        render();
                    });
                });

                sortButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const key = button.dataset.superAdminSort;
                        if (!key) {
                            return;
                        }

                        if (currentSortKey === key) {
                            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            currentSortKey = key;
                            currentSortDirection = 'asc';
                        }

                        sortButtons.forEach((candidate) => {
                            candidate.classList.toggle('active', candidate === button);
                        });

                        currentPage = 1;
                        render();
                    });
                });

                pageSizeSelect?.addEventListener('change', () => {
                    currentPage = 1;
                    render();
                });

                resetButton?.addEventListener('click', () => {
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    filterSelects.forEach((select) => {
                        select.value = 'all';
                    });
                    currentSortKey = root.dataset.defaultSort || '';
                    currentSortDirection = root.dataset.defaultDirection || 'asc';
                    sortButtons.forEach((button) => {
                        button.classList.toggle('active', button.dataset.superAdminSort === currentSortKey);
                    });
                    currentPage = 1;
                    render();
                });

                if (prevButton) {
                    prevButton.addEventListener('click', () => {
                        if (currentPage > 1) {
                            currentPage -= 1;
                            render();
                        }
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        currentPage += 1;
                        render();
                    });
                }

                sortButtons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.superAdminSort === currentSortKey);
                });

                render();
            });
        });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
