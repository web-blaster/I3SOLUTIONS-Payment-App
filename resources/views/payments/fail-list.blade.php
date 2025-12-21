<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Failed Payment Records
        </h2>
    </x-slot>

    <div class="container mx-auto p-6">

        <div class="relative w-full overflow-x-auto bg-white shadow-md rounded-xl dark:bg-gray-800">
            <table class="w-full text-left table-auto min-w-max">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">ID</th>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">File</th>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">Status</th>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">Row #</th>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">Error</th>
                        <th class="border-b px-3 py-2 text-black font-semibold dark:text-white">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($failList as $u)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="border-b px-3 py-2">{{ $u->id }}</td>

                            <td class="border-b px-3 py-2">
                                {{ $u->upload->original_filename ?? '-' }}
                            </td>

                            <td class="border-b px-3 py-2">
                                <span class="inline-flex rounded px-2 py-1 text-xs font-semibold bg-red-100 text-red-800">
                                    FAILED
                                </span>
                            </td>

                            <td class="border-b px-3 py-2">
                                {{ $u->row_number }}
                            </td>

                            <td class="border-b px-3 py-2 max-w-[400px]">
                                <div class="truncate text-sm text-gray-700 dark:text-gray-300">
                                    {{ $u->message }}
                                </div>
                            </td>

                            <td class="border-b px-3 py-2">
                                {{ $u->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                No failed record found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            {{ $failList->links() }}
        </div>

    </div>
</x-app-layout>
