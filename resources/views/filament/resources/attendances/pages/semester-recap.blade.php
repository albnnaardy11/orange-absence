<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Semester {{ $semester }} Attendance Summary
            </x-slot>
            
            <x-slot name="description">
                Comprehensive attendance statistics for all members in the current semester.
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">Member Name</th>
                            <th class="px-6 py-3">Division</th>
                            <th class="px-6 py-3">Total</th>
                            <th class="px-6 py-3">Hadir</th>
                            <th class="px-6 py-3">Izin</th>
                            <th class="px-6 py-3">Sakit</th>
                            <th class="px-6 py-3">Alfa</th>
                            <th class="px-6 py-3">Rate (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-6 py-4 font-medium">{{ $row['name'] }}</td>
                                <td class="px-6 py-4">{{ $row['division'] }}</td>
                                <td class="px-6 py-4">{{ $row['total'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $row['hadir'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ $row['izin'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $row['sakit'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ $row['alfa'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold">{{ $row['rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No attendance data available for this semester.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>

