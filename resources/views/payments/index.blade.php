<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Payments
        </h2>
    </x-slot>

    <div class="container mx-auto p-6">
        {{-- Success --}}
        @if (session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Upload card --}}
        <div class="mb-6 rounded-lg border bg-white p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <form method="POST" action="{{ route('payments.upload.web') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block  font-medium text-gray-700 dark:text-gray-200">
                        Upload CSV
                    </label>

                    <input
                        type="file"
                        name="file"
                        accept=".csv,text/csv"
                        required
                        class="mt-2 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                               focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500
                               dark:bg-gray-900 dark:border-gray-600 dark:text-gray-200" />

                    <p class="mt-2  text-gray-500">
                        Required columns: customer_id, customer_name, customer_email, amount, currency, reference_no, date_time
                    </p>
                </div>
                <button
                    type="submit"
                    style="background:#2563eb;color:white;padding:8px 16px;border-radius:6px;">
                    Upload &amp; Process
                </button>

            </form>
        </div>




        <div
            class="relative flex flex-col w-full h-full overflow-scroll text-gray-700 bg-white shadow-md rounded-xl bg-clip-border">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            ID
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            File
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            Status
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            Total
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            Success
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            Failed
                        </td>
                        <td class="border-b px-3 py-2 text-black font-semibold dark:text-white">
                            Created
                        </td>

                    </tr>
                </thead>
                <tbody>
                    @forelse($uploads as $u)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->id }}</td>
                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->original_filename }}</td>

                        <td class="border-b px-3 py-2 dark:border-gray-700">
                            @php
                            $badge = match($u->status) {
                            'COMPLETED' => 'bg-green-100 text-green-800',
                            'FAILED' => 'bg-red-100 text-red-800',
                            'PROCESSING' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-gray-100 text-gray-800',
                            };
                            @endphp
                            <span class="inline-flex rounded px-2 py-1 text-xs font-semibold {{ $badge }}">
                                {{ $u->status }}
                            </span>
                        </td>

                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->total_rows }}</td>
                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->success_rows }}</td>
                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->failed_rows }}</td>
                        <td class="border-b px-3 py-2 dark:border-gray-700">{{ $u->created_at }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                            No uploads yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="mt-4">
            {{ $uploads->links() }}
        </div>

    </div>
</x-app-layout>