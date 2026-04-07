<x-app-layout>
    <x-slot name="header"><h2 class="h4 mb-0">Stock Requests</h2></x-slot>
    <div class="py-4">
        <div class="container-fluid">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @can('create stock requests')
                <a href="{{ route('stock-requests.create') }}" class="btn btn-primary btn-sm mb-3">Request Medicine</a>
            @endcan
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>Product</th><th>Qty</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($stockRequests as $request)
                                <tr>
                                    <td>{{ $request->product->name }}</td>
                                    <td>{{ $request->quantity }}</td>
                                    <td>
                                        <span class="badge {{
                                            $request->status === 'pending' ? 'bg-warning text-dark' :
                                            ($request->status === 'fulfilled' ? 'bg-success' :
                                            ($request->status === 'approved' ? 'bg-primary' : 'bg-secondary'))
                                        }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @can('approve stock release')
                                            @if($request->status === 'pending')
                                                <form method="POST" action="{{ route('stock-requests.approve', $request) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Approve & Fulfill</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $stockRequests->links() }}</div>
        </div>
    </div>
</x-app-layout>
