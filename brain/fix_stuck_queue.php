<?php

// Fix Stuck in Queue screen issues

$viewFile = 'resources/views/admin/lead-queue.blade.php';
$content = file_get_contents($viewFile);

echo "Fixing Stuck in Queue screen...\n\n";

// 1. Add horizontal scroll for table
$tableOld = '<table class="table">';
$tableNew = '<div style="overflow-x: auto; width: 100%;">
        <table class="table" style="min-width: 800px;">';

$content = str_replace($tableOld, $tableNew, $content);

// Close the scroll div after table
$tableEndOld = '</table>';
$tableEndNew = '</table>
    </div>';

$content = str_replace($tableEndOld, $tableEndNew, $content);
echo "✓ Added horizontal scroll to table\n";

// 2. Add Date column to table header
$headerOld = '<th>Lead Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Actions</th>';

$headerNew = '<th>Lead Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Source</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>';

$content = str_replace($headerOld, $headerNew, $content);
echo "✓ Added Date column to header\n";

// 3. Add Date data to table rows
$rowDataOld = '<td>{{ $item->source }}</td>
                    <td>
                        <span class="badge badge-warning">Pending</span>
                    </td>
                    <td>';

$rowDataNew = '<td>{{ $item->source }}</td>
                    <td>
                        @if($item->created_at)
                            {{ $item->created_at->format(\'m/d/Y H:i\') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-warning">Pending</span>
                    </td>
                    <td>';

$content = str_replace($rowDataOld, $rowDataNew, $content);
echo "✓ Added Date column data\n";

// 4. Fix Lead Detail modal button
$modalButtonOld = '<button class="btn btn-sm btn-info" onclick="viewLeadDetails({{ $item->id }})">Details</button>';
$modalButtonNew = '<button class="btn btn-sm btn-info" onclick="viewLeadDetails(\'{{ $item->external_lead_id ?? $item->id }}\', \'{{ addslashes($item->lead_name) }}\', \'{{ $item->phone }}\', \'{{ $item->email }}\', \'{{ $item->source }}\', \'{{ $item->created_at }}\')">Details</button>';

$content = str_replace($modalButtonOld, $modalButtonNew, $content);
echo "✓ Fixed Lead Detail button\n";

// 5. Add modal and JavaScript for Lead Details
$modalJS = '
<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || \'Not provided\'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById(\'leadDetailsContent\').innerHTML = content;
    $(\'#leadDetailsModal\').modal(\'show\');
}

function processLead() {
    alert(\'Processing lead... (This feature will send the lead to Vici)\');
    $(\'#leadDetailsModal\').modal(\'hide\');
}
</script>';

// Add modal before @endsection
$content = str_replace('@endsection', $modalJS . "\n@endsection", $content);
echo "✓ Added Lead Details modal\n";

// Write the fixed content back
file_put_contents($viewFile, $content);

echo "\n✅ Stuck in Queue screen fixed:\n";
echo "  - Added horizontal scroll for narrow screens\n";
echo "  - Added Date column showing when lead was created\n";
echo "  - Fixed Lead Detail button to show modal\n";
echo "  - Added modal with lead information\n";

