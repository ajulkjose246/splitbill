<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SplitBill</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('/css/dashboard.css') }}" rel="stylesheet">
    
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
    <script src="{{ asset('/js/dashboard.js') }}"></script>
</body>
</html> 