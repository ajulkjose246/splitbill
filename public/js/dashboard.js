// Modal functions
function openModal() {
    const modal = document.getElementById('createBillModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    // Focus on first input
    setTimeout(() => {
        document.getElementById('bill_name').focus();
    }, 300);
}

function closeModal() {
    const modal = document.getElementById('createBillModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    document.body.style.overflow = 'auto';
    // Reset form
    document.getElementById('createBillForm').reset();
    updateParticipantsCount();
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('createBillModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Participant management functions
function addParticipant() {
    const container = document.getElementById('participantsContainer');
    const participantDiv = document.createElement('div');
    participantDiv.className = 'participant-input';
    participantDiv.innerHTML = `
        <input type="email" name="participants[]" placeholder="Enter email address" required>
        <button type="button" class="remove-participant" onclick="removeParticipant(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(participantDiv);
    updateParticipantsCount();

    // Focus on the new input
    const newInput = participantDiv.querySelector('input');
    newInput.focus();
}

function removeParticipant(button) {
    const container = document.getElementById('participantsContainer');
    if (container.children.length > 1) {
        const participantDiv = button.parentElement;
        participantDiv.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            participantDiv.remove();
            updateParticipantsCount();
        }, 300);
    } else {
        // Show a message that at least one participant is required
        alert('At least one participant is required. You can add more participants using the "Add Participant" button.');
    }
}

function updateParticipantsCount() {
    const container = document.getElementById('participantsContainer');
    const count = container.children.length;
    const countElement = document.getElementById('participantsCount');
    countElement.textContent = count;
}

// Initialize participant count
document.addEventListener('DOMContentLoaded', function () {
    updateParticipantsCount();
});