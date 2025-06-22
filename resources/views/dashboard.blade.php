<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SplitBill</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('/css/dashboard.css') }}" rel="stylesheet">
    <style>
        /* Modal Styles - Dark Theme based on image */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 20, 0.7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: #212330;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            width: 90%;
            max-width: 520px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            transform: scale(0.95);
            transition: transform 0.3s ease;
            margin: 3% auto;
            color: white;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        #createBillForm {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-height: 0;
        }

        .modal-body {
            padding: 0 1.5rem 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .form-input {
            width: 100%;
            background: #2d2f3f;
            border: 1px solid #4a4c5a;
            border-radius: 8px;
            padding: 0.875rem 1rem;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: #2d2f3f;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .participants-section {
            background: transparent;
            border: none;
            padding: 0;
        }

        .participants-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .participants-title {
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .participants-count {
            background: #28a745;
            color: white;
            padding: 0.125rem 0.625rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .participants-container {
            background: #2d2f3f;
            border-radius: 8px;
            padding: 0.75rem;
            max-height: 150px;
            overflow-y: auto;
        }

        .participant-input {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }
        .participant-input:last-child {
            margin-bottom: 0;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .participant-input input {
            flex: 1;
            background: #2d2f3f;
            border: 1px solid #4a4c5a;
            border-radius: 6px;
            padding: 0.75rem;
            color: #fff;
            transition: all 0.3s ease;
        }

        .participant-input input:focus {
            background: #2d2f3f; 
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .remove-participant {
            padding: 0.75rem;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-participant:hover {
            background: #fecaca;
        }

        .add-participant {
            padding: 0.75rem 1.25rem;
            background: #d1fae5;
            color: #065f46;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-participant:hover {
            background: #a7f3d0;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            justify-content: flex-end;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #4f46e5, #7c3aed);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background: #393b4a;
            color: white;
            border: 1px solid #4a4c5a;
        }

        .btn-secondary:hover {
            background: #4a4c5a;
        }

        .error {
            color: #fca5a5;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error::before {
            content: '⚠️';
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
            .modal-footer {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }

        /* Add slideOut animation */
        @keyframes slideOut {
            to { opacity: 0; transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <a href="/home" class="logo">
                <i class="fas fa-receipt"></i> SplitBill
            </a>
            <div class="user-info">
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

        @if(session('success'))
            <div class="success-message">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="welcome-section">
            <h1>Welcome to SplitBill, {{ Auth::user()->name }}!</h1>
            <p>Start managing your shared expenses and split bills with ease.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3 class="card-title">Create New Bill</h3>
                <p class="card-description">Add a new expense and split it among your group members.</p>
                <button onclick="openModal()" class="card-btn">Create Bill</button>
            </div>

            

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="card-title">View Bills</h3>
                <p class="card-description">See all your bills and track payments.</p>
                <a href="{{ route('bills.index') }}" class="card-btn">View Bills</a>
            </div>
        </div>
    </div>

    <!-- Create Bill Modal -->
    <div id="createBillModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-file-invoice"></i>&nbsp;Create New Bill</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('bills.store') }}" id="createBillForm">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="error">
                            <strong>Please fix the following errors:</strong>
                            <ul style="margin: 0.5rem 0 0 1rem;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    

                    <div class="form-group">
                        <label for="bill_name" class="form-label">Bill Name</label>
                        <input type="text" id="bill_name" name="bill_name" class="form-input" 
                               placeholder="Trip , Dinner , Rent" value="{{ old('bill_name') }}" required>
                        @error('bill_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    

                    <div class="form-group">
                        <div class="participants-section">
                            <div class="participants-header">
                                <div class="participants-title">
                                    <i class="fas fa-users"></i>
                                    Participants
                                </div>
                                <div class="participants-count" id="participantsCount">2</div>
                            </div>
                            <div class="participants-container" id="participantsContainer">
                                @if(old('participants'))
                                    @foreach(old('participants') as $index => $participant)
                                        <div class="participant-input">
                                            <input type="email" name="participants[]" placeholder="Enter email address" 
                                                   value="{{ $participant }}" required>
                                            <button type="button" class="remove-participant" onclick="removeParticipant(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="participant-input">
                                        <input type="email" name="participants[]" placeholder="Enter email address" required>
                                        <button type="button" class="remove-participant" onclick="removeParticipant(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="participant-input">
                                        <input type="email" name="participants[]" placeholder="Enter email address" required>
                                        <button type="button" class="remove-participant" onclick="removeParticipant(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="add-participant" onclick="addParticipant()">
                                <i class="fas fa-plus"></i> Add Participant
                            </button>
                        </div>
                        @error('participants')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Bill
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openModal();
            });
        </script>
    @endif

    <script>
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
        window.onclick = function(event) {
            const modal = document.getElementById('createBillModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
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
        document.addEventListener('DOMContentLoaded', function() {
            updateParticipantsCount();
        });
    </script>
</body>
</html> 