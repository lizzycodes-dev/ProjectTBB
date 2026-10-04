<x-app-layout>

    <div class="user-page">

        {{-- =====================================================
         HEADER
         ===================================================== --}}
        <div class="user-header">

            <div>
                <h1>User Management</h1>
                <p>
                    Select a user to view their details, update their position,
                    or archive their account.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="user-add-button">
                <span>+</span>
                Add New User
            </a>

        </div>


        {{-- =====================================================
         SUMMARY STATISTICS
         ===================================================== --}}
        <div class="user-stat-grid">

            <div class="user-stat-card">
                <div>
                    <span class="user-stat-label">Managers</span>
                    <strong>{{ $managerCount }}</strong>
                </div>
            </div>

            <div class="user-stat-card">
                <div>
                    <span class="user-stat-label">Cashiers</span>
                    <strong>{{ $cashierCount }}</strong>
                </div>
            </div>

            <div class="user-stat-card">
                <div>
                    <span class="user-stat-label">Kitchen Staff / Cooks</span>
                    <strong>{{ $cookCount }}</strong>
                </div>
            </div>

        </div>


        {{-- =====================================================
         SUCCESS MESSAGE
         ===================================================== --}}
        @if (session('success'))

        <div class="user-success-message">
            {{ session('success') }}
        </div>

        @endif


        {{-- =====================================================
         VALIDATION ERRORS
         ===================================================== --}}
        @if ($errors->any())

        <div class="user-error-message">

            <strong>Please check the form:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

        @endif


        {{-- =====================================================
         SEARCH & FILTER
         ===================================================== --}}
        <div class="user-filter-bar">

            <form
                method="GET"
                action="{{ route('users.index') }}"
                class="user-filter-form">

                <div class="user-search-wrapper">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or email..."
                        class="user-search-input">

                </div>


                <select
                    name="filter"
                    class="user-filter-select">

                    <option value="">
                        All Roles / Active
                    </option>

                    <option
                        value="Manager"
                        @selected(request('filter')=='Manager' )>
                        Manager
                    </option>

                    <option
                        value="Cashier"
                        @selected(request('filter')=='Cashier' )>
                        Cashier
                    </option>

                    <option
                        value="Cook"
                        @selected(request('filter')=='Cook' )>
                        Cook / Kitchen
                    </option>

                    <option
                        value="archived"
                        @selected(request('filter')=='archived' )>
                        Archived Accounts
                    </option>

                </select>


                <button
                    type="submit"
                    class="user-filter-button">
                    Filter
                </button>


                @if(request('search') || request('filter'))

                <a
                    href="{{ route('users.index') }}"
                    class="user-reset-button">
                    Reset
                </a>

                @endif

            </form>

        </div>


        {{-- =====================================================
         USER TABLE
         ===================================================== --}}
        <div class="user-card">

            <div class="user-card-header">

                <div>
                    <h2>User Accounts</h2>
                    <p>
                        Manage system users, positions, and account status.
                    </p>
                </div>

            </div>


            <div class="user-table-wrapper">

                <table class="user-table">

                    <thead>

                        <tr>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>POSITION</th>
                            <th>STATUS</th>
                            <th class="user-actions-column">ACTIONS</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($users as $user)

                        {{-- =================================================
                         USER ROW
                         ================================================= --}}
                        <tr class="user-row">

                            <td class="user-name-cell">
                                {{ $user->name }}
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>

                                <span class="user-role-badge">
                                    {{ $user->role->name ?? 'None' }}
                                </span>

                            </td>

                            <td>

                                @if($user->trashed())

                                <span class="user-status-badge archived">
                                    Archived
                                </span>

                                @else

                                <span class="user-status-badge active">
                                    Active
                                </span>

                                @endif

                            </td>


                            <td class="user-actions-cell">

                                <div class="user-actions">

                                    @if($user->trashed())

                                    {{-- ACTIVATE --}}

                                    <form
                                        action="{{ route('users.restore', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to reactivate this user account?');">

                                        @csrf
                                        @method('PUT')

                                        <button
                                            type="submit"
                                            class="user-activate-button">
                                            Activate
                                        </button>

                                    </form>

                                    @else

                                    {{-- EDIT --}}

                                    <button
                                        type="button"
                                        class="user-edit-button toggle-user"
                                        data-target="user-details-{{ $user->id }}"
                                        aria-expanded="false">

                                        <span class="toggle-label">
                                            Edit
                                        </span>

                                        <span class="toggle-icon">
                                            ＋
                                        </span>

                                    </button>


                                    {{-- ARCHIVE --}}

                                    <form
                                        action="{{ route('users.destroy', $user->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to archive this user?');">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="user-archive-button">
                                            Archive
                                        </button>

                                    </form>

                                    @endif

                                </div>

                            </td>

                        </tr>


                        {{-- =================================================
                         EDIT DETAILS ROW
                         ================================================= --}}
                        @unless($user->trashed())

                        <tr
                            id="user-details-{{ $user->id }}"
                            class="user-details-row hidden">

                            <td colspan="5">

                                <div class="user-details-panel">

                                    <div class="user-details-header">

                                        <div>

                                            <h3>
                                                {{ $user->name }} — Account Details
                                            </h3>

                                            <p>
                                                Update credentials or change
                                                their assigned position.
                                            </p>

                                        </div>

                                    </div>


                                    <form
                                        id="update-form-{{ $user->id }}"
                                        action="{{ route('users.update', $user->id) }}"
                                        method="POST">

                                        @csrf
                                        @method('PUT')


                                        <div class="user-edit-grid">

                                            {{-- NAME --}}
                                            <div class="user-form-group">

                                                <label>
                                                    Full Name
                                                </label>

                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ $user->name }}"
                                                    required>

                                            </div>


                                            {{-- EMAIL --}}
                                            <div class="user-form-group">

                                                <label>
                                                    Email Address
                                                </label>

                                                <input
                                                    type="email"
                                                    name="email"
                                                    value="{{ $user->email }}"
                                                    required>

                                            </div>


                                            {{-- ROLE --}}
                                            <div class="user-form-group">

                                                <label>
                                                    Position
                                                </label>

                                                <select
                                                    name="role_id"
                                                    required>

                                                    @foreach($roles as $role)

                                                    <option
                                                        value="{{ $role->id }}"
                                                        @selected($user->role_id == $role->id)>
                                                        {{ $role->name }}
                                                    </option>

                                                    @endforeach

                                                </select>

                                            </div>


                                            {{-- PASSWORD --}}
                                            <div class="user-form-group">

                                                <label>
                                                    Reset Password
                                                    <span>(Optional)</span>
                                                </label>

                                                <input
                                                    type="password"
                                                    name="password"
                                                    placeholder="Leave blank to keep"
                                                    minlength="8">

                                            </div>

                                        </div>

                                    </form>


                                    <div class="user-details-footer">

                                        <button
                                            type="submit"
                                            form="update-form-{{ $user->id }}"
                                            class="user-save-button">
                                            Save Changes
                                        </button>

                                    </div>

                                </div>

                            </td>

                        </tr>

                        @endunless


                        @empty

                        <tr>

                            <td
                                colspan="5"
                                class="user-empty">
                                No users found matching your filter criteria.
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =====================================================
             PAGINATION
             ===================================================== --}}
            @if ($users->hasPages())

            <div class="user-pagination">
                {{ $users->links() }}
            </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
     JAVASCRIPT
     ===================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document
                .querySelectorAll('.toggle-user')
                .forEach(function(button) {

                    button.addEventListener('click', function() {

                        const details =
                            document.getElementById(button.dataset.target);

                        if (!details) {
                            return;
                        }

                        const isHidden =
                            details.classList.contains('hidden');

                        details.classList.toggle(
                            'hidden',
                            !isHidden
                        );

                        button.setAttribute(
                            'aria-expanded',
                            isHidden ? 'true' : 'false'
                        );

                        const label =
                            button.querySelector('.toggle-label');

                        const icon =
                            button.querySelector('.toggle-icon');

                        if (label) {
                            label.textContent =
                                isHidden ? 'Close' : 'Edit';
                        }

                        if (icon) {
                            icon.textContent =
                                isHidden ? '−' : '＋';
                        }

                    });

                });

        });
    </script>

</x-app-layout>