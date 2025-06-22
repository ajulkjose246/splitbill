<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bills - SplitBill</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('/css/dashboard.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <a href="/dashboard" class="logo">
                <i class="fas fa-receipt"></i> SplitBill
            </a>
            <div class="user-info">
                <a href="/dashboard" class="logout-btn" style="text-decoration: none;">
                    <i class="fas fa-home"></i> Home
                </a>
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <span>{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="welcome-section">
            <h1>My Bills</h1>
            <p>Manage your shared expenses and track payments.</p>
        </div>

        @if(session('success'))
            <div class="success-message">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="dashboard-grid">
            

            @if($allBills->count() > 0)
                @foreach($allBills as $bill)
                    <div class="dashboard-card bill-card">
                        <div class="bill-header">
                            <div class="bill-icon">
                                @if($bill->created_by == Auth::id())
                                    <i class="fas fa-crown" style="color: #f59e0b;"></i>
                                @else
                                    <i class="fas fa-receipt"></i>
                                @endif
                            </div>
                            <div class="bill-status">
                                @if($bill->created_by == Auth::id())
                                    <span class="status-badge creator">You Created</span>
                                @else
                                    <span class="status-badge participant">Participant</span>
                                @endif
                            </div>
                        </div>
                        
                        <h3 class="bill-title">{{ $bill->name }}</h3>
                        
                        <div class="bill-details">
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <span>{{ $bill->participants->count() }} participants</span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span>
                                    @if($bill->created_by == Auth::id())
                                        You created this bill
                                    @else
                                        Created by {{ $bill->creator->name }}
                                    @endif
                                </span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>{{ $bill->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="bill-actions">
                            <a href="/bills/{{ $bill->id }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            @if($bill->created_by == Auth::id())
                                <button class="btn btn-secondary" onclick="openEditModal({{ $bill->id }}, '{{ $bill->name }}', {{ json_encode($bill->participants->where('user_id', '!=', Auth::id())->pluck('user.email')) }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if($allBills->count() == 0)
            <div style="text-align: center; padding: 3rem; background: #f8fafc; border-radius: 16px; margin-top: 2rem;">
                <i class="fas fa-receipt" style="font-size: 3rem; color: #6b7280; margin-bottom: 1rem;"></i>
                <h3>No bills yet</h3>
                <p>Start by creating your first bill or ask someone to add you to their bill.</p>
                <a href="/dashboard" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Create Your First Bill
                </a>
            </div>
        @endif
    </div>

    <!-- Edit Bill Modal -->
    <div class="edit-modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Bill</h3>
                <button class="close-modal" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editBillForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editBillId" name="bill_id">
                    
                    <div class="form-group">
                        <label for="editBillName" class="form-label">Bill Name</label>
                        <input type="text" id="editBillName" name="bill_name" class="form-input" 
                               placeholder="e.g., Goa Trip, Dinner at XYZ, Monthly Rent" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Participants</label>
                        <div class="participants-container">
                            <div id="editParticipantsList">
                                <!-- Participant inputs will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-secondary" onclick="addEditParticipant()">
                                <i class="fas fa-plus"></i> Add Participant
                            </button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Bill
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html> 