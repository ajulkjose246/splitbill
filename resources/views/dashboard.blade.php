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
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            margin: 3% auto;
            padding: 0;
            border-radius: 20px;
            width: 95%;
            max-width: 700px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 2.5rem;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .modal-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title::before {
            content: '💰';
            font-size: 1.5rem;
        }

        .close {
            position: absolute;
            top: 1.5rem;
            right: 2rem;
            color: white;
            font-size: 1.75rem;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        #createBillForm {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-height: 0; /* Fix for flexbox scrolling */
        }

        .modal-body {
            padding: 2.5rem;
            background: white;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
            position: relative;
        }

        .form-label::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 30px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #f9fafb;
            color: #374151;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .amount-input-wrapper {
            position: relative;
        }

        .amount-input-wrapper::before {
            content: '₹';
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 600;
            z-index: 1;
        }

        .amount-input {
            padding-left: 2.5rem !important;
        }

        .participants-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 1.5rem;
            border: 2px solid #e2e8f0;
        }

        .participants-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .participants-title {
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .participants-count {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .participants-container {
            min-height: 120px;
        }

        .participant-input {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .participant-input input {
            flex: 1;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }

        .participant-input input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .remove-participant {
            padding: 0.875rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
        }

        .remove-participant:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .add-participant {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-participant:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            padding: 2rem 2.5rem;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
            border-top: 1px solid #e2e8f0;
            flex-shrink: 0; /* Prevent footer from shrinking */
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 140px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error::before {
            content: '⚠️';
        }

        .success-message {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #a7f3d0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .success-message::before {
            content: '✅';
            font-size: 1.25rem;
        }

        .form-tips {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #1e40af;
            font-size: 0.875rem;
        }

        .form-tips strong {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-tips ul {
            margin: 0;
            padding-left: 1.25rem;
        }

        .form-tips li {
            margin-bottom: 0.25rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-content {
                margin: 5% auto;
                width: 95%;
                max-width: none;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1.5rem;
            }
            
            .modal-title {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.875rem 1.5rem;
                font-size: 0.875rem;
            }
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
                <h2 class="modal-title">Create New Bill</h2>
                <span class="close" onclick="closeModal()">&times;</span>
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
            document.getElementById('createBillModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            // Focus on first input
            setTimeout(() => {
                document.getElementById('bill_name').focus();
            }, 300);
        }

        function closeModal() {
            document.getElementById('createBillModal').style.display = 'none';
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
            
            // Update count badge color based on count
            if (count < 2) {
                countElement.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            } else if (count < 5) {
                countElement.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            } else {
                countElement.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            }
        }

        // Add slideOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                to {
                    opacity: 0;
                    transform: translateX(10px);
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize participant count
        document.addEventListener('DOMContentLoaded', function() {
            updateParticipantsCount();
        });
    </script>
</body>
</html> 