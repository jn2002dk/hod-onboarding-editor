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
                            <button onclick="editEntry(${entry.id}, '${entry.name}', '${entry.email}', '${entry.start_date}', '${entry.phone || ''}', '${entry.ice_name || ''}', '${entry.ice_phone || ''}', '${entry.bank_reg_nr || ''}', '${entry.bank_account_nr || ''}', '${entry.tax_type || ''}', '${entry.teaching_degree || ''}', '${entry.pedagogue_degree || ''}', '${entry.misc || ''}', ${entry.consent}, ${entry.keys_30 || 0}, ${entry.keys_0 || 0}, ${entry.keys_music || 0}, ${entry.keys_gym || 0}, '${entry.flowers || 'no'}', '${entry.flower_delivery || 'home'}', '${entry.laptop || 'no'}', '${entry.temp_employment || 'no'}', '${entry.temp_reason || ''}', '${entry.employment_level || '0'}')">Edit</button>
                            <button onclick="sendEntry(${entry.id})">Send</button>
                        </div>
                    </td>
                    <td>${entry.id}</td>
                    <td>${entry.name}</td>
                    <td>${entry.email}</td>
                    <td>${entry.start_date}</td>
                    <td>${entry.phone || ''}</td>
                    <td>${entry.ice_name || ''}</td>
                    <td>${entry.ice_phone || ''}</td>
                    <td>${entry.bank_reg_nr || ''}</td>
                    <td>${entry.bank_account_nr || ''}</td>
                    <td>${entry.tax_type || ''}</td>
                    <td>${entry.teaching_degree || ''}</td>
                    <td>${entry.pedagogue_degree || ''}</td>
                    <td>${entry.misc || ''}</td>
                    <td>${entry.consent ? 'Yes' : 'No'}</td>
                    <td>${entry.status}</td>
                    <td>${entry.keys_30 ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_0 ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_music ? 'Yes' : 'No'}</td>
                    <td>${entry.keys_gym ? 'Yes' : 'No'}</td>
                    <td>${entry.flowers == 'yes' ? 'Yes' : 'No'}</td>
                    <td>${entry.flower_delivery}</td>
                    <td>${entry.laptop == 'yes' ? 'Yes' : 'No'}</td>
                    <td>${entry.temp_employment == 'yes' ? 'Yes' : 'No'}</td>
                    <td>${entry.temp_reason || ''}</td>
                    <td>${entry.employment_level || ''}</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
        $('#entries-table').html(html);
    }

    window.editEntry = function(entryId, name, email, start_date, phone, ice_name, ice_phone, bank_reg_nr, bank_account_nr, tax_type, teaching_degree, pedagogue_degree, misc, consent, keys_30, keys_0, keys_music, keys_gym, flowers, flower_delivery, laptop, temp_employment, temp_reason, employment_level) {
        const modalHtml = `
            <div id="hod-modal" class="hod-modal">
                <h3>Edit Entry #${entryId}</h3>
                <form id="hod-edit-form">
                    <div class="form-field">
                        <label>Name</label>
                        <input name="name-1" value="${name || ''}">
                    </div>
                    <div class="form-field">
                        <label>Email</label>
                        <input name="email-1" value="${email || ''}">
                    </div>
                    <div class="form-field">
                        <label>Start Date</label>
                        <input name="date-1" value="${start_date || ''}">
                    </div>
                    <div class="form-field">
                        <label>Phone</label>
                        <input name="phone" value="${phone || ''}">
                    </div>
                    <div class="form-field">
                        <label>ICE Name</label>
                        <input name="ice_name" value="${ice_name || ''}">
                    </div>
                    <div class="form-field">
                        <label>ICE Phone</label>
                        <input name="ice_phone" value="${ice_phone || ''}">
                    </div>
                    <div class="form-field">
                        <label>Bank Reg Nr</label>
                        <input name="bank_reg_nr" value="${bank_reg_nr || ''}">
                    </div>
                    <div class="form-field">
                        <label>Bank Account Nr</label>
                        <input name="bank_account_nr" value="${bank_account_nr || ''}">
                    </div>
                    <div class="form-field">
                        <label>Tax Type</label>
                        <div class="radio-group">
                            <input type="radio" name="tax_type" value="hoved" ${tax_type === 'hoved' ? 'checked' : ''}> Hoved
                            <input type="radio" name="tax_type" value="bikort" ${tax_type === 'bikort' ? 'checked' : ''}> Bikort
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Teaching Degree</label>
                        <div class="radio-group">
                            <input type="radio" name="teaching_degree" value="yes" ${teaching_degree === 'yes' ? 'checked' : ''}> Yes
                            <input type="radio" name="teaching_degree" value="no" ${teaching_degree === 'no' ? 'checked' : ''}> No
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Pedagogue Degree</label>
                        <div class="radio-group">
                            <input type="radio" name="pedagogue_degree" value="yes" ${pedagogue_degree === 'yes' ? 'checked' : ''}> Yes
                            <input type="radio" name="pedagogue_degree" value="no" ${pedagogue_degree === 'no' ? 'checked' : ''}> No
                        </div>
                    </div>
                    <div class="form-field full-width">
                        <label>Misc</label>
                        <textarea name="misc">${misc || ''}</textarea>
                    </div>
                    <div class="form-field full-width">
                        <label><input type="checkbox" name="consent" ${consent ? 'checked' : ''}> Consent</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_30" ${keys_30 ? 'checked' : ''}> Keys 30</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_0" ${keys_0 ? 'checked' : ''}> Keys 0</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_music" ${keys_music ? 'checked' : ''}> Keys Music</label>
                    </div>
                    <div class="form-field">
                        <label><input type="checkbox" name="keys_gym" ${keys_gym ? 'checked' : ''}> Keys Gym</label>
                    </div>
                    <div class="form-field">
                        <label>Flowers</label>
                        <select name="flowers">
                            <option value="no" ${flowers === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${flowers === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Flower Delivery</label>
                        <select name="flower_delivery">
                            <option value="home" ${flower_delivery === 'home' ? 'selected' : ''}>Home</option>
                            <option value="school" ${flower_delivery === 'school' ? 'selected' : ''}>School</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Laptop</label>
                        <select name="laptop">
                            <option value="no" ${laptop === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${laptop === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Temp Employment</label>
                        <select name="temp_employment">
                            <option value="no" ${temp_employment === 'no' ? 'selected' : ''}>No</option>
                            <option value="yes" ${temp_employment === 'yes' ? 'selected' : ''}>Yes</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Temp Reason</label>
                        <input name="temp_reason" value="${temp_reason || ''}">
                    </div>
                    <div class="form-field">
                        <label>Employment Level</label>
                        <input name="employment_level" value="${employment_level || '0'}">
                    </div>
                    <div class="form-field full-width">
                        <button type="submit">Save</button>
                        <button type="button" onclick="jQuery('#hod-modal').remove()">Cancel</button>
                    </div>
                </form>
            </div>
        `;
        $('body').append(modalHtml);

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