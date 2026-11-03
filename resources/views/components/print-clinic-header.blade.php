<header class="print-clinic-header">
    <img src="{{ asset('images/Test.jpg') }}" alt="" class="print-clinic-logo">
    <div>
        <h1>{{ \App\Models\SystemSetting::getValue('clinic_name', config('super-admin.settings.clinic_name')) }}</h1>
        <p>{{ \App\Models\SystemSetting::getValue('clinic_address', config('super-admin.settings.clinic_address')) }}</p>
        <p>{{ \App\Models\SystemSetting::getValue('clinic_phone', config('super-admin.settings.clinic_phone')) }}</p>
    </div>
</header>
