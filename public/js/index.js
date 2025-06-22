function openEditModal(billId, billName, participants) {
    document.getElementById('editBillId').value = billId;
    document.getElementById('editBillName').value = billName;
    
    const participantsList = document.getElementById('editParticipantsList');
    participantsList.innerHTML = '';
    
    // Add existing participants
    participants.forEach(email => {
        addEditParticipant(email);
    });
    
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.getElementById('editBillForm').reset();
}

function addEditParticipant(email = '') {
    const participantsList = document.getElementById('editParticipantsList');
    const participantDiv = document.createElement('div');
    participantDiv.className = 'participant-input';
    participantDiv.innerHTML = `
        <input type="email" name="participants[]" class="form-input" 
               placeholder="Enter email address" value="${email}" required>
        <button type="button" class="remove-participant" onclick="removeEditParticipant(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    participantsList.appendChild(participantDiv);
}

function removeEditParticipant(button) {
    button.parentElement.remove();
}

// Handle form submission
document.getElementById('editBillForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const billId = document.getElementById('editBillId').value;
    
    fetch(`/bills/${billId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            // Reload the page to show updated data
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update bill'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the bill.');
    });
});

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});