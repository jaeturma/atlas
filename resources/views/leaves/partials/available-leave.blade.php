<div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
    @php
        $sampleLeaves = [
            ['name' => 'Vacation Leave', 'count' => 10.00],
            ['name' => 'Sick Leave', 'count' => 10.00],
            ['name' => 'Special Leave', 'count' => 3.00],
            ['name' => 'Forced Leave', 'count' => 5.00],
            ['name' => 'CTO Leave', 'count' => 2.00],
        ];
        $sampleTotal = collect($sampleLeaves)->sum('count');
    @endphp
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-blue-200" id="availableLeaveTable">
            <thead>
                <tr>
                    <th colspan="2" class="px-2 py-2 text-left text-blue-900 font-semibold bg-blue-100 border-b border-blue-200">Available Leave Credits</th>
                </tr>
            </thead>
            <tbody class="text-blue-900">
                @foreach ($sampleLeaves as $index => $item)
                    <tr class="border-b border-blue-100" data-leave-row="{{ $index }}" data-base-count="{{ number_format($item['count'], 2, '.', '') }}">
                        <td class="px-2 py-1">{{ $item['name'] }}</td>
                        <td class="px-2 py-1 text-right font-semibold" data-leave-count>{{ number_format($item['count'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-blue-100" data-total-row>
                    <td class="px-2 py-1 font-semibold">Total</td>
                    <td class="px-2 py-1 text-right font-semibold" data-total-count>{{ number_format($sampleTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        if (window.__leaveCreditsSampleMounted) {
            return;
        }
        window.__leaveCreditsSampleMounted = true;

        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const periodSelect = document.getElementById('leave_period');
        const table = document.getElementById('availableLeaveTable');
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('[data-leave-row]'));
        const totalCell = table.querySelector('[data-total-count]');

        const toDate = (value) => {
            if (!value) return null;
            const [year, month, day] = value.split('-').map(Number);
            return new Date(year, (month || 1) - 1, day || 1);
        };

        const diffDaysInclusive = (start, end) => {
            const ms = 24 * 60 * 60 * 1000;
            const utcStart = Date.UTC(start.getFullYear(), start.getMonth(), start.getDate());
            const utcEnd = Date.UTC(end.getFullYear(), end.getMonth(), end.getDate());
            return Math.max(1, Math.round((utcEnd - utcStart) / ms) + 1);
        };

        const computeRequestedDays = () => {
            const start = toDate(startInput?.value);
            const end = toDate(endInput?.value) || start;
            const period = periodSelect?.value || 'full';

            if (!start) return 0;
            if (period === 'morning' || period === 'afternoon') return 0.5;
            return diffDaysInclusive(start, end);
        };

        const updateSampleCredits = () => {
            const requested = computeRequestedDays();
            let total = 0;

            rows.forEach((row, index) => {
                const base = parseFloat(row.getAttribute('data-base-count') || '0');
                let adjusted = base;

                if (index === 0 && requested > 0) {
                    adjusted = Math.max(0, base - requested);
                }

                total += adjusted;
                const cell = row.querySelector('[data-leave-count]');
                if (cell) {
                    cell.textContent = adjusted.toFixed(2);
                }
            });

            if (totalCell) {
                totalCell.textContent = total.toFixed(2);
            }
        };

        ['change', 'input'].forEach((evt) => {
            startInput?.addEventListener(evt, updateSampleCredits);
            endInput?.addEventListener(evt, updateSampleCredits);
            periodSelect?.addEventListener(evt, updateSampleCredits);
        });

        updateSampleCredits();
    })();
</script>
