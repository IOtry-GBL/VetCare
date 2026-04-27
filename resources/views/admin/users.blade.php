@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('admin.users') }}" class="sidebar-item active">User Management</a>
    <a href="{{ route('admin.analytics') }}" class="sidebar-item">Analytics</a>
@endsection

@section('content')
    <div class="card">
        <h1>User Management</h1>
        <a href="#" onclick="openAddModal()" class="btn btn-primary">+ Add New User</a>
    </div>

    <div class="card">
        <h2>Users by Role</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb; background: #f9fafb;">
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Name</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Email</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Role</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Status</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Joined</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.875rem;">
                @forelse($users as $user)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 0.75rem;">{{ $user->name }}</td>
                        <td style="padding: 0.75rem;">{{ $user->email }}</td>
                        <td style="padding: 0.75rem;">
                            @if($user->role === 'Pet Owner')
                                <span class="badge badge-monitor">Pet Owner</span>
                            @elseif($user->role === 'Veterinarian')
                                <span class="badge badge-visit">Veterinarian</span>
                            @elseif($user->role === 'Staff')
                                <span class="badge badge-visit">Staff</span>
                            @else
                                <span class="badge badge-emergency">{{ $user->role }}</span>
                            @endif
                            @if($user->isSuperAdmin())
                                <span class="badge" style="background: #667eea; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; margin-left: 0.5rem;">SUPER ADMIN</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem;"><span class="badge badge-monitor">Active</span></td>
                        <td style="padding: 0.75rem;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="padding: 0.75rem;">
                            @if(!$user->isSuperAdmin())
                                <a href="#" onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->phone }}', '{{ $user->role }}')" style="color: #667eea; text-decoration: none; cursor: pointer;">Edit</a>
                                <span style="margin: 0 0.5rem;">|</span>
                                <a href="#" onclick="openDeleteModal({{ $user->id }}, '{{ $user->name }}')" style="color: #ef4444; text-decoration: none; cursor: pointer;">Delete</a>
                            @else
                                <span style="color: #999;">Cannot edit</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: #999;">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>User Statistics</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">{{ $roleStats['owner'] }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Pet Owners</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">{{ $roleStats['vet'] }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Veterinarians</p>
            </div>
            <div style="padding: 1rem; background: #f9fafb; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">{{ $roleStats['staff'] }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Staff Members</p>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h2 style="margin-top: 0; color: #333;">Add New User</h2>
            
            <form id="addForm" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Name</label>
                    <input type="text" id="addName" name="name" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Email</label>
                    <input type="email" id="addEmail" name="email" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Phone</label>
                    <input type="text" id="addPhone" name="phone" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Password</label>
                    <input type="password" id="addPassword" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Role</label>
                    <select id="addRole" name="role" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                        <option value="">Select a role</option>
                        <option value="Pet Owner">Pet Owner</option>
                        <option value="Veterinarian">Veterinarian</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <div id="addError" style="color: #ef4444; display: none; padding: 0.75rem; background: #fee2e2; border-radius: 0.375rem;"></div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" onclick="closeAddModal()" style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; background: white; color: #333; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">Cancel</button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h2 style="margin-top: 0; color: #333;">Edit User</h2>
            
            <form id="editForm" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" id="editUserId" name="id">
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Name</label>
                    <input type="text" id="editName" name="name" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Email</label>
                    <input type="email" id="editEmail" name="email" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Phone</label>
                    <input type="text" id="editPhone" name="phone" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Role</label>
                    <select id="editRole" name="role" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem;" required>
                        <option value="Pet Owner">Pet Owner</option>
                        <option value="Veterinarian">Veterinarian</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <div id="editError" style="color: #ef4444; display: none; padding: 0.75rem; background: #fee2e2; border-radius: 0.375rem;"></div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" onclick="closeEditModal()" style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; background: white; color: #333; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">Cancel</button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 400px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center;">
            <h2 style="margin-top: 0; color: #333;">Delete User</h2>
            <p style="color: #666; margin: 1rem 0;">Are you sure you want to delete <strong id="deleteUserName" style="color: #333;"></strong>?</p>
            <p style="color: #999; font-size: 0.875rem; margin: 1rem 0;">This action cannot be undone.</p>
            
            <div id="deleteError" style="color: #ef4444; display: none; padding: 0.75rem; background: #fee2e2; border-radius: 0.375rem; margin-bottom: 1rem;"></div>

            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <button type="button" onclick="closeDeleteModal()" style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; background: white; color: #333; border-radius: 0.375rem; cursor: pointer; font-weight: 600; flex: 1;">Cancel</button>
                <button type="button" onclick="confirmDelete()" style="padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600; flex: 1;">Delete</button>
            </div>
        </div>
    </div>

    <style>
        #addModal[style*="display: flex"] {
            display: flex !important;
        }
        #editModal[style*="display: flex"] {
            display: flex !important;
        }
        #deleteModal[style*="display: flex"] {
            display: flex !important;
        }
    </style>

    <script>
        let currentDeleteUserId = null;

        function openAddModal() {
            document.getElementById('addForm').reset();
            document.getElementById('addError').style.display = 'none';
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(id, name, email, phone, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editPhone').value = phone;
            document.getElementById('editRole').value = role;
            document.getElementById('editError').style.display = 'none';
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openDeleteModal(id, name) {
            currentDeleteUserId = id;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteError').style.display = 'none';
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            currentDeleteUserId = null;
        }

        document.getElementById('addForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('name', document.getElementById('addName').value);
            formData.append('email', document.getElementById('addEmail').value);
            formData.append('phone', document.getElementById('addPhone').value);
            formData.append('password', document.getElementById('addPassword').value);
            formData.append('role', document.getElementById('addRole').value);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('/admin/users', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    closeAddModal();
                    location.reload();
                } else {
                    document.getElementById('addError').textContent = data.error || 'An error occurred';
                    document.getElementById('addError').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('addError').textContent = 'An error occurred. Please try again.';
                document.getElementById('addError').style.display = 'block';
            }
        });

        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const formData = new FormData();
            formData.append('name', document.getElementById('editName').value);
            formData.append('email', document.getElementById('editEmail').value);
            formData.append('phone', document.getElementById('editPhone').value);
            formData.append('role', document.getElementById('editRole').value);
            formData.append('_method', 'PUT');
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    closeEditModal();
                    location.reload();
                } else {
                    document.getElementById('editError').textContent = data.error || 'An error occurred';
                    document.getElementById('editError').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('editError').textContent = 'An error occurred. Please try again.';
                document.getElementById('editError').style.display = 'block';
            }
        });

        async function confirmDelete() {
            try {
                const response = await fetch(`/admin/users/${currentDeleteUserId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    closeDeleteModal();
                    location.reload();
                } else {
                    document.getElementById('deleteError').textContent = data.error || 'An error occurred';
                    document.getElementById('deleteError').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('deleteError').textContent = 'An error occurred. Please try again.';
                document.getElementById('deleteError').style.display = 'block';
            }
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });
    </script>
@endsection
