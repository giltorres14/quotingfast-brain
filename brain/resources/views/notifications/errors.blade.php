<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold">Notification Errors</h1>
    </x-slot>

    <div class="container mx-auto p-6">
        <div class="mb-4">
            <a href="{{ url('/notifications/errors/export') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Export CSV</a>
        </div>

        <table class="min-w-full bg-white shadow rounded mb-4">
            <thead>
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Client</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Error Message</th>
                    <th class="px-4 py-2">Attempts</th>
                    <th class="px-4 py-2">Last Attempt</th>
                    <th class="px-4 py-2">Resolved At</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($errors as $error)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $error->id }}</td>
                    <td class="px-4 py-2">{{ $error->client?->name }}</td>
                    <td class="px-4 py-2">{{ ucfirst($error->type) }}</td>
                    <td class="px-4 py-2">{{ $error->error_message }}</td>
                    <td class="px-4 py-2">{{ $error->attempts }}</td>
                    <td class="px-4 py-2">{{ $error->last_attempt_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-2">{{ $error->resolved_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <form method="POST" action="{{ url('/notifications/errors/'.$error->id.'/retry') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-500 text-white px-2 py-1 rounded">Retry</button>
                        </form>
                        <form method="POST" action="{{ url('/notifications/errors/'.$error->id.'/resolve') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Resolve</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $errors->links() }}
        </div>
    </div>
</x-app-layout> 