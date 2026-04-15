<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Create Prescription</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="card module-surface">
                <div class="card-body">
                    <form method="POST" action="{{ route('prescriptions.store') }}">
                        @include('prescriptions._form')
                        <button class="btn btn-primary mt-3">Save Prescription</button>
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary mt-3">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
