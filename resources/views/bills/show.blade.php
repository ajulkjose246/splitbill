<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bill->name }} - SplitBill</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('/css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/bill-chat.css') }}" rel="stylesheet">
</head>
<body>
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <a href="/bills" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="bill-info">
                    <h2>{{ $bill->name }}</h2>
                    <p>{{ $bill->participants->count() }} participants</p>
                </div>
            </div>
            <div class="chat-header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="chat-messages" id="chatMessages">
            

            <!-- Existing Expenses -->
            @if($bill->expenses->count() > 0)
                
                @foreach($bill->expenses->sortBy('created_at') as $expense)
                    @php
                        $currentUser = Auth::user();
                        $isIncluded = $expense->participants->contains('id', $currentUser->id);
                        $isPaidByMe = $expense->paid_by == $currentUser->id;
                    @endphp
                    <div class="message expense-message">
                        <div class="message-avatar">
                            {{ substr($expense->paidBy->name, 0, 1) }}
                        </div>
                        <div class="message-bubble">
                            <div class="message-sender">{{ $expense->paidBy->name }}</div>
                            <div class="message-content">
                                <div class="expense-item">
                                    <div class="expense-header">
                                        <span class="expense-title">💰 {{ $expense->title }}</span>
                                        <span class="expense-amount">₹{{ number_format($expense->amount, 2) }}</span>
                                    </div>
                                    @if($expense->description)
                                        <div class="expense-description">{{ $expense->description }}</div>
                                    @endif
                                    <div class="expense-meta">
                                        <span class="expense-date">{{ $expense->created_at->format('M d, Y g:i A') }}</span>
                                        <span class="expense-participants">
                                            Shared with {{ $expense->participants->count() }} participant{{ $expense->participants->count() > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <div class="expense-status">
                                        @if($isPaidByMe)
                                            <span class="status-paid">✅ You paid for this expense</span>
                                            <div class="expense-actions">
                                                <button class="action-btn edit-expense-btn" onclick="openEditExpenseModal({{ $expense->id }}, '{{ $expense->title }}', {{ $expense->amount }}, '{{ $expense->description ?? '' }}', {{ json_encode($expense->participants->pluck('id')) }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete-expense-btn" onclick="deleteExpense({{ $expense->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        @elseif($isIncluded)
                                            <span class="status-owe">💸 You owe ₹{{ number_format($expense->amount / $expense->participants->count(), 2) }}</span>
                                        @else
                                            <span class="status-excluded">🚫 You don't need to pay for this</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Chat Input Area -->
        <div class="chat-input-area">
            <div class="input-container">
                <div class="input-actions">
                    <button class="action-btn expense-btn" title="Add Expense" onclick="showExpenseModal()">
                        <i class="fas fa-plus"></i>
                        <span>Add Expense</span>
                    </button>
                    <button class="action-btn balance-btn" title="View Balance Sheet" onclick="showBalanceSheetModal()">
                        <i class="fas fa-calculator"></i>
                        <span>Balance Sheet</span>
                    </button>
                </div>
              </div>
        </div>

        <!-- Expense Modal -->
        <div class="expense-modal" id="expenseModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Expense</h3>
                    <button class="close-modal" onclick="hideExpenseModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="expenseForm">
                        @csrf
                        <div class="form-group">
                            <label for="expenseName">Expense Name</label>
                            <input type="text" id="expenseName" name="title" placeholder="e.g., Restaurant dinner" required>
                        </div>
                        <div class="form-group">
                            <label for="expenseAmount">Amount</label>
                            <input type="number" id="expenseAmount" name="amount" placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="expenseDescription">Description (Optional)</label>
                            <textarea id="expenseDescription" name="description" placeholder="Add any additional details..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="shareWithAll" name="share_with_all" value="1" checked>
                                <span class="checkmark"></span>
                                Share with all participants
                            </label>
                        </div>
                        
                        <div class="form-group hidden" id="participantSelection">
                            <label>Select Participants</label>
                            <div class="participants-checkboxes" id="participantsCheckboxesContainer">
                                <!-- Participant checkboxes will be injected here by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="hideExpenseModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Modal -->
        <div class="balance-modal" id="balanceModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Balance Sheet</h3>
                    <button class="close-modal" onclick="hideBalanceSheetModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="balanceSheetContent">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Calculating balance sheet...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Expense Modal -->
        <div class="expense-modal" id="editExpenseModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Expense</h3>
                    <button class="close-modal" onclick="hideEditExpenseModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editExpenseForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editExpenseId" name="expense_id">
                        
                        <div class="form-group">
                            <label for="editExpenseName">Expense Name</label>
                            <input type="text" id="editExpenseName" name="title" placeholder="e.g., Restaurant dinner" required>
                        </div>
                        <div class="form-group">
                            <label for="editExpenseAmount">Amount</label>
                            <input type="number" id="editExpenseAmount" name="amount" placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editExpenseDescription">Description (Optional)</label>
                            <textarea id="editExpenseDescription" name="description" placeholder="Add any additional details..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="editShareWithAll" name="share_with_all" value="1" checked>
                                <span class="checkmark"></span>
                                Share with all participants
                            </label>
                        </div>
                        
                        <div class="form-group hidden" id="editParticipantSelection">
                            <label>Select Participants</label>
                            <div class="participants-checkboxes" id="editParticipantsCheckboxesContainer">
                                <!-- Participant checkboxes will be injected here by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="hideEditExpenseModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Participants Sidebar (Hidden on mobile) -->
        <div class="participants-sidebar" id="participantsSidebar">
            <div class="sidebar-header">
                <h3>Participants</h3>
                <button class="close-sidebar" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="participants-list">
                @foreach($bill->participants as $participant)
                    <div class="participant-item">
                        <div class="participant-avatar">
                            {{ substr($participant->user->name, 0, 1) }}
                        </div>
                        <div class="participant-info">
                            <div class="participant-name">{{ $participant->user->name }}</div>
                            <div class="participant-role">
                                @if($participant->user_id == $bill->created_by)
                                    <span class="role-badge creator">Creator</span>
                                @else
                                    <span class="role-badge participant">Participant</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Mobile Participants Button -->
        <button class="participants-btn" id="participantsBtn">
            <i class="fas fa-users"></i>
            <span>{{ $bill->participants->count() }}</span>
        </button>
    </div>

    <script id="participants-data" type="application/json">
        {!! json_encode($bill->participants) !!}
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const participantsData = JSON.parse(document.getElementById('participants-data').textContent);
            const currentUserId = {!! Auth::id() !!};

            function scrollToBottom() {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            window.showExpenseModal = function() {
                document.getElementById('expenseModal').classList.add('show');
            }

            window.hideExpenseModal = function() {
                const modal = document.getElementById('expenseModal');
                modal.classList.remove('show');
                document.getElementById('expenseForm').reset();
                document.getElementById('shareWithAll').checked = true;
                document.getElementById('participantSelection').classList.add('hidden');
                document.getElementById('participantsCheckboxesContainer').innerHTML = '';
            }

            window.showBalanceSheetModal = function() {
                document.getElementById('balanceModal').classList.add('show');
                fetchBalanceSheet();
            }

            window.hideBalanceSheetModal = function() {
                document.getElementById('balanceModal').classList.remove('show');
            }

            window.openEditExpenseModal = function(expenseId, title, amount, description, participantIds) {
                document.getElementById('editExpenseId').value = expenseId;
                document.getElementById('editExpenseName').value = title;
                document.getElementById('editExpenseAmount').value = amount;
                document.getElementById('editExpenseDescription').value = description;
                
                // Set up participant selection
                const shareWithAll = document.getElementById('editShareWithAll');
                const participantSelection = document.getElementById('editParticipantSelection');
                const checkboxesContainer = document.getElementById('editParticipantsCheckboxesContainer');
                
                // Check if all participants are selected
                const allParticipantIds = participantsData.map(p => p.user_id);
                const isAllSelected = participantIds.length === allParticipantIds.length && 
                                    participantIds.every(id => allParticipantIds.includes(id));
                
                if (isAllSelected) {
                    shareWithAll.checked = true;
                    participantSelection.classList.add('hidden');
                    checkboxesContainer.innerHTML = '';
                } else {
                    shareWithAll.checked = false;
                    let checkboxesHtml = '';
                    participantsData.forEach(participant => {
                        const isChecked = participantIds.includes(participant.user_id) ? 'checked' : '';
                        checkboxesHtml += `
                            <label class="checkbox-label participant-checkbox">
                                <input type="checkbox" name="selected_participants[]" value="${participant.user_id}" ${isChecked}>
                                <span class="checkmark"></span>
                                <div class="participant-info">
                                    <div class="participant-avatar-small">
                                        ${participant.user.name.substring(0, 1)}
                                    </div>
                                    <span>${participant.user.name}</span>
                                </div>
                            </label>
                        `;
                    });
                    checkboxesContainer.innerHTML = checkboxesHtml;
                    participantSelection.classList.remove('hidden');
                }
                
                document.getElementById('editExpenseModal').classList.add('show');
            }

            window.hideEditExpenseModal = function() {
                const modal = document.getElementById('editExpenseModal');
                modal.classList.remove('show');
                document.getElementById('editExpenseForm').reset();
                document.getElementById('editShareWithAll').checked = true;
                document.getElementById('editParticipantSelection').classList.add('hidden');
                document.getElementById('editParticipantsCheckboxesContainer').innerHTML = '';
            }

            window.deleteExpense = function(expenseId) {
                if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
                    fetch(`/bills/{{ $bill->id }}/expenses/${expenseId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the expense from the chat
                            const expenseMessages = document.querySelectorAll('.expense-message');
                            expenseMessages.forEach(message => {
                                const editBtn = message.querySelector('.edit-expense-btn');
                                if (editBtn && editBtn.getAttribute('onclick').includes(expenseId.toString())) {
                                    message.remove();
                                }
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Failed to delete expense'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the expense.');
                    });
                }
            }

            function toggleParticipantSelection() {
                const shareWithAll = document.getElementById('shareWithAll');
                const participantSelection = document.getElementById('participantSelection');
                const checkboxesContainer = document.getElementById('participantsCheckboxesContainer');

                if (shareWithAll.checked) {
                    participantSelection.classList.add('hidden');
                    checkboxesContainer.innerHTML = '';
                } else {
                    let checkboxesHtml = '';
                    participantsData.forEach(participant => {
                        const isChecked = participant.user_id === currentUserId ? 'checked' : '';
                        checkboxesHtml += `
                            <label class="checkbox-label participant-checkbox">
                                <input type="checkbox" name="selected_participants[]" value="${participant.user_id}" ${isChecked}>
                                <span class="checkmark"></span>
                                <div class="participant-info">
                                    <div class="participant-avatar-small">
                                        ${participant.user.name.substring(0, 1)}
                                    </div>
                                    <span>${participant.user.name}</span>
                                </div>
                            </label>
                        `;
                    });
                    checkboxesContainer.innerHTML = checkboxesHtml;
                    participantSelection.classList.remove('hidden');
                }
            }

            function toggleEditParticipantSelection() {
                const shareWithAll = document.getElementById('editShareWithAll');
                const participantSelection = document.getElementById('editParticipantSelection');
                const checkboxesContainer = document.getElementById('editParticipantsCheckboxesContainer');

                if (shareWithAll.checked) {
                    participantSelection.classList.add('hidden');
                    checkboxesContainer.innerHTML = '';
                } else {
                    let checkboxesHtml = '';
                    participantsData.forEach(participant => {
                        const isChecked = participant.user_id === currentUserId ? 'checked' : '';
                        checkboxesHtml += `
                            <label class="checkbox-label participant-checkbox">
                                <input type="checkbox" name="selected_participants[]" value="${participant.user_id}" ${isChecked}>
                                <span class="checkmark"></span>
                                <div class="participant-info">
                                    <div class="participant-avatar-small">
                                        ${participant.user.name.substring(0, 1)}
                                    </div>
                                    <span>${participant.user.name}</span>
                                </div>
                            </label>
                        `;
                    });
                    checkboxesContainer.innerHTML = checkboxesHtml;
                    participantSelection.classList.remove('hidden');
                }
            }

            function addMessageToChat(content, type) {
                const chatMessages = document.getElementById('chatMessages');
                if (!chatMessages) return;
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                
                if (type === 'sent') {
                    messageDiv.innerHTML = `<div class="message-bubble"><div class="message-content">${content}</div></div>`;
                } else {
                    messageDiv.innerHTML = `<div class="message-avatar">S</div><div class="message-bubble"><div class="message-sender">System</div><div class="message-content">${content}</div></div>`;
                }
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }

            function addExpenseToChat(expense) {
                const chatMessages = document.getElementById('chatMessages');
                if (!chatMessages) return;
                
                const expenseDiv = document.createElement('div');
                expenseDiv.className = 'message expense-message';
                
                const description = expense.description ? `<div class="expense-description">${expense.description}</div>` : '';
                const date = new Date(expense.created_at).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
                // Determine payment status
                const isPaidByMe = expense.paid_by.id === currentUserId;
                const isIncluded = expense.participants.some(p => p.id === currentUserId);
                let statusHtml = '';
                
                if (isPaidByMe) {
                    statusHtml = '<span class="status-paid">✅ You paid for this expense</span>';
                    // Add edit and delete buttons for expenses created by current user
                    const participantIds = expense.participants.map(p => p.id);
                    statusHtml += `
                        <div class="expense-actions">
                            <button class="action-btn edit-expense-btn" onclick="openEditExpenseModal(${expense.id}, '${expense.title.replace(/'/g, "\\'")}', ${expense.amount}, '${(expense.description || '').replace(/'/g, "\\'")}', ${JSON.stringify(participantIds)})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-expense-btn" onclick="deleteExpense(${expense.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                } else if (isIncluded) {
                    const amountPerPerson = (expense.amount / expense.participants.length).toFixed(2);
                    statusHtml = `<span class="status-owe">💸 You owe ₹${amountPerPerson}</span>`;
                } else {
                    statusHtml = '<span class="status-excluded">🚫 You don\'t need to pay for this</span>';
                }
                
                expenseDiv.innerHTML = `
                    <div class="message-avatar">
                        ${expense.paid_by.name.substring(0, 1)}
                    </div>
                    <div class="message-bubble">
                        <div class="message-sender">${expense.paid_by.name}</div>
                        <div class="message-content">
                            <div class="expense-item">
                                <div class="expense-header">
                                    <span class="expense-title">💰 ${expense.title}</span>
                                    <span class="expense-amount">₹${parseFloat(expense.amount).toFixed(2)}</span>
                                </div>
                                ${description}
                                <div class="expense-meta">
                                    <span class="expense-date">${date}</span>
                                    <span class="expense-participants">
                                        Shared with ${expense.participants.length} participant${expense.participants.length > 1 ? 's' : ''}
                                    </span>
                                </div>
                                <div class="expense-status">
                                    ${statusHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add new expense at the bottom of the chat
                chatMessages.appendChild(expenseDiv);
                
                scrollToBottom();
            }

            function fetchExpenses() {
                fetch(`/bills/{{ $bill->id }}/expenses`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Clear existing expense messages (keep welcome message)
                            const chatMessages = document.getElementById('chatMessages');
                            const expenseMessages = chatMessages.querySelectorAll('.expense-message');
                            expenseMessages.forEach(msg => msg.remove());
                            
                            // Add expenses in chronological order (oldest first)
                            data.expenses.forEach(expense => {
                                addExpenseToChat(expense);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching expenses:', error);
                    });
            }

            function fetchBalanceSheet() {
                const contentDiv = document.getElementById('balanceSheetContent');
                contentDiv.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><span>Calculating balance sheet...</span></div>';
                
                fetch(`/bills/{{ $bill->id }}/balance-sheet`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayBalanceSheet(data);
                        } else {
                            contentDiv.innerHTML = '<div class="error-message">Error loading balance sheet</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching balance sheet:', error);
                        contentDiv.innerHTML = '<div class="error-message">Error loading balance sheet</div>';
                    });
            }

            function displayBalanceSheet(data) {
                const contentDiv = document.getElementById('balanceSheetContent');
                
                let html = `
                    <div class="balance-summary">
                        <div class="total-expenses">
                            <h4>Total Expenses</h4>
                            <div class="total-amount">₹${parseFloat(data.total_expenses).toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <div class="balance-section">
                        <h4>Individual Balances</h4>
                        <div class="balance-list">
                `;
                
                data.balance_sheet.forEach(item => {
                    const balanceClass = item.is_positive ? 'positive-balance' : 'negative-balance';
                    const balanceIcon = item.is_positive ? '💰' : '💸';
                    const balanceText = item.is_positive ? 'Gets back' : 'Owes';
                    
                    html += `
                        <div class="balance-item">
                            <div class="user-info">
                                <div class="user-avatar-small">${item.user.name.substring(0, 1)}</div>
                                <div class="user-details">
                                    <div class="user-name">${item.user.name}</div>
                                    <div class="user-stats">
                                        <span class="spent">Spent: ₹${parseFloat(item.total_spent).toFixed(2)}</span>
                                        <span class="owed">Owed: ₹${parseFloat(item.total_owed).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="balance-amount ${balanceClass}">
                                ${balanceIcon} ${balanceText} ₹${Math.abs(parseFloat(item.balance)).toFixed(2)}
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
                
                if (data.settlements.length > 0) {
                    html += `
                        <div class="settlements-section">
                            <h4>Final Settlements</h4>
                            <div class="settlements-list">
                    `;
                    
                    data.settlements.forEach(settlement => {
                        const fromUser = data.balance_sheet.find(item => item.user.id === settlement.from)?.user;
                        const toUser = data.balance_sheet.find(item => item.user.id === settlement.to)?.user;
                        
                        if (fromUser && toUser) {
                            html += `
                                <div class="settlement-item">
                                    <div class="settlement-from">
                                        <div class="user-avatar-small">${fromUser.name.substring(0, 1)}</div>
                                        <span>${fromUser.name}</span>
                                    </div>
                                    <div class="settlement-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                        <span class="settlement-amount">₹${parseFloat(settlement.amount).toFixed(2)}</span>
                                    </div>
                                    <div class="settlement-to">
                                        <div class="user-avatar-small">${toUser.name.substring(0, 1)}</div>
                                        <span>${toUser.name}</span>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="no-settlements">
                            <p>All balances are settled! 🎉</p>
                        </div>
                    `;
                }
                
                contentDiv.innerHTML = html;
            }

            function handleExpenseSubmit(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                
                const shareWithAll = formData.get('share_with_all');
                const selectedParticipants = formData.getAll('selected_participants[]');
                
                if (!shareWithAll && selectedParticipants.length === 0) {
                    alert('Please select at least one participant to share the expense with.');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                submitBtn.disabled = true;
                
                fetch(`/bills/{{ $bill->id }}/expenses`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideExpenseModal();
                        // Refresh the expense list
                        fetchExpenses();
                    } else {
                        alert('Error: ' + (data.message || 'An unknown error occurred.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the expense.');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }

            function handleEditExpenseSubmit(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const expenseId = formData.get('expense_id');

                // Handle share_with_all checkbox state
                const shareWithAll = document.getElementById('editShareWithAll').checked;
                formData.set('share_with_all', shareWithAll ? '1' : '0');

                const selectedParticipants = formData.getAll('selected_participants[]');
                
                if (!shareWithAll && selectedParticipants.length === 0) {
                    alert('Please select at least one participant to share the expense with.');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                submitBtn.disabled = true;
                
                fetch(`/bills/{{ $bill->id }}/expenses/${expenseId}`, {
                    method: 'POST', // Use POST for FormData with method spoofing (@method('PUT') in form)
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formData.get('_token')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const expenseMessage = `💰 Expense updated: "${data.expense.title}" for ₹${data.expense.amount}`;
                        addMessageToChat(expenseMessage, 'sent');
                        hideEditExpenseModal();
                        fetchExpenses(); // Refresh the expense list
                    } else {
                        let errorMessage = data.message || 'An unknown error occurred.';
                        if (data.errors) {
                            errorMessage = Object.values(data.errors).flat().join('\n');
                        }
                        alert('Error:\n' + errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the expense.');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }

            // Initial setup
            scrollToBottom();
            
            // Fetch and display expenses
            fetchExpenses();

            // Attach event listeners
            const expenseForm = document.getElementById('expenseForm');
            if (expenseForm) expenseForm.addEventListener('submit', handleExpenseSubmit);

            const editExpenseForm = document.getElementById('editExpenseForm');
            if (editExpenseForm) editExpenseForm.addEventListener('submit', handleEditExpenseSubmit);

            const shareCheckbox = document.getElementById('shareWithAll');
            if (shareCheckbox) shareCheckbox.addEventListener('change', toggleParticipantSelection);

            const editShareCheckbox = document.getElementById('editShareWithAll');
            if (editShareCheckbox) editShareCheckbox.addEventListener('change', toggleEditParticipantSelection);

            const participantsBtn = document.getElementById('participantsBtn');
            if (participantsBtn) {
                participantsBtn.addEventListener('click', () => document.getElementById('participantsSidebar').classList.add('show'));
            }

            const closeSidebar = document.getElementById('closeSidebar');
            if (closeSidebar) {
                closeSidebar.addEventListener('click', () => document.getElementById('participantsSidebar').classList.remove('show'));
            }

            const expenseModal = document.getElementById('expenseModal');
            if (expenseModal) {
                expenseModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideExpenseModal();
                    }
                });
            }

            const balanceModal = document.getElementById('balanceModal');
            if (balanceModal) {
                balanceModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideBalanceSheetModal();
                    }
                });
            }

            const editExpenseModal = document.getElementById('editExpenseModal');
            if (editExpenseModal) {
                editExpenseModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideEditExpenseModal();
                    }
                });
            }
        });
    </script>
</body>
</html>