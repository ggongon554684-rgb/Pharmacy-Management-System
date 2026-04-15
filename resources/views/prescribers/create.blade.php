<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Create Prescriber</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="card module-surface">
                <div class="card-body">
                    <form method="POST" action="{{ route('prescribers.store') }}">
                        @include('prescribers._form')
                        <button class="btn btn-primary mt-3">Save Prescriber</button>
                        <a href="{{ route('prescribers.index') }}" class="btn btn-outline-secondary mt-3">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
