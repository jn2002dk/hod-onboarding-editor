jQuery(document).ready(function($) {
    loadEntries();

    function loadEntries() {
        $.post(hod_ajax.ajax_url, {
            action: 'get_hod_entries',
            nonce: hod_ajax.nonce
        }, function(response) {
            if (response.success) {
                displayEntries(response.data);
            } else {
                alert('Error: ' + response.data);
            }
        });
    }

    function displayEntries(entries) {
        let html = `
            <style>
                .hod-table { width: 100%; border-collapse: collapse; }
                .hod-table th, .hod-table td { border: 1px solid #ddd; padding: 8px; }
                .hod-table th { background: #f4f4f4; }
                .hod-modal { position: fixed; top: 20%; left: 20%; width: 60%; background: #fff; padding: 20px; border: 1px solid #000; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
                .hod-modal input { width: 100%; margin: 5px 0; }
            </style>
            <table class="hod-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;
        entries.forEach(entry => {
            html += `
                <tr>
                    <td>${entry.id}</td>
                    <td>${entry.name}</td>
                    <td>${entry.email}</td>
                    <td>${entry.start_date}</td>
                    <td>${entry.status}</td>
                    <td>
                        <button onclick="editEntry(${entry.id}, '${entry.name}', '${entry.email}', '${entry.start_date}')">Edit</button>
                        <button onclick="sendEntry(${entry.id})">Send</button>
                    </td>
                </tr>
            `;
        });
        html += '</tbody></table>';
        $('#entries-table').html(html);
    }

    window.editEntry = function(entryId, name, email, start_date) {
        const modalHtml = `
            <div id="hod-modal" class="hod-modal">
                <h3>Edit Entry #${entryId}</h3>
                <form id="hod-edit-form">
                    <label>Name</label>
                    <input name="name-1" value="${name || ''}">
                    <label>Email</label>
                    <input name="email-1" value="${email || ''}">
                    <label>Start Date</label>
                    <input name="date-1" value="${start_date || ''}">
                    <button type="submit">Save</button>
                    <button type="button" onclick="jQuery('#hod-modal').remove()">Cancel</button>
                </form>
            </div>
        `;
        $('body').append(modalHtml);

        $('#hod-edit-form').on('submit', function(e) {
            e.preventDefault();
            const updates = {
                'name-1': $('input[name="name-1"]').val(),
                'email-1': $('input[name="email-1"]').val(),
                'date-1': $('input[name="date-1"]').val()
            };

            $.post(hod_ajax.ajax_url, {
                action: 'update_hod_entry',
                entry_id: entryId,
                updates: updates,
                nonce: hod_ajax.nonce
            }, function(response) {
                if (response.success) {
                    $('#hod-modal').remove();
                    loadEntries();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
    };

    window.sendEntry = function(entryId) {
        if (confirm('Send to Admin/IT?')) {
            $.post(hod_ajax.ajax_url, {
                action: 'send_hod_entry',
                entry_id: entryId,
                nonce: hod_ajax.nonce
            }, function(response) {
                if (response.success) {
                    loadEntries();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
    };
});