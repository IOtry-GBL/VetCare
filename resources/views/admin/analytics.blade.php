@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('admin.users') }}" class="sidebar-item">User Management</a>
    <a href="{{ route('admin.analytics') }}" class="sidebar-item active">Analytics</a>
@endsection

@section('content')
    <div class="card">
        <h1>System Analytics (AI-Powered)</h1>
        <p>Comprehensive analytics and trend analysis powered by AI.</p>
    </div>

    <div class="grid">
        <div class="widget">
            <div class="widget-title">Total Consultations</div>
            <div class="widget-value">248</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">+12% vs. last month</p>
        </div>

        <div class="widget">
            <div class="widget-title">Avg. Response Time</div>
            <div class="widget-value">45min</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Down from 52 min</p>
        </div>

        <div class="widget">
            <div class="widget-title">Patient Satisfaction</div>
            <div class="widget-value">4.8/5</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Based on 127 reviews</p>
        </div>

        <div class="widget">
            <div class="widget-title">Med. Adherence</div>
            <div class="widget-value">87%</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Follow-up compliance rate</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
        <!-- Symptom Trends -->
        <div class="card">
            <h2>Most Common Symptoms (AI Analysis)</h2>
            <div style="display: grid; gap: 1rem;">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Scratching & Itching</strong>
                        <span>28%</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #667eea; height: 100%; width: 28%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Vomiting & Diarrhea</strong>
                        <span>22%</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #f59e0b; height: 100%; width: 22%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Lethargy & Weakness</strong>
                        <span>18%</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #10b981; height: 100%; width: 18%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Coughing & Sneezing</strong>
                        <span>15%</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #3b82f6; height: 100%; width: 15%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Other</strong>
                        <span>17%</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #8b5cf6; height: 100%; width: 17%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peak Hours -->
        <div class="card">
            <h2>Appointment Peak Hours</h2>
            <div style="display: grid; gap: 1rem;">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>09:00 - 10:00 AM</strong>
                        <span>18 bookings</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #667eea; height: 100%; width: 100%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>10:00 - 11:00 AM</strong>
                        <span>15 bookings</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #667eea; height: 100%; width: 83%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>02:00 - 03:00 PM</strong>
                        <span>14 bookings</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #667eea; height: 100%; width: 78%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>03:00 - 04:00 PM</strong>
                        <span>12 bookings</span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; overflow: hidden;">
                        <div style="background: #667eea; height: 100%; width: 67%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Medication Adherence Prediction</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="padding: 1rem; background: #dcfce7; border-radius: 0.375rem;">
                <strong>High Adherence</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; color: var(--color-monitor); font-weight: bold;">78%</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Pets with >90% compliance</p>
            </div>

            <div style="padding: 1rem; background: #fef3c7; border-radius: 0.375rem;">
                <strong>Medium Adherence</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; color: var(--color-visit); font-weight: bold;">15%</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Pets with 70-90% compliance</p>
            </div>

            <div style="padding: 1rem; background: #fee2e2; border-radius: 0.375rem;">
                <strong>Low Adherence</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; color: var(--color-emergency); font-weight: bold;">7%</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Pets with <70% compliance</p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Key Insights & Recommendations</h2>
        <div style="display: grid; gap: 1rem;">
            <div style="padding: 1rem; background: #dbeafe; border-radius: 0.375rem; border-left: 4px solid #667eea;">
                <strong>Peak Hours Analysis</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #1e40af;">Schedule more vets during 9-11 AM slots to reduce wait times. Current 45-min average can be improved to 35 min.</p>
            </div>

            <div style="padding: 1rem; background: #dbeafe; border-radius: 0.375rem; border-left: 4px solid #667eea;">
                <strong>Medication Reminder Program</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #1e40af;">AI predicts 7% of patients at risk of non-adherence. Implement daily reminder notifications to improve compliance by 20%.</p>
            </div>

            <div style="padding: 1rem; background: #dbeafe; border-radius: 0.375rem; border-left: 4px solid #667eea;">
                <strong>Symptom Patterns</strong>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #1e40af;">Scratching & itching cases increased by 35% in February. Recommend focused awareness campaign on allergen prevention.</p>
            </div>
        </div>
    </div>
@endsection
