jQuery(document).ready(function($) {
    let entriesMap = {};

    function esc(val) {
        return String(val ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

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
        entriesMap = {};
        entries.forEach(entry => { entriesMap[entry.id] = entry; });

        let html = `
            <div style="overflow-x: auto;">
                <table class="hod-table">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Start Date</th>
                            <th>Phone</th>
                            <th>ICE Name</th>
                            <th>ICE Phone</th>
                            <th>Bank Reg Nr</th>
                            <th>Bank Account Nr</th>
                            <th>Tax Type</th>
                            <th>Teaching Degree</th>
                            <th>Pedagogue Degree</th>
                            <th>Misc</th>
                            <th>Consent</th>
                            <th>Status</th>
                            <th>Keys 30</th>
                            <th>Keys 0</th>
                            <th>Keys Music</th>
                            <th>Keys Gym</th>
                            <th>Flowers</th>
                            <th>Flower Delivery</th>
                            <th>Laptop</th>
                            <th>Temp Employment</th>
                            <th>Temp Reason</th>
                            <th>Employment Level</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        entries.forEach(entry => {
            html += `
                <tr>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <button class="hod-edit-btn" data-id="${entry.id}">Edit</button>
                            <button class="hod-send-btn" data-id="${entry.id}">Send</button>
                        </div>
                    </td>
                    <td>${esc(entry.id)}</td>
                    <td>${esc(entry.name)}</td>
                    <td>${esc(entry.email)}</td>
                    <td>${esc(entry.start_date)}</td>
                    <td>${esc(entry.phone)}</td>
                    <td>${esc(entry.ice_name)}</td>
                    <td>${esc(entry.ice_phone)}</td>
                    <td>${esc(entry.bank_reg_nr)}</td>
                    <td>${esc(entry.bank_account_nr)}</td>
                    <td>${esc(entry.tax_type)}</td>
                    <td>${esc(entry.teaching_degree)}</td>
                    <td>${esc(entry.pedagogue_degree)}</td>
                    <td>${esc(entry.misc)}</td>
                    <td>${entry.consent ? 'Yes' : 'No'}</td>
                    <td>${esc(entry.status)}</td>
                    <td>${entry.keys_30 ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_0 ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_music ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_gym ? 'Yes' : 'No'}</td>
                    <td>${entry.flowers === 'yes' ? 'Yes' : 'No'}</td>
                    <td>${esc(entry.flower_delivery)}</td>
                    <td>${entry.laptop === 'yes' ? 'Yes' : 'No'}</td>
                    <td>${entry.temp_employment === 'yes' ? 'Yes' : 'No'}</td>
                    <td>${esc(entry.temp_reason)}</td>
                    <td>${esc(entry.employment_level)}</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
        $('#entries-table').html(html);
    }

    $('#entries-table').on('click', '.hod-edit-btn', function() {
        editEntry($(this).data('id'));
    });

    $('#entries-table').on('click', '.hod-send-btn', function() {
        sendEntry($(this).data('id'));
    });

    function editEntry(entryId) {
        const entry = entriesMap[entryId];
        if (!entry) return;

        const modalHtml = `
            <div id="hod-modal" class="hod-modal">
                <h3>Edit Entry #${esc(entryId)}</h3>
                <form id="hod-edit-form">
                    <div class="form-field">
                        <label>Name</label>
                        <input name="name-1" value="${esc(entry.name)}">
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <input name="email-1" value="${esc(entry.email)}">
                    </div>
                    <div class="form-field">
                        <label>Start Date</label>
                        <input name="date-1" value="${esc(entry.start_date)}">
                    </div>
                    <div class="form-field">
                        <label>Phone</label>
                        <input name="phone" value="${esc(entry.phone)}">
                    </div>
                    <div class="form-field">
                        <label>ICE Name</label>
                        <input name="ice_name" value="${esc(entry.ice_name)}">
                    </div>
                    <div class="form-field">
                        <label>ICE Phone</label>
                        <input name="ice_phone" value="${esc(entry.ice_phone)}">
                    </div>
                    <div class="form-field">
                        <label>Bank Reg Nr</label>
                        <input name="bank_reg_nr" value="${esc(entry.bank_reg_nr)}">
                    </div>
                    <div class="form-field">
                        <label>Bank Account Nr</label>
                        <input name="bank_account_nr" value="${esc(entry.bank_account_nr)}">
                    </div>
                    <div class="form-field">
                        <label>Tax Type</label>
                        <div class="radio-group">
                            <input type="radio" name="tax_type" value="hoved" ${entry.tax_type === 'hoved' ? 'checked' : ''}> Hoved
                            <input type="radio" name="tax_type" value="bikort" ${entry.tax_type === 'bikort' ? 'checked' : ''}> Bikort
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Teaching Degree</label>
                        <div class="radio-group">
                            <input type="radio" name="teaching_degree" value="yes" ${entry.teaching_degree === 'yes' ? 'checked' : ''}> Yes
                            <input type="radio" name="teaching_degree" value="no" ${entry.teaching_degree === 'no' ? 'checked' : ''}> No
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Pedagogue Degree</label>
                        <div class="radio-group">
                            <input type="radio" name="pedagogue_degree" value="yes" ${entry.pedagogue_degree === 'yes' ? 'checked' : ''}> Yes
                            <input type="radio" name="pedagogue_degree" value="no" ${entry.pedagogue_degree === 'no' ? 'checked' : ''}> No
                        </div>
                    </div>
                    <div class="form-field full-width">
                        <label>Misc</label>
                        <textarea name="misc">${esc(entry.misc)}</textarea>
                    </div>
                    <div class="form-field full-width">
                        <label><input type="checkbox" name="consent" ${entry.consent ? 'checked' : ''}> Consent</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_30" ${entry.keys_30 ? 'checked' : ''}> Keys 30</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_0" ${entry.keys_0 ? 'checked' : ''}> Keys 0</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_music" ${entry.keys_music ? 'checked' : ''}> Keys Music</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_gym" ${entry.keys_gym ? 'checked' : ''}> Keys Gym</label>
                    </div>
                    <div class="form-field">
                        <label>Flowers</label>
                        <select name="flowers">
                            <option value="no" ${entry.flowers === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${entry.flowers === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Flower Delivery</label>
                        <select name="flower_delivery">
                            <option value="home" ${entry.flower_delivery === 'home' ? 'selected' : ''}>Home</option>
                            <option value="school" ${entry.flower_delivery === 'school' ? 'selected' : ''}>School</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Laptop</label>
                        <select name="laptop">
                            <option value="no" ${entry.laptop === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${entry.laptop === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Temp Employment</label>
                        <select name="temp_employment">
                            <option value="no" ${entry.temp_employment === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${entry.temp_employment === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Temp Reason</label>
                        <input name="temp_reason" value="${esc(entry.temp_reason)}">
                    </div>
                    <div class="form-field">
                        <label>Employment Level</label>
                        <input name="employment_level" value="${esc(entry.employment_level ?? '0')}">
                    </div>
                    <div class="form-field full-width">
                        <button type="submit">Save</button>
                        <button type="button" id="hod-modal-cancel">Cancel</button>
                    </div>
                </form>
            </div>
        `;
        $('body').append(modalHtml);

        $('#hod-modal-cancel').on('click', function() {
            $('#hod-modal').remove();
        });

        $('#hod-edit-form').on('submit', function(e) {
            e.preventDefault();
            const updates = {
                'name-1': $('input[name="name-1"]').val(),
                'email-1': $('input[name="email-1"]').val(),
                'date-1': $('input[name="date-1"]').val(),
                'phone': $('input[name="phone"]').val(),
                'ice_name': $('input[name="ice_name"]').val(),
                'ice_phone': $('input[name="ice_phone"]').val(),
                'bank_reg_nr': $('input[name="bank_reg_nr"]').val(),
                'bank_account_nr': $('input[name="bank_account_nr"]').val(),
                'tax_type': $('input[name="tax_type"]:checked').val(),
                'teaching_degree': $('input[name="teaching_degree"]:checked').val(),
                'pedagogue_degree': $('input[name="pedagogue_degree"]:checked').val(),
                'misc': $('textarea[name="misc"]').val(),
                'consent': $('input[name="consent"]').is(':checked') ? 1 : 0,
                'keys_30': $('input[name="keys_30"]').is(':checked') ? 1 : 0,
                'keys_0': $('input[name="keys_0"]').is(':checked') ? 1 : 0,
                'keys_music': $('input[name="keys_music"]').is(':checked') ? 1 : 0,
                'keys_gym': $('input[name="keys_gym"]').is(':checked') ? 1 : 0,
                'flowers': $('select[name="flowers"]').val(),
                'flower_delivery': $('select[name="flower_delivery"]').val(),
                'laptop': $('select[name="laptop"]').val(),
                'temp_employment': $('select[name="temp_employment"]').val(),
                'temp_reason': $('input[name="temp_reason"]').val(),
                'employment_level': $('input[name="employment_level"]').val()
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
    }

    function sendEntry(entryId) {
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
    }
});
