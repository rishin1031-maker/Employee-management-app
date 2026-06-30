@if(session('success'))
    <div class="ems-alert ems-alert-success mb-5">
        <i class="fas fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('warning'))
    <div class="ems-alert ems-alert-warning mb-5">
        <i class="fas fa-triangle-exclamation text-base"></i>
        <span>{{ session('warning') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="ems-alert ems-alert-error mb-5">
        <i class="fas fa-circle-xmark text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif
