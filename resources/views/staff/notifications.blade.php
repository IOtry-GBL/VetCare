@extends('layouts.app')

@section('title', 'Staff Notifications')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Live Staff Notifications</h1>
                <p class="hero-copy">
                    Track new appointment entries, today's cancellations, and recently marked did-not-arrive cases from one live feed.
                </p>
            </div>

            <div class="live-indicator-card">
                <span class="live-pill">Live Feed</span>
                <strong>Auto-refresh every 30 seconds</strong>
                <span class="muted-copy" data-staff-last-synced>
                    Last synced {{ $lastSyncedAt->format('M j, Y g:i:s A') }} PH
                </span>
            </div>
        </div>
    </section>

    <div data-staff-notification-summary>
        @include('staff.partials.notification-summary')
    </div>

    <div data-staff-notification-feed>
        @include('staff.partials.notification-feed')
    </div>
@endsection

@section('styles')
    <style>
        .live-indicator-card {
            min-width: min(100%, 270px);
            display: grid;
            gap: 0.35rem;
            padding: 1rem 1.1rem;
            border: 1px solid rgba(15, 139, 141, 0.18);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.82);
        }

        .live-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.14);
            color: #047857;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .notification-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(170px, 1fr));
            gap: 1rem;
            margin-top: 1.2rem;
        }

        .notification-summary-card {
            padding: 1rem 1.05rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
        }

        .notification-summary-card strong {
            display: block;
            margin-top: 0.45rem;
            font-size: 1.5rem;
            color: var(--shell-ink);
        }

        .summary-label {
            display: block;
            font-size: 0.78rem;
            color: var(--shell-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .notification-feed-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1.2rem;
        }

        .notification-feed-column {
            display: grid;
            gap: 1rem;
            align-content: start;
        }

        .notification-feed-card {
            padding: 1rem 1.05rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
        }

        .notification-feed-card h2 {
            margin: 0;
            font-size: 1.05rem;
        }

        .notification-section-copy {
            margin: 0.35rem 0 0 0;
            color: var(--shell-muted);
            line-height: 1.5;
        }

        .notification-item-list {
            display: grid;
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .notification-item {
            padding: 0.95rem 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: #f8fafc;
            display: grid;
            gap: 0.55rem;
        }

        .notification-item.notification-item-new {
            border-left: 4px solid #0f8b8d;
        }

        .notification-item.notification-item-cancelled {
            border-left: 4px solid #f59e0b;
        }

        .notification-item.notification-item-missed {
            border-left: 4px solid #ef4444;
        }

        .notification-item-head {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: start;
            flex-wrap: wrap;
        }

        .notification-item-title {
            font-weight: 700;
            color: var(--shell-ink);
        }

        .notification-time-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
            color: var(--shell-muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .notification-item-copy {
            margin: 0;
            color: var(--shell-muted);
            line-height: 1.55;
        }

        .notification-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .notification-meta .pill {
            font-size: 0.76rem;
        }

        .pill-warn {
            background: rgba(245, 158, 11, 0.14);
            color: #92400e;
        }

        .pill-danger {
            background: rgba(239, 68, 68, 0.14);
            color: #991b1b;
        }

        .notification-empty {
            padding: 1rem;
            border-radius: 1rem;
            border: 1px dashed var(--shell-line);
            background: rgba(248, 250, 252, 0.85);
            color: var(--shell-muted);
            text-align: center;
        }

        @media (max-width: 1100px) {
            .notification-summary-grid,
            .notification-feed-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const summaryContainer = document.querySelector('[data-staff-notification-summary]');
            const feedContainer = document.querySelector('[data-staff-notification-feed]');
            const lastSyncedLabel = document.querySelector('[data-staff-last-synced]');
            const pollUrl = @json(route('staff.notifications.feed'));

            if (!summaryContainer || !feedContainer || !lastSyncedLabel) {
                return;
            }

            let refreshInFlight = false;

            const refreshFeed = async () => {
                if (refreshInFlight || document.hidden) {
                    return;
                }

                refreshInFlight = true;

                try {
                    const response = await fetch(pollUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();

                    if (payload.summary_html) {
                        summaryContainer.innerHTML = payload.summary_html;
                    }

                    if (payload.feed_html) {
                        feedContainer.innerHTML = payload.feed_html;
                    }

                    if (payload.synced_at) {
                        lastSyncedLabel.textContent = `Last synced ${payload.synced_at}`;
                    }
                } catch (error) {
                    console.warn('Unable to refresh staff notifications feed.', error);
                } finally {
                    refreshInFlight = false;
                }
            };

            window.setInterval(refreshFeed, 30000);
        });
    </script>
@endsection
