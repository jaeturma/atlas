@props(['id' => 'dataTable', 'columns' => [], 'data' => []])

<table id="{{ $id }}" class="w-full border-collapse">
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column['label'] ?? ucfirst($column['name'] ?? '') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                @foreach($columns as $column)
                    <td>
                        @if(isset($column['render']))
                            {!! $column['render']($row) !!}
                        @else
                            {{ data_get($row, $column['name'] ?? '') }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const config = {
            responsive: true,
            language: {
                search: "Filter:",
                lengthMenu: "Show _MENU_ rows per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: "No data available in table",
                zeroRecords: "No matching records found"
            },
            dom: '<"flex flex-col sm:flex-row gap-3 mb-4 items-center justify-between"<"flex items-center gap-2"l>f>t<"flex flex-col sm:flex-row gap-3 items-center justify-between"ip>',
            pageLength: 10,
            lengthMenu: [[5, 10, 15, 25, 50, 100], [5, 10, 15, 25, 50, 100]],
        };

        let table = new DataTable('#{{ $id }}', config);
    });
</script>
